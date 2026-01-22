<?php

declare(strict_types=1);

namespace App\Admin;

use App\Controller\Admin\ReunionCRUDController;
use App\Entity\Document;
use App\Entity\MeetingRoom;
use App\Entity\Personnel;
use App\Entity\Reunion;
use App\Entity\Structure;
use App\Entity\ReunionParticipation;
use App\Entity\AgendaItem;
use App\Entity\ActionItem;
use App\Enum\MeetingTypeEnum;
use App\Enum\ReunionStatut;
use App\Enum\VideoConferencePlatform;
use App\Form\Type\HtmlType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ReunionAdmin extends AbstractAdmin
{
    protected function getControllerClass(): string
    {
        return ReunionCRUDController::class;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'dateDebut';
        $sortValues['_sort_order'] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // Custom routes
        $collection->add('validate', $this->getRouterIdParameter().'/validate');
        $collection->add('cancel', $this->getRouterIdParameter().'/cancel');
        $collection->add('postpone', $this->getRouterIdParameter().'/postpone');
        $collection->add('complete', $this->getRouterIdParameter().'/complete');
        $collection->add('send_invitations', $this->getRouterIdParameter().'/send-invitations');
        $collection->add('view_report', $this->getRouterIdParameter().'/view-report');
        $collection->add('attendance', $this->getRouterIdParameter().'/attendance');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('objet', null, [
                'label' => 'Subject',
                'show_filter' => true,
            ])
            ->add('dateDebut', null, [
                'label' => 'Start Date',
            ])
            ->add('organisateur', null, [
                'label' => 'Organizer',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'nameFr',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('president', null, [
                'label' => 'President',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'nomComplet',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('statut', null, [
                'label' => 'Status',
            ])
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('lieu', null, [
                'label' => 'Location',
            ])
            ->add('salle', null, [
                'label' => 'Meeting Room',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('_status_indicator', null, [
                'label' => '',
                'template' => '@SonataAdmin/CRUD/reunion/list_status_indicator.html.twig',
            ])
            ->add('dateDebut', null, [
                'label' => 'Date & Time',
                'format' => 'd/m/Y H:i',
                'template' => '@SonataAdmin/CRUD/reunion/list_date.html.twig',
            ])
            ->add('objet', null, [
                'label' => 'Subject',
                'template' => '@SonataAdmin/CRUD/reunion/list_objet.html.twig',
            ])
            ->add('meetingType', null, [
                'label' => 'Format',
                'template' => '@SonataAdmin/CRUD/reunion/list_meeting_type.html.twig',
            ])
            ->add('organisateur', null, [
                'label' => 'Organizer',
                'associated_property' => 'nameFr',
            ])
            ->add('_location', null, [
                'label' => 'Location',
                'template' => '@SonataAdmin/CRUD/reunion/list_location.html.twig',
            ])
            ->add('_participants', null, [
                'label' => 'Participants',
                'template' => '@SonataAdmin/CRUD/reunion/list_participants.html.twig',
            ])
            ->add('statut', null, [
                'label' => 'Status',
                'template' => '@SonataAdmin/CRUD/reunion/list_statut.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'validate' => ['template' => '@SonataAdmin/CRUD/reunion/list_validate_action.html.twig'],
                    'send_invitations' => ['template' => '@SonataAdmin/CRUD/reunion/list_send_invitations_action.html.twig'],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEditMode = $this->hasSubject() && $this->getSubject()->getId() !== null;

        $form
            ->tab('Basic Information')
                ->with('Meeting Details', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-primary'
                ])
                    ->add('objet', TextType::class, [
                        'label' => 'Meeting Subject',
                        'help' => 'Clear, concise subject/title for the meeting',
                    ])
                    ->add('type', TextType::class, [
                        'label' => 'Meeting Type',
                        'required' => false,
                        'help' => 'Type of meeting: Ordinary, Extraordinary, Emergency, etc.',
                    ])
                    ->add('dateDebut', DateTimeType::class, [
                        'label' => 'Start Date & Time',
                        'widget' => 'single_text',
                    ])
                    ->add('dateFin', DateTimeType::class, [
                        'label' => 'End Date & Time',
                        'widget' => 'single_text',
                    ])
                    ->add('organisateur', ModelType::class, [
                        'label' => 'Organizing Structure',
                        'class' => Structure::class,
                        'property' => 'nameFr',
                        'btn_add' => false,
                        'btn_delete' => false,
                    ])
                ->end()

                ->with('Quick Guide', [
                    'class' => 'col-md-4',
                    'box_class' => 'box box-primary'
                ])
                    ->add('_format_help', HtmlType::class, [
                        'html' => '
                            <div class="alert alert-info">
                                <h4><i class="icon fa fa-info-circle"></i> Meeting Formats</h4>
                                
                                <p><strong>🏢 In-Person:</strong></p>
                                <ul>
                                    <li>All participants attend physically</li>
                                    <li>Requires: Room or Location</li>
                                </ul>
                                
                                <p><strong>💻 Virtual:</strong></p>
                                <ul>
                                    <li>All participants join online</li>
                                    <li>Requires: Video conference link</li>
                                </ul>
                                
                                <p><strong>🔗 Hybrid:</strong></p>
                                <ul>
                                    <li>Some in-person, some virtual</li>
                                    <li>Requires: Both location AND video link</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-success">
                                <h4><i class="icon fa fa-lightbulb-o"></i> Tips</h4>
                                <ul>
                                    <li>Test video link before meeting</li>
                                    <li>Send link in invitations</li>
                                    <li>Include password if required</li>
                                    <li>Provide dial-in option if available</li>
                                </ul>
                            </div>
                        ',
                    ])
                ->end()
                
                ->with('Meeting Format', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-info'
                ])
                    ->add('meetingType', EnumType::class, [
                        'label' => 'Meeting Format',
                        'class' => MeetingTypeEnum::class,
                        'help' => 'How will participants attend this meeting?',
                        'attr' => [
                            'class' => 'meeting-type-selector',
                        ],
                        'choice_label' => function (MeetingTypeEnum $type) {
                            return $type->labelEn();
                        },
                    ])
                ->end()
                
                ->with('Physical Location', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-success',
                    'description' => 'Required for In-Person and Hybrid meetings'
                ])
                    ->add('salle', ModelType::class, [
                        'label' => 'Meeting Room',
                        'class' => MeetingRoom::class,
                        'property' => 'nom',
                        'required' => false,
                        'btn_add' => 'Add Room',
                        'btn_delete' => false,
                    ])
                    ->add('lieu', TextType::class, [
                        'label' => 'Location/Address',
                        'required' => false,
                        'help' => 'Physical address if not using a meeting room',
                    ])
                ->end()
                
                ->with('Video Conference Details', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-warning',
                    'description' => 'Required for Virtual and Hybrid meetings'
                ])
                    ->add('videoConferencePlatform', EnumType::class, [
                        'label' => 'Platform',
                        'class' => VideoConferencePlatform::class,
                        'required' => false,
                        'choice_label' => function (VideoConferencePlatform $platform) {
                            return $platform->label();
                        },
                        'placeholder' => 'Select platform...',
                    ])
                    ->add('videoConferenceLink', TextType::class, [
                        'label' => 'Meeting Link',
                        'required' => false,
                        'help' => 'Full URL to join the meeting',
                        'attr' => [
                            'placeholder' => 'https://zoom.us/j/1234567890 or https://meet.google.com/xxx-yyyy-zzz',
                        ],
                    ])
                    ->add('videoConferenceMeetingId', TextType::class, [
                        'label' => 'Meeting ID',
                        'required' => false,
                        'help' => 'Meeting ID or Room number (if applicable)',
                        'attr' => [
                            'placeholder' => 'e.g., 123 456 7890',
                        ],
                    ])
                    ->add('videoConferencePassword', TextType::class, [
                        'label' => 'Password/Passcode',
                        'required' => false,
                        'help' => 'Access password (if required)',
                        'attr' => [
                            'placeholder' => 'Enter meeting password',
                        ],
                    ])
                    ->add('videoConferenceInstructions', TextareaType::class, [
                        'label' => 'Additional Instructions',
                        'required' => false,
                        'help' => 'Any special instructions for joining',
                        'attr' => [
                            'rows' => 3,
                            'placeholder' => 'e.g., Please join 5 minutes early, ensure your camera is on, etc.',
                        ],
                    ])
                ->end()
                
                ->with('Key Roles', [
                    'class' => 'col-md-4',
                    'box_class' => 'box box-success'
                ])
                    ->add('president', ModelType::class, [
                        'label' => 'Meeting President',
                        'class' => Personnel::class,
                        'property' => 'nomComplet',
                        'required' => false,
                        'btn_add' => false,
                        'btn_delete' => false,
                    ])
                ->end()
            ->end()
            
            ->tab('Participants')
                ->with('Manage Participants', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-primary'
                ])
                    ->add('participations', CollectionType::class, [
                        'label' => 'Participants',
                        'by_reference' => false,
                        'help' => 'Add internal staff or external participants',
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
            ->end()
            
            ->tab('Agenda')
                ->with('Meeting Agenda', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-info'
                ])
                    ->add('agendaItems', CollectionType::class, [
                        'label' => 'Agenda Items',
                        'by_reference' => false,
                        'help' => 'Define the agenda for this meeting',
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
            ->end()
            ->tab('Action Items')
                ->with('Action Items')
                    ->add('actionItems', CollectionType::class, ['label' => 'Actions', 'by_reference' => false])
                ->end()
            ->end()
            ->tab('Report')
                ->with('Meeting Report')
                    ->add('compteRendu', CKEditorType::class, ['label' => 'Summary', 'required' => false])
                    ->add('statut', EnumType::class, [
                        'label' => 'Status', 
                        'class' => ReunionStatut::class,
                        'choice_label' => function (ReunionStatut $status) {
                            return $status->labelEn();
                        },
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Overview')
                ->with('Meeting Info', ['class' => 'col-md-6'])
                    ->add('objet', null, ['label' => 'Subject'])
                    ->add('type', null, ['label' => 'Type'])
                    ->add('dateDebut', null, ['label' => 'Start', 'format' => 'd/m/Y H:i'])
                    ->add('dateFin', null, ['label' => 'End', 'format' => 'd/m/Y H:i'])
                    ->add('organisateur', null, ['label' => 'Organizer'])
                    ->add('president', null, ['label' => 'President'])
                ->end()
                ->with('Location & Status', ['class' => 'col-md-6'])
                    ->add('salle', null, ['label' => 'Room'])
                    ->add('lieu', null, ['label' => 'Address'])
                    ->add('statut', null, ['label' => 'Status', 'template' => '@SonataAdmin/CRUD/reunion/show_status.html.twig'])
                ->end()
            ->end()
            ->tab('Participants')
                ->with('Participation', ['class' => 'col-md-12'])
                    ->add('_participants', 'html', ['template' => '@SonataAdmin/CRUD/reunion/show_participants.html.twig'])
                ->end()
            ->end()
            ->tab('Agenda & Actions')
                ->with('Agenda', ['class' => 'col-md-6'])
                    ->add('_agenda', 'html', ['template' => '@SonataAdmin/CRUD/reunion/show_agenda.html.twig'])
                ->end()
                ->with('Action Items', ['class' => 'col-md-6'])
                    ->add('_actions', 'html', ['template' => '@SonataAdmin/CRUD/reunion/show_actions.html.twig'])
                ->end()
            ->end()
            ->tab('Report')
                ->with('Meeting Summary')
                    ->add('compteRendu', null, ['label' => 'Report'])
                ->end()
            ->end();
    }

    public function toString(object $object): string
    {
        return $object instanceof Reunion ? $object->getObjet() : 'Meeting';
    }

    public function prePersist(object $object): void
    {
        if (!$object->getStatut()) {
            $object->setStatut(ReunionStatut::PLANNED);
        }
    }
}