<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\ExternalParticipant;
use App\Entity\Personnel;
use App\Entity\Reunion;
use App\Entity\ReunionParticipation;
use App\Enum\ParticipantStatut;
use App\Form\Type\HtmlType;
use Doctrine\DBAL\Types\TextType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\TemplateType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Spipu\Html2Pdf\Parsing\Html;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

final class ReunionParticipationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'reunion.dateDebut';
        $sortValues['_sort_order'] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('reunion', null, [
                'label' => 'Meeting',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'objet',
                    'minimum_input_length' => 2,
                ],
                'show_filter' => true,
            ])
            ->add('personnel', null, [
                'label' => 'Internal Personnel',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'nomComplet',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('externalParticipant', null, [
                'label' => 'External Participant',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'nom',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('status', null, [
                'label' => 'Status',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $isChild = $this->isChild();

        $list
            ->add('reunion', null, [
                'label' => 'Meeting',
                'associated_property' => 'objet',
                'template' => '@SonataAdmin/CRUD/reunion_participation/list_reunion.html.twig',
            ])
            ->add('participantName', null, [
                'label' => 'Participant',
                'template' => '@SonataAdmin/CRUD/reunion_participation/list_participant.html.twig',
            ])
            ->add('participantType', null, [
                'label' => 'Type',
                'template' => '@SonataAdmin/CRUD/reunion_participation/list_participant_type.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Status',
                'template' => '@SonataAdmin/CRUD/reunion_participation/list_status.html.twig',
            ])
            ->add('confirmedAt', null, [
                'label' => 'Confirmed',
                'format' => 'd/m/Y H:i',
            ])
            ->add('absenceReason', null, [
                'label' => 'Absence Reason',
                'template' => '@SonataAdmin/CRUD/fields/list_string_truncated.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;

        if ($isChild) {
            $list->remove('reunion');
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEditMode = $this->hasSubject() && $this->getSubject()->getId() !== null;
        // Check if we're in embedded mode (within Reunion form)
        $isEmbedded = $this->hasParentFieldDescription();

        $isChild = $this->isChild();

        // Only show reunion field if not embedded
        if (!$isEmbedded && !$isChild) {
            $form
                ->with('Meeting Details', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-primary'
                ])
                    ->add('reunion', ModelType::class, [
                        'label' => 'Meeting',
                        'class' => Reunion::class,
                        'property' => 'objet',
                        'btn_add' => false,
                        'btn_delete' => false,
                        'disabled' => $isEditMode,
                        'help' => $isEditMode ? 'Meeting cannot be changed after creation' : 'Select the meeting',
                    ])
                ->end();
        }
            
        $form->with('Participant Selection', [
                'class' => $isEmbedded ? 'col-md-8' : 'col-md-6',
                'box_class' => 'box box-info'
            ])
                ->add('personnel', ModelType::class, [
                    'label' => 'Internal Personnel',
                    'class' => Personnel::class,
                    'property' => 'nomComplet',
                    'required' => false,
                    'btn_add' => false,
                    'btn_delete' => false,
                    'disabled' => $isEditMode, // Can't change participant once created
                    'help' => $isEditMode ? 'Participant cannot be changed' : 'Select internal staff member',
                ])
                ->add('externalParticipant', ModelType::class, [
                    'label' => 'External Participant',
                    'class' => ExternalParticipant::class,
                    'property' => 'nom',
                    'required' => false,
                    'btn_add' => 'Add External Participant',
                    'btn_delete' => false,
                    'disabled' => $isEditMode, // Can't change participant once created
                    'help' => $isEditMode ? 'Participant cannot be changed' : 'Select external consultant/guest',
                ])
            ->end()
        ;

        if ($isEditMode) {
            $form
                ->with('Participation Status', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-success'
                ])
                    ->add('status', EnumType::class, [
                        'label' => 'Status',
                        'class' => ParticipantStatut::class,
                        'help' => 'Current participation status',
                    ])
                    ->add('confirmedAt', DateTimeType::class, [
                        'label' => 'Confirmed At',
                        'required' => false,
                        'widget' => 'single_text',
                        'help' => 'When the participant confirmed attendance',
                    ])
                    ->add('absenceReason', TextareaType::class, [
                        'label' => 'Absence/Excuse Reason',
                        'required' => false,
                        'attr' => [
                            'rows' => 4,
                            'placeholder' => 'Enter reason for absence or excuse...',
                        ],
                        'help' => 'Provide reason if status is Absent or Excused',
                    ])
                ->end()
                
                ->with('Quick Status Actions', [
                    'class' => 'col-md-4',
                    'box_class' => 'box box-warning'
                ])
                    ->add('_status_help', HtmlType::class, [
                        'html' => '
                            <div class="alert alert-info">
                                <h4><i class="icon fa fa-info-circle"></i> Status Guide</h4>
                                <ul>
                                    <li><strong>Invited:</strong> Initial state, awaiting response</li>
                                    <li><strong>Confirmed:</strong> Participant confirmed attendance</li>
                                    <li><strong>Attended:</strong> Participant was present at meeting</li>
                                    <li><strong>Absent:</strong> Did not attend, no excuse</li>
                                    <li><strong>Excused:</strong> Justified absence</li>
                                </ul>
                                <p class="text-warning">
                                    <i class="fa fa-warning"></i> Remember to provide reason for absences!
                                </p>
                            </div>
                        ',
                    ])
                ->end()
            ;
        } else {
            $form
                ->with('Important Notes', [
                    'class' => $isEmbedded ? 'col-md-4' : 'col-md-12',
                    'box_class' => 'box box-warning'
                ])
                    ->add('_creation_help', HtmlType::class, [
                        'html' => '
                            <div class="alert alert-warning">
                                <h4><i class="icon fa fa-warning"></i> Important!</h4>
                                <p>You must select <strong>either</strong> an Internal Personnel <strong>or</strong> an External Participant (not both).</p>
                                <ul>
                                    <li><strong>Internal Personnel:</strong> Staff members with personnel records</li>
                                    <li><strong>External Participant:</strong> Consultants, guests, or external attendees</li>
                                </ul>
                                <p class="text-info">
                                    <i class="fa fa-lightbulb-o"></i> <strong>Tip:</strong> Use the "Add External Participant" button if the person is not in the system yet.
                                </p>
                            </div>
                        ',
                    ])
                ->end()
            ;
        }
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Participation Details', [
                'class' => 'col-md-6',
                'box_class' => 'box box-primary'
            ])
                ->add('id', null, [
                    'label' => 'ID',
                ])
                ->add('reunion', null, [
                    'label' => 'Meeting',
                    'associated_property' => 'objet',
                    'template' => '@SonataAdmin/CRUD/reunion_participation/show_reunion.html.twig',
                ])
                ->add('participantName', null, [
                    'label' => 'Participant',
                    'template' => '@SonataAdmin/CRUD/reunion_participation/show_participant.html.twig',
                ])
                ->add('participantType', null, [
                    'label' => 'Participant Type',
                    'template' => '@SonataAdmin/CRUD/reunion_participation/show_participant_type.html.twig',
                ])
            ->end()
            
            ->with('Status Information', [
                'class' => 'col-md-6',
                'box_class' => 'box box-success'
            ])
                ->add('status', null, [
                    'label' => 'Current Status',
                    'template' => '@SonataAdmin/CRUD/reunion_participation/show_status.html.twig',
                ])
                ->add('confirmedAt', null, [
                    'label' => 'Confirmed At',
                    'format' => 'd/m/Y H:i',
                ])
                ->add('absenceReason', null, [
                    'label' => 'Absence/Excuse Reason',
                ])
                ->add('date_created', null, [
                    'label' => 'Added On',
                    'format' => 'd/m/Y H:i',
                ])
            ->end()
            
            ->with('Contact Information', [
                'class' => 'col-md-12',
                'box_class' => 'box box-info'
            ])
                ->add('date_updated', TemplateType::class, [
                    'label' => false,
                    'mapped' => false,
                    'template' => '@SonataAdmin/CRUD/reunion_participation/show_contact_info.html.twig',
                ])
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof ReunionParticipation
            ? sprintf('%s - %s', 
                $object->getReunion() ? $object->getReunion()->getObjet() : 'N/A',
                $object->getParticipantName()
            )
            : 'Participation';
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Meeting' => 'reunion.objet',
            'Meeting Date' => 'reunion.dateDebut',
            'Participant Name' => function($object) {
                return $object->getParticipantName();
            },
            'Participant Type' => 'participantType',
            'Status' => 'status',
            'Confirmed At' => 'confirmedAt',
            'Absence Reason' => 'absenceReason',
        ];
    }

    public function prePersist(object $object): void
    {
        // Set initial status if not set
        if (!$object->getStatus()) {
            $object->setStatus(ParticipantStatut::Invited);
        }
    }

    public function preUpdate(object $object): void
    {
        // Auto-set confirmedAt when status changes to Confirmed
        if ($object->getStatus() === ParticipantStatut::Confirmed && !$object->getConfirmedAt()) {
            $object->setConfirmedAt(new \DateTimeImmutable());
        }
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        if($this->hasParentFieldDescription() or $this->isChild()) {
            return;
        }
        
        $collection->clearExcept(['list', 'show', 'export']);
    }
}