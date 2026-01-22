<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\MeetingRoom;
use App\Entity\Structure;
use App\Form\Type\HtmlType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class MeetingRoomAdmin extends AbstractAdmin
{
    private const COMMON_EQUIPMENT = [
        'Projector' => 'Projector',
        'Video Conference' => 'Video Conference',
        'Whiteboard' => 'Whiteboard',
        'Air Conditioning' => 'Air Conditioning',
        'Sound System' => 'Sound System',
        'WiFi' => 'WiFi',
        'TV Screen' => 'TV Screen',
        'Flip Chart' => 'Flip Chart',
        'Conference Phone' => 'Conference Phone',
        'Recording Equipment' => 'Recording Equipment',
    ];

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'nom';
        $sortValues['_sort_order'] = 'ASC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('schedule', $this->getRouterIdParameter().'/schedule');
        $collection->add('exportSchedule', $this->getRouterIdParameter().'/export-schedule');
        $collection->add('checkAvailability', 'check-availability');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nom', null, [
                'label' => 'Room Name',
                'show_filter' => true,
            ])
            ->add('structure', null, [
                'label' => 'Structure',
            ])
            ->add('capacite', null, [
                'label' => 'Capacity',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('nom', null, [
                'label' => 'Room Name',
                'editable' => true,
            ])
            ->add('structure', null, [
                'label' => 'Location/Structure',
                'associated_property' => 'nameFr',
                'template' => '@SonataAdmin/CRUD/meeting_room/list_structure.html.twig',
            ])
            ->add('capacite', null, [
                'label' => 'Capacity',
                'editable' => true,
                'template' => '@SonataAdmin/CRUD/meeting_room/list_capacity.html.twig',
            ])
            ->add('equipements', null, [
                'label' => 'Equipment',
                'template' => '@SonataAdmin/CRUD/meeting_room/list_equipment.html.twig',
            ])
            ->add('available', null, [
                'label' => 'Status',
                'template' => '@SonataAdmin/CRUD/meeting_room/list_availability.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'schedule' => [
                        'template' => '@SonataAdmin/CRUD/meeting_room/list_schedule_action.html.twig'
                    ],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Room Information', [
                'class' => 'col-md-8',
                'box_class' => 'box box-primary'
            ])
                ->add('nom', TextType::class, [
                    'label' => 'Room Name',
                    'help' => 'Name or identifier for the meeting room',
                    'attr' => [
                        'placeholder' => 'e.g., Conference Room A, Board Room, Training Hall',
                    ],
                ])
                ->add('structure', ModelType::class, [
                    'label' => 'Structure/Location',
                    'class' => Structure::class,
                    'property' => 'nameFr',
                    'required' => false,
                    'btn_add' => false,
                    'btn_delete' => false,
                    'help' => 'The structure/building where this room is located',
                    'query' => $this->getModelManager()->createQuery(Structure::class, 'o')
                        ->where('o.category = :category')
                        ->setParameter('category', 1)
                        ->orderBy('o.nameFr', 'ASC'),
                ])
                ->add('capacite', IntegerType::class, [
                    'label' => 'Seating Capacity',
                    'required' => false,
                    'help' => 'Maximum number of people the room can accommodate',
                    'attr' => [
                        'placeholder' => 'e.g., 20',
                        'min' => 1,
                    ],
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Additional details about the room',
                    'attr' => [
                        'rows' => 4,
                        'placeholder' => 'Describe the room layout, special features, access requirements, etc.',
                    ],
                ])
            ->end()

            ->with('Room Features', [
                'class' => 'col-md-4',
                'box_class' => 'box box-success'
            ])
                ->add('_features_info', HtmlType::class, [
                    'html' => '
                        <div class="alert alert-success">
                            <h4><i class="icon fa fa-info-circle"></i> Room Management</h4>
                            <p><strong>Essential Information:</strong></p>
                            <ul>
                                <li>Room name for easy identification</li>
                                <li>Capacity for meeting planning</li>
                                <li>Equipment list for requirements</li>
                            </ul>
                            <p class="text-info">
                                <i class="fa fa-lightbulb-o"></i> <strong>Tip:</strong> Keep equipment list updated for accurate planning!
                            </p>
                        </div>
                        
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Future Enhancement</span>
                                <span class="info-box-number">Room Booking</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 30%"></div>
                                </div>
                                <span class="progress-description">
                                    Coming soon: Check availability & book rooms
                                </span>
                            </div>
                        </div>
                    ',
                ])
            ->end()
            
            ->with('Equipment & Facilities', [
                'class' => 'col-md-8',
                'box_class' => 'box box-info'
            ])
                ->add('equipements', ChoiceType::class, [
                    'label' => 'Available Equipment',
                    'choices' => self::COMMON_EQUIPMENT,
                    'multiple' => true,
                    'required' => false,
                    'help' => 'Select all equipment available in this room',
                    'attr' => [
                        'class' => 'select2',
                        'style' => 'width: 100%',
                    ],
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Room Details', [
                'class' => 'col-md-6',
                'box_class' => 'box box-primary'
            ])
                ->add('id', null, [
                    'label' => 'ID',
                ])
                ->add('nom', null, [
                    'label' => 'Room Name',
                ])
                ->add('structure', null, [
                    'label' => 'Structure/Location',
                    'associated_property' => 'nameFr',
                ])
                ->add('capacite', null, [
                    'label' => 'Seating Capacity',
                    'template' => '@SonataAdmin/CRUD/meeting_room/show_capacity.html.twig',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'safe' => false,
                ])
            ->end()
            
            ->with('Facilities', [
                'class' => 'col-md-6',
                'box_class' => 'box box-info'
            ])
                ->add('equipements', null, [
                    'label' => 'Available Equipment',
                    'template' => '@SonataAdmin/CRUD/meeting_room/show_equipment.html.twig',
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
            
            ->with('Usage Statistics', [
                'class' => 'col-md-12',
                'box_class' => 'box box-success'
            ])
                ->add('usage_stats', 'html', [
                    'label' => false,
                    'mapped' => false,
                    'template' => '@SonataAdmin/CRUD/meeting_room/show_usage_stats.html.twig',
                ])
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof MeetingRoom
            ? sprintf('%s (%s)', 
                $object->getNom(),
                $object->getStructure() ? $object->getStructure()->getNameFr() : 'No location'
            )
            : 'Meeting Room';
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Room Name' => 'nom',
            'Structure' => 'structure.nameFr',
            'Capacity' => 'capacite',
            'Equipment' => function($object) {
                return implode(', ', $object->getEquipements() ?? []);
            },
            'Description' => 'description',
        ];
    }

    public function prePersist(object $object): void
    {
        // Ensure equipements is an array
        if ($object->getEquipements() === null) {
            $object->setEquipements([]);
        }
    }

    public function preUpdate(object $object): void
    {
        // Ensure equipements is an array
        if ($object->getEquipements() === null) {
            $object->setEquipements([]);
        }
    }
}