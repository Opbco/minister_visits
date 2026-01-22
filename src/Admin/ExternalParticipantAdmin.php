<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\ExternalParticipant;
use App\Form\Type\HtmlType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\TemplateType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

final class ExternalParticipantAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'nom';
        $sortValues['_sort_order'] = 'ASC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('meetings', $this->getRouterIdParameter().'/meetings');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nom', null, [
                'label' => 'Name',
                'show_filter' => true,
            ])
            ->add('organisation', null, [
                'label' => 'Organization',
            ])
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('telephone', null, [
                'label' => 'Phone',
            ])
            ->add('fonction', null, [
                'label' => 'Function/Role',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('nom', null, [
                'label' => 'Name',
                'editable' => true,
            ])
            ->add('organisation', null, [
                'label' => 'Organization',
                'editable' => true,
                'template' => '@SonataAdmin/CRUD/external_participant/list_organisation.html.twig',
            ])
            ->add('fonction', null, [
                'label' => 'Function/Role',
                'editable' => true,
            ])
            ->add('email', null, [
                'label' => 'Email',
                'template' => '@SonataAdmin/CRUD/external_participant/list_email.html.twig',
            ])
            ->add('telephone', null, [
                'label' => 'Phone',
                'template' => '@SonataAdmin/CRUD/external_participant/list_phone.html.twig',
            ])
            ->add('myReunions', null, [
                'label' => 'Meetings',
                'template' => '@SonataAdmin/CRUD/external_participant/list_meeting_count.html.twig',
            ])
            ->add('date_created', null, [
                'label' => 'Added On',
                'format' => 'd/m/Y',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'meetings' => [
                        'template' => '@SonataAdmin/CRUD/external_participant/list_meetings_action.html.twig'
                    ],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEditMode = $this->hasSubject() && $this->getSubject()->getId() !== null;

        $form
            ->with('Contact Information', [
                'class' => 'col-md-8',
                'box_class' => 'box box-primary'
            ])
                ->add('nom', TextType::class, [
                    'label' => 'Full Name',
                    'help' => 'Complete name of the external participant',
                    'attr' => [
                        'placeholder' => 'e.g., Dr. John Smith',
                    ],
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email Address',
                    'required' => false,
                    'help' => 'Email for meeting invitations and notifications',
                    'attr' => [
                        'placeholder' => 'john.smith@company.com',
                    ],
                ])
                ->add('telephone', TelType::class, [
                    'label' => 'Phone Number',
                    'required' => false,
                    'help' => 'Phone number for SMS notifications',
                    'attr' => [
                        'placeholder' => '+237 6XX XXX XXX',
                        'maxlength' => 30,
                    ],
                ])
            ->end()

            ->with('Important Notes', [
                'class' => 'col-md-4',
                'box_class' => 'box box-warning'
            ])
                ->add('_contact_requirement', HtmlType::class, [
                    'html' => '
                        <div class="alert alert-warning">
                            <h4><i class="icon fa fa-warning"></i> Contact Information Required!</h4>
                            <p>At least <strong>email</strong> or <strong>phone number</strong> must be provided.</p>
                            <ul>
                                <li><strong>Email:</strong> For meeting invitations, reports, and email notifications</li>
                                <li><strong>Phone:</strong> For SMS reminders and urgent notifications</li>
                            </ul>
                            <p class="text-info">
                                <i class="fa fa-info-circle"></i> <strong>Best practice:</strong> Provide both for maximum reach.
                            </p>
                        </div>
                        
                        <div class="alert alert-info">
                            <h4><i class="icon fa fa-users"></i> External Participants</h4>
                            <p>External participants are:</p>
                            <ul>
                                <li>Consultants and advisors</li>
                                <li>Guest speakers</li>
                                <li>Partner organization representatives</li>
                                <li>Any non-staff attendees</li>
                            </ul>
                        </div>
                    ',
                ])
            ->end()
            
            ->with('Professional Details', [
                'class' => 'col-md-8',
                'box_class' => 'box box-info'
            ])
                ->add('organisation', TextType::class, [
                    'label' => 'Organization',
                    'required' => false,
                    'help' => 'Company, institution, or organization',
                    'attr' => [
                        'placeholder' => 'e.g., XYZ Consulting Ltd',
                    ],
                ])
                ->add('fonction', TextType::class, [
                    'label' => 'Function/Role',
                    'required' => false,
                    'help' => 'Job title or role in the organization',
                    'attr' => [
                        'placeholder' => 'e.g., Senior Consultant',
                    ],
                ])
            ->end()
        ;

        if ($isEditMode) {
            $form
                ->with('Meeting Participation', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-success'
                ])
                    ->add('_meeting_history', TemplateType::class, [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/external_participant/form_meeting_history.html.twig',
                    ])
                ->end()
            ;
        }
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Profile')
                ->with('Contact Information', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-primary'
                ])
                    ->add('id', null, [
                        'label' => 'ID',
                    ])
                    ->add('nom', null, [
                        'label' => 'Full Name',
                    ])
                    ->add('email', null, [
                        'label' => 'Email',
                        'template' => '@SonataAdmin/CRUD/personnel/show_email.html.twig',
                    ])
                    ->add('telephone', null, [
                        'label' => 'Phone',
                        'template' => '@SonataAdmin/CRUD/personnel/show_phone.html.twig',
                    ])
                ->end()
                
                ->with('Professional Details', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-info'
                ])
                    ->add('organisation', null, [
                        'label' => 'Organization',
                    ])
                    ->add('fonction', null, [
                        'label' => 'Function/Role',
                    ])
                    ->add('date_created', null, [
                        'label' => 'Added On',
                        'format' => 'd/m/Y H:i',
                    ])
                    ->add('user_created', null, [
                        'label' => 'Added By',
                        'associated_property' => 'username',
                    ])
                ->end()
            ->end()
            
            ->tab('Meeting History')
                ->with('Participation', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-success'
                ])
                    ->add('myReunions', null, [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/external_participant/show_meetings.html.twig',
                    ])
                ->end()
            ->end()
            
            ->tab('Statistics')
                ->with('Participation Metrics', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-primary'
                ])
                    ->add('statistics', 'html', [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/external_participant/show_statistics.html.twig',
                    ])
                ->end()
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof ExternalParticipant
            ? sprintf('%s (%s)', 
                    $object->getNom(), 
                    $object->getOrganisation() ?? $object->getEmail() ?? 'External'
                )
            : 'External Participant';
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Full Name' => 'nom',
            'Organization' => 'organisation',
            'Function' => 'fonction',
            'Email' => 'email',
            'Phone' => 'telephone',
            'Added On' => 'date_created',
        ];
    }

    public function configureBatchActions(array $actions): array
    {
        if ($this->hasRoute('edit') && $this->hasAccess('edit')) {
            $actions['export_contacts'] = [
                'label' => 'Export Contacts (CSV)',
                'ask_confirmation' => false,
            ];
            
            $actions['send_notification'] = [
                'label' => 'Send Notification',
                'ask_confirmation' => true,
            ];
        }

        return $actions;
    }

    public function preValidate(object $object): void
    {
        // Ensure at least email or phone is provided
        if (empty($object->getEmail()) && empty($object->getTelephone())) {
            throw new \InvalidArgumentException('Either email or phone number must be provided.');
        }
    }
}