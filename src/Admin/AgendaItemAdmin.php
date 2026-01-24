<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\AgendaItem;
use App\Entity\Reunion;
use App\Entity\ReunionParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class AgendaItemAdmin extends AbstractAdmin
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'ordre';
        $sortValues['_sort_order'] = 'ASC';
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
            ->add('titre', null, [
                'label' => 'Title',
            ])
            ->add('presentateur', null, [
                'label' => 'Presenter',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('ordre', null, [
                'label' => '#',
                'editable' => true,
            ])
            ->add('titre', null, [
                'label' => 'Agenda Item',
                'editable' => true,
            ])
            ->add('reunion', null, [
                'label' => 'Meeting',
                'associated_property' => 'objet',
            ])
            ->add('dureeEstimee', null, [
                'label' => 'Duration (min)',
                'editable' => true,
            ])
            ->add('presentateur', null, [
                'label' => 'Presenter',
                'template' => '@SonataAdmin/CRUD/agenda_item/list_presentateur.html.twig',
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
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // Check if we're in embedded mode (within Reunion form)
        $isEmbedded = $this->hasParentFieldDescription();
        $isChild = $this->isChild();
        
        // Get the current subject (agenda item) to determine which meeting's participants to show
        $reunion = null;
        if ($this->hasSubject() && $this->getSubject()->getReunion()) {
            $reunion = $this->getSubject()->getReunion();
        } elseif ($this->hasParentFieldDescription()) {
            // If we're embedded, get reunion from parent
            $parent = $this->getParentFieldDescription()->getAdmin()->getSubject();
            if ($parent instanceof Reunion) {
                $reunion = $parent;
            }
        }
        
        // Get available presenters for this meeting
        $presentateurs = [];
        if ($reunion) {
            $presentateurs = $this->entityManager
                ->getRepository(ReunionParticipation::class)
                ->createQueryBuilder('rp')
                ->where('rp.reunion = :reunion')
                ->setParameter('reunion', $reunion)
                ->getQuery()
                ->getResult();
        }
        
        $form
            ->with('Agenda Item Details', [
                'class' => 'col-md-12',
            ])
                ->add('ordre', IntegerType::class, [
                    'label' => 'Order',
                    'help' => 'Display order in the agenda',
                    'attr' => [
                        'min' => 1,
                    ],
                ])
                ->add('titre', TextType::class, [
                    'label' => 'Title',
                    'help' => 'Brief title of the agenda item',
                    'attr' => [
                        'placeholder' => 'e.g., Budget Review, Project Update',
                    ],
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Detailed description or notes',
                    'attr' => [
                        'rows' => 4,
                        'placeholder' => 'Provide context, objectives, or discussion points...',
                    ],
                ])
                ->add('dureeEstimee', IntegerType::class, [
                    'label' => 'Estimated Duration (minutes)',
                    'required' => false,
                    'help' => 'How long this item should take',
                    'attr' => [
                        'min' => 1,
                        'placeholder' => 'e.g., 15',
                    ],
                ])
            ->end()
        ;

        // Only show reunion field if not embedded
        if (!$isEmbedded && !$isChild) {
            $form
                ->with('Agenda Item Details')
                    ->add('reunion', ModelType::class, [
                        'label' => 'Meeting',
                        'class' => Reunion::class,
                        'property' => 'objet',
                        'btn_add' => false,
                        'btn_delete' => false,
                    ])
                ->end()
            ;
        }

        // Presenter field with filtered choices
        $form
            ->with('Presenter', [
                'class' => 'col-md-12',
            ])
                ->add('presentateur', ModelType::class, [
                    'label' => 'Presenter',
                    'class' => ReunionParticipation::class,
                    'required' => false,
                    'btn_add' => false,
                    'btn_delete' => false,
                    'placeholder' => $reunion 
                        ? 'Select a presenter (must be a meeting participant)' 
                        : 'Please select a meeting first',
                    'help' => $reunion 
                        ? 'The person who will present this agenda item. Only meeting participants can be selected.' 
                        : 'Add participants to the meeting before assigning a presenter.',
                    // Use choices to provide filtered list
                    'choices' => $presentateurs,
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Agenda Item Information', [
                'class' => 'col-md-6',
                'box_class' => 'box box-primary'
            ])
                ->add('id', null, [
                    'label' => 'ID',
                ])
                ->add('ordre', null, [
                    'label' => 'Order',
                ])
                ->add('titre', null, [
                    'label' => 'Title',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'safe' => false,
                ])
                ->add('reunion', null, [
                    'label' => 'Meeting',
                    'associated_property' => 'objet',
                ])
            ->end()
            
            ->with('Timing & Presenter', [
                'class' => 'col-md-6',
                'box_class' => 'box box-info'
            ])
                ->add('dureeEstimee', null, [
                    'label' => 'Estimated Duration',
                    'template' => '@SonataAdmin/CRUD/agenda_item/show_duration.html.twig',
                ])
                ->add('presentateur', null, [
                    'label' => 'Presenter',
                    'template' => '@SonataAdmin/CRUD/agenda_item/show_presentateur.html.twig',
                ])
                ->add('date_created', null, [
                    'label' => 'Created On',
                    'format' => 'd/m/Y H:i',
                ])
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof AgendaItem
            ? sprintf('#%d - %s', $object->getOrdre(), $object->getTitre())
            : 'Agenda Item';
    }

    public function prePersist(object $object): void
    {
        // Auto-set order if not provided
        if (!$object->getOrdre() && $object->getReunion()) {
            $maxOrder = 0;
            foreach ($object->getReunion()->getAgendaItems() as $item) {
                if ($item->getOrdre() > $maxOrder) {
                    $maxOrder = $item->getOrdre();
                }
            }
            $object->setOrdre($maxOrder + 1);
        }
    }

    public function preUpdate(object $object): void
    {
        $object->setDateUpdated(new \DateTime());
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Meeting' => 'reunion.objet',
            'Order' => 'ordre',
            'Title' => 'titre',
            'Description' => 'description',
            'Duration (min)' => 'dureeEstimee',
            'Presenter' => function($object) {
                return $object->getPresentateur() 
                    ? $object->getPresentateur()->getParticipantName() 
                    : '';
            },
        ];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        if($this->hasParentFieldDescription() or $this->isChild()) {
            // Disable custom routes in embedded mode
            return;
        }
        
        $collection->clearExcept(['list', 'show', 'export']);
    }
}