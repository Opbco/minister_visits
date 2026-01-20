<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Fonction;
use App\Entity\Personnel;
use App\Entity\Structure;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

final class PersonnelAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'nomComplet';
        $sortValues['_sort_order'] = 'ASC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        // Add custom routes
        $collection->add('meetings', $this->getRouterIdParameter().'/meetings');
        $collection->add('action_items', $this->getRouterIdParameter().'/action-items');
        $collection->add('create_user_account', $this->getRouterIdParameter().'/create-user-account');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nomComplet', null, [
                'label' => 'Full Name',
                'show_filter' => true,
            ])
            ->add('matricule', null, [
                'label' => 'Staff Number',
            ])
            ->add('fonction', null, [
                'label' => 'Function',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'libelle',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('structure', null, [
                'label' => 'Structure',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'nameFr',
                    'minimum_input_length' => 2,
                ],
            ])
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('telephone', null, [
                'label' => 'Phone',
            ])
            ->add('userAccount', null, [
                'label' => 'Has User Account',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('matricule', null, [
                'label' => 'Staff #',
                'editable' => false,
            ])
            ->add('nomComplet', null, [
                'label' => 'Full Name',
                'editable' => true,
            ])
            ->add('fonction', null, [
                'label' => 'Function',
                'associated_property' => 'libelle',
                'template' => '@SonataAdmin/CRUD/personnel/list_fonction.html.twig',
            ])
            ->add('structure', null, [
                'label' => 'Structure',
                'associated_property' => 'nameFr',
                'template' => '@SonataAdmin/CRUD/personnel/list_structure.html.twig',
            ])
            ->add('email', null, [
                'label' => 'Email',
                'template' => '@SonataAdmin/CRUD/fields/list_email.html.twig',
            ])
            ->add('telephone', null, [
                'label' => 'Phone',
            ])
            ->add('userAccount', null, [
                'label' => 'Account',
                'template' => '@SonataAdmin/CRUD/personnel/list_user_account.html.twig',
            ])
            ->add('_meetingStats', null, [
                'label' => 'Meetings',
                'template' => '@SonataAdmin/CRUD/personnel/list_meeting_stats.html.twig',
                'virtual_field' => true,
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'meetings' => [
                        'template' => '@SonataAdmin/CRUD/personnel/list_meetings_action.html.twig'
                    ],
                    'action_items' => [
                        'template' => '@SonataAdmin/CRUD/personnel/list_action_items_action.html.twig'
                    ],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEditMode = $this->hasSubject() && $this->getSubject()->getId() !== null;
        
        $form
            ->tab('Basic Information')
                ->with('Personal Information', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-primary'
                ])
                    ->add('nomComplet', TextType::class, [
                        'label' => 'Full Name',
                        'help' => 'Complete name of the staff member (will be converted to uppercase)',
                        'attr' => [
                            'placeholder' => 'Enter full name',
                        ],
                    ])
                    ->add('matricule', TextType::class, [
                        'label' => 'Staff Number',
                        'required' => false,
                        'help' => 'Unique employee identification number',
                        'attr' => [
                            'placeholder' => 'e.g., EMP-2024-001',
                            'maxlength' => 100,
                        ],
                    ])
                ->end()
                
                ->with('Position Details', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-info'
                ])
                    ->add('fonction', ModelType::class, [
                        'label' => 'Function',
                        'class' => Fonction::class,
                        'property' => 'libelle',
                        'btn_add' => 'Add Function',
                        'btn_delete' => false,
                        'help' => 'Job function/position of the staff member',
                    ])
                    ->add('structure', ModelType::class, [
                        'label' => 'Structure',
                        'class' => Structure::class,
                        'property' => 'nameFr',
                        'btn_add' => false,
                        'btn_delete' => false,
                        'help' => 'Organizational unit where the staff member works',
                    ])
                ->end()
            ->end()
            
            ->tab('Contact Information')
                ->with('Contact Details', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-success'
                ])
                    ->add('email', EmailType::class, [
                        'label' => 'Email Address',
                        'required' => false,
                        'help' => 'Official email address for notifications',
                        'attr' => [
                            'placeholder' => 'name@example.com',
                        ],
                    ])
                    ->add('telephone', TelType::class, [
                        'label' => 'Phone Number',
                        'required' => false,
                        'help' => 'Mobile or office phone number for SMS notifications',
                        'attr' => [
                            'placeholder' => '+237 6XX XXX XXX',
                            'maxlength' => 50,
                        ],
                    ])
                ->end()
                
                ->with('Contact Information Tips', [
                    'class' => 'col-md-4',
                    'box_class' => 'box box-warning'
                ])
                    ->add('_contact_help', TextType::class, [
                        'label' => false,
                        'mapped' => false,
                        'help' => '
                            <div class="alert alert-warning">
                                <h4><i class="icon fa fa-warning"></i> Important!</h4>
                                <ul>
                                    <li><strong>Email</strong> is required for:
                                        <ul>
                                            <li>Meeting invitations</li>
                                            <li>Report notifications</li>
                                        </ul>
                                    </li>
                                    <li><strong>Phone</strong> is required for:
                                        <ul>
                                            <li>SMS reminders</li>
                                            <li>Urgent notifications</li>
                                        </ul>
                                    </li>
                                    <li>At least one contact method is recommended</li>
                                </ul>
                            </div>
                        ',
                        'help_html' => true,
                    ])
                ->end()
            ->end()
            
            ->tab('System Access')
                ->with('User Account', [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-danger'
                ])
                    ->add('userAccount', ModelType::class, [
                        'label' => 'System User Account',
                        'class' => User::class,
                        'property' => 'username',
                        'required' => false,
                        'btn_add' => 'Create User Account',
                        'btn_delete' => true,
                        'help' => 'Link this staff member to a system user account for login access',
                    ])
                ->end()
                
                ->with('Account Management', [
                    'class' => 'col-md-4',
                    'box_class' => 'box box-info'
                ])
                    ->add('_account_help', TextType::class, [
                        'label' => false,
                        'mapped' => false,
                        'help' => '
                            <div class="alert alert-info">
                                <h4><i class="icon fa fa-info-circle"></i> User Accounts</h4>
                                <p>Create a user account to allow this staff member to:</p>
                                <ul>
                                    <li>Access the meeting management system</li>
                                    <li>View their assigned meetings</li>
                                    <li>Track action items</li>
                                    <li>Submit reports</li>
                                </ul>
                                ' . ($isEditMode && !$this->getSubject()->getUserAccount() 
                                    ? '<a href="'.$this->generateUrl('create_user_account', ['id' => $this->getSubject()->getId()]).'" class="btn btn-success btn-block">
                                        <i class="fa fa-plus"></i> Create User Account Now
                                        </a>' 
                                    : '') . '
                            </div>
                        ',
                        'help_html' => true,
                    ])
                ->end()
            ->end()
        ;
        
        if ($isEditMode) {
            $form
                ->tab('Meeting History')
                    ->with('Participation Statistics', [
                        'class' => 'col-md-12',
                        'box_class' => 'box box-primary'
                    ])
                        ->add('_meeting_stats', TextType::class, [
                            'label' => false,
                            'mapped' => false,
                            'template' => '@SonataAdmin/CRUD/personnel/form_meeting_stats.html.twig',
                        ])
                    ->end()
                ->end()
                
                ->tab('Action Items')
                    ->with('Assigned Tasks', [
                        'class' => 'col-md-12',
                        'box_class' => 'box box-warning'
                    ])
                        ->add('_action_items', TextType::class, [
                            'label' => false,
                            'mapped' => false,
                            'template' => '@SonataAdmin/CRUD/personnel/form_action_items.html.twig',
                        ])
                    ->end()
                ->end()
            ;
        }
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Profile')
                ->with('Basic Information', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-primary'
                ])
                    ->add('id', null, [
                        'label' => 'ID',
                    ])
                    ->add('nomComplet', null, [
                        'label' => 'Full Name',
                    ])
                    ->add('matricule', null, [
                        'label' => 'Staff Number',
                    ])
                    ->add('fonction', null, [
                        'label' => 'Function',
                        'associated_property' => 'libelle',
                    ])
                    ->add('structure', null, [
                        'label' => 'Structure',
                        'associated_property' => 'nameFr',
                    ])
                ->end()
                
                ->with('Contact Information', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-success'
                ])
                    ->add('email', null, [
                        'label' => 'Email',
                        'template' => '@SonataAdmin/CRUD/personnel/show_email.html.twig',
                    ])
                    ->add('telephone', null, [
                        'label' => 'Phone',
                        'template' => '@SonataAdmin/CRUD/personnel/show_phone.html.twig',
                    ])
                    ->add('userAccount', null, [
                        'label' => 'User Account',
                        'associated_property' => 'username',
                    ])
                ->end()
            ->end()
            
            ->tab('Meeting History')
                ->with('Meeting Participation', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-info'
                ])
                    ->add('_meetings', 'html', [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/personnel/show_meetings.html.twig',
                    ])
                ->end()
            ->end()
            
            ->tab('Action Items')
                ->with('Assigned Tasks', [
                    'class' => 'col-md-12',
                    'box_class' => 'box box-warning'
                ])
                    ->add('_action_items', 'html', [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/personnel/show_action_items.html.twig',
                    ])
                ->end()
            ->end()
            
            ->tab('Statistics')
                ->with('Meeting Statistics', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-primary'
                ])
                    ->add('_meeting_stats', 'html', [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/personnel/show_meeting_statistics.html.twig',
                    ])
                ->end()
                
                ->with('Performance Metrics', [
                    'class' => 'col-md-6',
                    'box_class' => 'box box-success'
                ])
                    ->add('_performance', 'html', [
                        'label' => false,
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/personnel/show_performance.html.twig',
                    ])
                ->end()
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof Personnel
            ? sprintf('%s (%s)', 
                $object->getNomComplet(), 
                $object->getFonction() ? $object->getFonction()->getLibelle() : 'N/A'
            )
            : 'Personnel';
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Staff Number' => 'matricule',
            'Full Name' => 'nomComplet',
            'Function' => 'fonction.libelle',
            'Function Abbreviation' => 'fonction.abbreviation',
            'Structure' => 'structure.nameFr',
            'Structure Code' => 'structure.acronym',
            'Email' => 'email',
            'Phone' => 'telephone',
            'Has User Account' => 'userAccount.username',
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

    public function preUpdate(object $object): void
    {
        // Auto-convert name to uppercase
        if ($object instanceof Personnel && $object->getNomComplet()) {
            $object->setNomComplet($object->getNomComplet());
        }
    }

    public function prePersist(object $object): void
    {
        // Auto-convert name to uppercase on creation
        if ($object instanceof Personnel && $object->getNomComplet()) {
            $object->setNomComplet($object->getNomComplet());
        }
        
        // Auto-generate matricule if not provided
        if ($object instanceof Personnel && !$object->getMatricule()) {
            $object->setMatricule($this->generateMatricule());
        }
    }

    private function generateMatricule(): string
    {
        // Generate a unique staff number
        // Format: EMP-YYYY-NNNN
        $year = date('Y');
        $random = str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return sprintf('EMP-%s-%s', $year, $random);
    }
}