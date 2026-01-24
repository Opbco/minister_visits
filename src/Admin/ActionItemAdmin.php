<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\ActionItem;
use App\Entity\Personnel;
use App\Entity\Reunion;
use App\Enum\ActionStatut;
use App\Form\Type\HtmlType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class ActionItemAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'dateEcheance';
        $sortValues['_sort_order'] = 'ASC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        if($this->hasParentFieldDescription()) {
            // Disable custom routes in embedded mode
            return;
        }
        if(!$this->isChild()) {
            $collection->clearExcept(['list', 'show', 'export']);
        }
        // Add custom routes
        $collection->add('mark_completed', $this->getRouterIdParameter().'/mark-completed');
        $collection->add('mark_in_progress', $this->getRouterIdParameter().'/mark-in-progress');
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
            ->add('responsable', null, [
                'label' => 'Responsible Person',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'nomComplet',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('statut', null, [
                'label' => 'Status',
            ])
            ->add('dateEcheance', null, [
                'label' => 'Due Date',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('priority', null, [
                'label' => '',
                'template' => '@SonataAdmin/CRUD/action_item/list_priority_indicator.html.twig',
            ])
            ->add('description', null, [
                'label' => 'Action Item',
                'template' => '@SonataAdmin/CRUD/action_item/list_description.html.twig',
            ])
            ->add('reunion', null, [
                'label' => 'Meeting',
                'associated_property' => 'objet',
                'template' => '@SonataAdmin/CRUD/action_item/list_reunion.html.twig',
            ])
            ->add('responsable', null, [
                'label' => 'Responsible',
                'associated_property' => 'nomComplet',
                'template' => '@SonataAdmin/CRUD/action_item/list_responsable.html.twig',
            ])
            ->add('dateEcheance', null, [
                'label' => 'Due Date',
                'format' => 'd/m/Y',
                'template' => '@SonataAdmin/CRUD/action_item/list_due_date.html.twig',
            ])
            ->add('statut', null, [
                'label' => 'Status',
                'template' => '@SonataAdmin/CRUD/action_item/list_status.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'mark_completed' => [
                        'template' => '@SonataAdmin/CRUD/action_item/list_mark_completed_action.html.twig'
                    ],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEditMode = $this->hasSubject() && $this->getSubject()->getId() !== null;
        $isOverdue = $isEditMode && $this->getSubject()->getDateEcheance() 
            && $this->getSubject()->getDateEcheance() < new \DateTime()
            && $this->getSubject()->getStatut()->value !== 'completed';
        // Check if we're in embedded mode (within Reunion form)
        $isEmbedded = $this->hasParentFieldDescription();
        $isChild = $this->isChild();

        $form
            ->with('Action Details', [
                'class' => 'col-md-8',
                'box_class' => 'box box-primary'
            ])
                ->ifTrue(!$isEmbedded && !$isChild)
                    ->add('reunion', ModelType::class, [
                            'label' => 'Meeting',
                            'class' => Reunion::class,
                            'property' => 'objet',
                            'btn_add' => false,
                            'btn_delete' => false,
                            'disabled' => $isEditMode, // Can't change meeting after creation
                            'help' => $isEditMode ? 'Meeting cannot be changed' : 'Select the meeting this action item was created in',
                        ])
                ->ifEnd()
                ->add('description', CKEditorType::class, [
                    'label' => 'Action Description',
                    'help' => 'Clear description of what needs to be done',
                    'attr' => [
                        'placeholder' => 'Describe the action item in detail...',
                    ],
                ])
                ->add('responsable', ModelType::class, [
                    'label' => 'Responsible Person',
                    'class' => Personnel::class,
                    'property' => 'nomComplet',
                    'required' => false,
                    'btn_add' => false,
                    'btn_delete' => false,
                    'help' => 'Person responsible for completing this action',
                ])
                ->add('dateEcheance', DateType::class, [
                    'label' => 'Due Date',
                    'required' => false,
                    'widget' => 'single_text',
                    'help' => 'Deadline for completing this action',
                    'attr' => [
                        'min' => date('Y-m-d'),
                    ],
                ])
            ->end()
            ->with('Quick Info', [
                'class' => 'col-md-4',
                'box_class' => $isOverdue ? 'box box-danger' : 'box box-info'
            ])
                ->add('_info', HtmlType::class, [
                    'html' => '
                        <div class="alert alert-info">
                            <h4><i class="icon fa fa-info-circle"></i> Action Item Status</h4>
                            <ul>
                                <li><strong>Pending:</strong> Not yet started</li>
                                <li><strong>In Progress:</strong> Currently being worked on</li>
                                <li><strong>Completed:</strong> Action finished</li>
                                <li><strong>Cancelled:</strong> No longer needed</li>
                            </ul>
                        </div>
                        
                        ' . ($isOverdue ? '
                        <div class="alert alert-danger">
                            <h4><i class="icon fa fa-warning"></i> OVERDUE!</h4>
                            <p>This action item is past its due date and requires immediate attention.</p>
                        </div>
                        ' : '') . '
                        
                        ' . ($isEditMode ? '
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Created On</span>
                                <span class="info-box-number">' . $this->getSubject()->getDateCreated()->format('d/m/Y') . '</span>
                            </div>
                        </div>
                        ' : '') . '
                    ',
                ])
            ->end()
            ->ifTrue($isEditMode) 
                ->with('Status & Progress', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-success'
                ])
                    ->add('statut', EnumType::class, [
                        'label' => 'Status',
                        'class' => ActionStatut::class,
                        'choice_label' => function (ActionStatut $status) {
                                return $status->labelEn();
                            },
                        'help' => 'Current status of this action item',
                    ])
                    ->add('commentaire', CKEditorType::class, [
                        'label' => 'Comments/Notes',
                        'required' => false,
                        'help' => 'Progress updates, challenges, or additional notes',
                        'attr' => [
                            'rows' => 4,
                            'placeholder' => 'Add updates, progress notes, or challenges encountered...',
                        ],
                    ])
                ->end()
            ->ifEnd()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Action Item Details', [
                'class' => 'col-md-8',
                'box_class' => 'box box-primary'
            ])
                ->add('description', FieldDescriptionInterface::TYPE_HTML, [
                    'label' => 'Action Description',
                    'safe' => false,
                ])
                ->add('reunion', null, [
                    'label' => 'Meeting',
                    'associated_property' => 'objet',
                    'template' => '@SonataAdmin/CRUD/action_item/show_reunion.html.twig',
                ])
                ->add('responsable', null, [
                    'label' => 'Responsible Person',
                    'template' => '@SonataAdmin/CRUD/action_item/show_responsable.html.twig',
                ])
            ->end()
            
            ->with('Timeline & Status', [
                'class' => 'col-md-4',
                'box_class' => 'box box-success'
            ])
                ->add('dateEcheance', null, [
                    'label' => 'Due Date',
                    'format' => 'd/m/Y',
                    'template' => '@SonataAdmin/CRUD/action_item/show_due_date.html.twig',
                ])
                ->add('statut', null, [
                    'label' => 'Status',
                    'template' => '@SonataAdmin/CRUD/action_item/show_status.html.twig',
                ])
                ->add('date_created', null, [
                    'label' => 'Created On',
                    'format' => 'd/m/Y H:i',
                ])
                ->add('date_updated', null, [
                    'label' => 'Last Updated',
                    'format' => 'd/m/Y H:i',
                ])
            ->end()
            
            ->with('Progress Notes', [
                'class' => 'col-md-12',
                'box_class' => 'box box-info'
            ])
                ->add('commentaire', null, [
                    'label' => 'Comments/Updates',
                    'safe' => false,
                ])
            ->end()
            
            ->with('Meeting Context', [
                'class' => 'col-md-12',
                'box_class' => 'box box-warning'
            ])
                ->add('id', null, [
                    'label' => false,
                    'mapped' => false,
                    'template' => '@SonataAdmin/CRUD/action_item/show_meeting_context.html.twig',
                ])
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof ActionItem
            ?  'Action item ref: ' . $object->getId()
            : 'Action Item';
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Description' => 'description',
            'Meeting' => 'reunion.objet',
            'Meeting Date' => 'reunion.dateDebut',
            'Responsible' => 'responsable.nomComplet',
            'Due Date' => 'dateEcheance',
            'Status' => 'statut',
            'Comments' => 'commentaire',
            'Created' => 'date_created',
        ];
    }

    public function configureBatchActions(array $actions): array
    {
        if ($this->hasRoute('edit') && $this->hasAccess('edit')) {
            $actions['mark_completed'] = [
                'label' => 'Mark as Completed',
                'ask_confirmation' => true,
            ];
            
            $actions['mark_in_progress'] = [
                'label' => 'Mark as In Progress',
                'ask_confirmation' => true,
            ];
            
            $actions['export_report'] = [
                'label' => 'Export Action Items Report',
                'ask_confirmation' => false,
            ];
        }

        return $actions;
    }

    public function preUpdate(object $object): void
    {
        // Auto-update timestamp
        $object->setDateUpdated(new \DateTime());
    }

    public function prePersist(object $object): void
    {
        // Set initial status if not provided
        if (!$object->getStatut()) {
            $object->setStatut(ActionStatut::PENDING);
        }
    }
}