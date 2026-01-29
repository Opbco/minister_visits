<?php

declare(strict_types=1);

namespace App\Admin;

use App\Controller\Admin\ReunionCRUDController;
use App\Entity\MeetingRoom;
use App\Entity\Personnel;
use App\Entity\Reunion;
use App\Entity\Structure;
use App\Enum\MeetingTypeEnum;
use App\Enum\ReunionStatut;
use App\Enum\VideoConferencePlatform;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

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
            
            // NEW: Filter by Any Participant (searches both internal and external)
            ->add('participations', CallbackFilter::class, [
                'label' => 'Any Participant (Name)',
                'callback' => function (ProxyQueryInterface $query, string $alias, string $field, FilterData $data) {
                    if (!$data->hasValue()) {
                        return false;
                    }

                    $searchTerm = '%' . strtolower($data->getValue()) . '%';

                    $query
                        ->leftJoin(sprintf('%s.participations', $alias), 'p_any')
                        ->leftJoin('p_any.personnel', 'personnel')
                        ->leftJoin('p_any.externalParticipant', 'external')
                        ->andWhere(
                            $query->expr()->orX(
                                'LOWER(personnel.nomComplet) LIKE :searchTerm',
                                'LOWER(external.nom) LIKE :searchTerm'
                            )
                        )
                        ->setParameter('searchTerm', $searchTerm);

                    return true;
                },
                'field_type' => TextType::class,
                'field_options' => [
                    'attr' => [
                        'placeholder' => 'Search by participant name...',
                    ],
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
            ->add('meetingType', null, [
                'label' => 'Meeting Format',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('statutIndicator', null, [
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
            ->add('lieu', null, [
                'label' => 'Location',
                'template' => '@SonataAdmin/CRUD/reunion/list_location.html.twig',
            ])
            ->add('participations', null, [
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
                    'validate' => ['template' => '@SonataAdmin/CRUD/reunion/list_validate_action.html.twig'],
                    'cancel' => ['template' => '@SonataAdmin/CRUD/reunion/list_cancel_action.html.twig'],
                    'postpone' => ['template' => '@SonataAdmin/CRUD/reunion/list_postpone_action.html.twig'],
                    'attendance' => ['template' => '@SonataAdmin/CRUD/reunion/list_attendance_action.html.twig'],
                    'complete' => ['template' => '@SonataAdmin/CRUD/reunion/list_complete_action.html.twig'],
                    'send_invitations' => ['template' => '@SonataAdmin/CRUD/reunion/list_send_invitations_action.html.twig'],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $subject = $this->getSubject();
        $rapportHelp = 'No report uploaded.';
        if ($subject && $subject->getRapport() && $subject->getRapport()->getFileName()) {
            $rapportHelp = sprintf(
                '<div class="alert alert-info mb-0"><i class="fa fa-file"></i> Current file: <strong>%s</strong></div>', 
                $subject->getRapport()->getFileName()
            );
        }

        $form
            ->tab('General')
                ->with('Identification', ['class' => 'col-md-8', 'box_class' => 'box box-primary'])
                    ->add('objet', TextType::class, [
                        'label' => 'Meeting Subject',
                        'help' => 'Brief description of the meeting purpose',
                    ])
                    ->add('type', TextType::class, [
                        'required' => false, 
                        'label' => 'Meeting Type',
                        'help' => 'e.g., Staff Meeting, Board Meeting, CODIR',
                    ])
                    ->add('statut', EnumType::class, [
                        'class' => ReunionStatut::class, 
                        'label' => 'Status',
                        'choice_label' => fn($enum) => $enum->label(),
                    ])
                ->end()
                
                ->with('Organization', ['class' => 'col-md-4', 'box_class' => 'box box-info'])
                    ->add('organisateur', ModelType::class, [
                        'class' => Structure::class,
                        'property' => 'nameFr',
                        'label' => 'Organizing Structure',
                        'help' => 'Department or structure organizing this meeting',
                    ])
                    ->add('president', ModelType::class, [
                        'class' => Personnel::class,
                        'property' => 'nomComplet',
                        'label' => 'Chairperson',
                        'required' => false,
                        'help' => 'Person who will chair the meeting',
                    ])
                ->end()
                
                ->with('Schedule', ['class' => 'col-md-12', 'box_class' => 'box box-success'])
                    ->add('dateDebut', DateTimePickerType::class, [
                        'label' => 'Start Date & Time',
                        'format' => 'dd/MM/yyyy HH:mm',
                    ])
                    ->add('dateFin', DateTimePickerType::class, [
                        'label' => 'End Date & Time',
                        'format' => 'dd/MM/yyyy HH:mm',
                    ])
                ->end()
                
                ->with('Meeting Format', ['class' => 'col-md-12', 'box_class' => 'box box-warning'])
                    ->add('meetingType', EnumType::class, [
                        'class' => MeetingTypeEnum::class,
                        'label' => 'Meeting Format',
                        'choice_label' => fn($enum) => $enum->label(),
                        'help' => 'Select whether this is in-person, virtual, or hybrid',
                    ])
                ->end()
                
                ->with('Physical Location', ['class' => 'col-md-6', 'box_class' => 'box box-default'])
                    ->add('salle', ModelType::class, [
                        'class' => MeetingRoom::class,
                        'property' => 'nom',
                        'required' => false,
                        'label' => 'Meeting Room',
                        'placeholder' => 'Select a room',
                        'help' => 'For in-person and hybrid meetings',
                    ])
                    ->add('lieu', TextType::class, [
                        'required' => false, 
                        'label' => 'External Location',
                        'help' => 'Fill only if meeting is outside ministry premises',
                    ])
                ->end()
                
                ->with('Video Conference Details', ['class' => 'col-md-6', 'box_class' => 'box box-default'])
                    ->add('videoConferencePlatform', EnumType::class, [
                        'class' => VideoConferencePlatform::class,
                        'required' => false,
                        'label' => 'Platform',
                        'choice_label' => fn($enum) => $enum->label(),
                        'placeholder' => 'Select platform',
                        'help' => 'For virtual and hybrid meetings',
                    ])
                    ->add('videoConferenceLink', UrlType::class, [
                        'required' => false,
                        'label' => 'Meeting Link',
                        'help' => 'URL to join the video conference',
                    ])
                    ->add('videoConferenceMeetingId', TextType::class, [
                        'required' => false,
                        'label' => 'Meeting ID',
                        'help' => 'Meeting ID or access code',
                    ])
                    ->add('videoConferencePassword', TextType::class, [
                        'required' => false,
                        'label' => 'Password',
                        'help' => 'Password to join the meeting',
                    ])
                ->end()
            ->end()

            /* ->tab('Participants')
                ->with('Attendance List', ['class' => 'col-md-12', 'box_class' => 'box box-primary'])
                    ->add('participations', CollectionType::class, [
                        'by_reference' => false,
                        'label' => false,
                        'btn_add' => 'Add Participant',
                        'type_options' => [
                            'delete' => true,
                        ]
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                        'admin_code' => 'admin.reunion_participation',
                    ])
                ->end()
            ->end()

            ->tab('Agenda & Actions')
                ->with('Agenda Items', ['class' => 'col-md-12', 'box_class' => 'box box-info'])
                    ->add('agendaItems', CollectionType::class, [
                        'by_reference' => false,
                        'label' => false,
                        'btn_add' => 'Add Agenda Item',
                        'type_options' => [
                            'delete' => true,
                        ]
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'ordre',
                        'admin_code' => 'admin.agenda_item',
                    ])
                ->end()
                
                ->with('Action Items', ['class' => 'col-md-12', 'box_class' => 'box box-warning'])
                    ->add('actionItems', CollectionType::class, [
                        'by_reference' => false,
                        'label' => false,
                        'btn_add' => 'Add Action Item',
                        'type_options' => [
                            'delete' => true,
                        ]
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                        'admin_code' => 'admin.action_item',
                    ])
                ->end()
            ->end() */

            ->tab('Report & Documents')
                ->with('Official Report (Minutes)', ['class' => 'col-md-12', 'box_class' => 'box box-success'])
                    ->add('compteRendu', CKEditorType::class, [
                        'required' => false,
                        'label' => 'Summary Notes (Text)',
                        'config' => [
                            'toolbar' => 'standard',
                        ],
                    ])
                    ->add('rapport', ModelListType::class, [
                        'btn_add' => 'Upload Report',
                        'btn_list' => false,
                        'btn_delete' => 'Remove',
                        'label' => 'Report File (PDF)',
                        'required' => false,
                        'help_html' => true,
                        'help' => $rapportHelp,
                    ], [
                        'admin_code' => 'admin.document',
                    ])
                ->end()
                
                ->with('Attached Documents (Presentations, Lists...)', ['class' => 'col-md-12', 'box_class' => 'box box-default'])
                    ->add('documents', CollectionType::class, [
                        'by_reference' => false,
                        'label' => false,
                        'btn_add' => 'Add Document',
                        'type_options' => [
                            'delete' => true,
                        ]
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                        'admin_code' => 'admin.document',
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Overview')
                ->with('Meeting Info', ['class' => 'col-md-6', 'box_class' => 'box box-primary'])
                    ->add('objet', null, ['label' => 'Subject'])
                    ->add('type', null, ['label' => 'Type'])
                    ->add('dateDebut', null, ['label' => 'Start', 'format' => 'd/m/Y H:i'])
                    ->add('dateFin', null, ['label' => 'End', 'format' => 'd/m/Y H:i'])
                    ->add('organisateur', null, ['label' => 'Organizer'])
                    ->add('president', null, ['label' => 'Chairperson'])
                ->end()
                
                ->with('Location & Status', ['class' => 'col-md-6', 'box_class' => 'box box-info'])
                    ->add('meetingType', null, [
                        'label' => 'Format',
                        'template' => '@SonataAdmin/CRUD/reunion/show_meeting_type.html.twig',
                    ])
                    ->add('salle', null, ['label' => 'Room'])
                    ->add('lieu', null, ['label' => 'Address'])
                    ->add('videoConference', null, [
                        'label' => 'Video Conference',
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/reunion/show_video_conference.html.twig',
                    ])
                    ->add('statut', null, [
                        'label' => 'Status',
                        'template' => '@SonataAdmin/CRUD/reunion/show_status.html.twig',
                    ])
                ->end()
            ->end()
            
            ->tab('Participants')
                ->with('Participation', ['class' => 'col-md-12', 'box_class' => 'box box-primary'])
                    ->add('participations', null, [
                        'template' => '@SonataAdmin/CRUD/reunion/show_participants.html.twig',
                    ])
                ->end()
            ->end()
            
            ->tab('Agenda')
                ->with('Agenda', ['class' => 'col-md-12', 'box_class' => 'box box-info'])
                    ->add('agendaItems', null, [
                        'template' => '@SonataAdmin/CRUD/reunion/show_agenda.html.twig',
                    ])
                ->end()
            ->end()

            ->tab('Actions')
                ->with('Action Items', ['class' => 'col-md-12', 'box_class' => 'box box-warning'])
                    ->add('actionItems', null, [
                        'template' => '@SonataAdmin/CRUD/reunion/show_actions.html.twig',
                    ])
                ->end()
            ->end()
            
            ->tab('Report')
                ->with('Meeting Summary', ['class' => 'col-md-12', 'box_class' => 'box box-success'])
                    ->add('compteRendu', null, [
                        'label' => 'Report',
                        'safe' => true,
                    ])
                ->end()
            ->end()
        ;
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
        
        // Handle nested collections
        $this->handleNestedCollections($object);
    }

    public function preUpdate(object $object): void
    {
        // Handle nested collections
        $this->handleNestedCollections($object);
    }

    private function handleNestedCollections(Reunion $reunion): void
    {
        // Set reunion for agenda items
        foreach ($reunion->getAgendaItems() as $agendaItem) {
            if (!$agendaItem->getReunion()) {
                $agendaItem->setReunion($reunion);
            }
        }

        // Set reunion for action items
        foreach ($reunion->getActionItems() as $actionItem) {
            if (!$actionItem->getReunion()) {
                $actionItem->setReunion($reunion);
            }
        }

        // Set reunion for participations
        foreach ($reunion->getParticipations() as $participation) {
            if (!$participation->getReunion()) {
                $participation->setReunion($reunion);
            }
        }

        // Set reunion for documents
        foreach ($reunion->getDocuments() as $document) {
            if (!$document->getReunion()) {
                $document->setReunion($reunion);
            }
        }
    }

    protected function configureTabMenu(ItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        // 1. Only show tabs on Edit or Show pages
        if (!$childAdmin && !in_array($action, ['edit'], true)) {
            return;
        }

        // 2. Get the current Event ID
        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        // 3. Add menu items
        $menu->addChild('Overview', [
            'uri' => $admin->generateUrl('show', ['id' => $id]),
        ]);

        $menu->addChild('Participants', [
            'uri' => $admin->generateUrl('admin.reunion_participation.list', ['id' => $id]),
            'attributes' => ['class' => 'nav-item'],
            'linkAttributes' => ['class' => 'nav-link']
        ]);

        $menu->addChild('Agenda', [
            'uri' => $admin->generateUrl('admin.agenda_item.list', ['id' => $id]),
            'attributes' => ['class' => 'nav-item'],
            'linkAttributes' => ['class' => 'nav-link']
        ]);

        $menu->addChild('Actions', [
            'uri' => $admin->generateUrl('admin.action_item.list', ['id' => $id]),
            'attributes' => ['class' => 'nav-item'],
            'linkAttributes' => ['class' => 'nav-link']
        ]);

        
    }
}