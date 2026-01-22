<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Type\HtmlType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class FonctionAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_by'] = 'libelle';
        $sortValues['_sort_order'] = 'ASC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('batch');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('libelle', null, [
                'label' => 'Function Name',
                'show_filter' => true,
            ])
            ->add('abbreviation', null, [
                'label' => 'Abbreviation',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('libelle', null, [
                'label' => 'Function Name',
                'editable' => true,
            ])
            ->add('abbreviation', null, [
                'label' => 'Abbreviation',
                'editable' => true,
            ])
            ->add('description', null, [
                'label' => 'Description',
                'template' => '@SonataAdmin/CRUD/fields/list_string_truncated.html.twig',
            ])
            ->add('personnels', 'html', [
                'label' => 'Staff Count',
                'mapped' => false,
                'template' => '@SonataAdmin/CRUD/fonction/personnel_count.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Function Information', [
                'class' => 'col-md-8',
                'box_class' => 'box box-primary'
            ])
                ->add('libelle', TextType::class, [
                    'label' => 'Function Name',
                    'help' => 'Full name of the function (e.g., "Directeur des Ressources Humaines")',
                    'attr' => [
                        'placeholder' => 'Enter function name',
                    ],
                ])
                ->add('abbreviation', TextType::class, [
                    'label' => 'Abbreviation',
                    'required' => false,
                    'help' => 'Short form of the function (e.g., "DRH")',
                    'attr' => [
                        'placeholder' => 'Enter abbreviation (optional)',
                        'maxlength' => 50,
                    ],
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Detailed description of responsibilities and duties',
                    'attr' => [
                        'rows' => 6,
                        'placeholder' => 'Describe the function responsibilities...',
                    ],
                ])
            ->end()
            
            ->with('Quick Tips', [
                'class' => 'col-md-4',
                'box_class' => 'box box-info'
            ])
                ->add('_help', HtmlType::class, [
                    'html' => '
                        <div class="alert alert-info">
                            <h4><i class="icon fa fa-info-circle"></i> Function Management Tips</h4>
                            <ul>
                                <li>Use clear, standardized function names</li>
                                <li>Abbreviations help with reports and listings</li>
                                <li>Functions are used for meeting roles (President, Secretary)</li>
                                <li>Cannot delete functions assigned to personnel</li>
                            </ul>
                        </div>
                    ',
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Function Details', [
                'class' => 'col-md-6',
                'box_class' => 'box box-primary'
            ])
                ->add('id', null, [
                    'label' => 'ID',
                ])
                ->add('libelle', null, [
                    'label' => 'Function Name',
                ])
                ->add('abbreviation', null, [
                    'label' => 'Abbreviation',
                ])
            ->end()
            
            ->with('Additional Information', [
                'class' => 'col-md-6',
                'box_class' => 'box box-info'
            ])
                ->add('description', null, [
                    'label' => 'Description',
                    'safe' => false,
                ])
            ->end()
            
            ->with('Personnel with this Function', [
                'class' => 'col-md-12',
                'box_class' => 'box box-success'
            ])
                ->add('personnels', 'html', [
                    'label' => false,
                    'mapped' => false,
                    'template' => '@SonataAdmin/CRUD/fonction/show_personnel_list.html.twig',
                ])
            ->end()
        ;
    }

    public function toString(object $object): string
    {
        return $object instanceof \App\Entity\Fonction
            ? ($object->getAbbreviation() 
                ? sprintf('%s (%s)', $object->getLibelle(), $object->getAbbreviation())
                : $object->getLibelle())
            : 'Function';
    }

    public function preRemove(object $object): void
    {
        // Check if any personnel has this function
        // This should be handled by database constraints or service layer
        // Here we just demonstrate the hook
        if (!$object instanceof \App\Entity\Fonction) {
            return;
        }
        if (count($object->getPersonnels()) > 0) {
            throw new \RuntimeException('Cannot delete function assigned to personnel.');
        }
    }

    protected function configureExportFields(): array
    {
        return [
            'ID' => 'id',
            'Function Name' => 'libelle',
            'Abbreviation' => 'abbreviation',
            'Description' => 'description',
        ];
    }

    public function configureBatchActions(array $actions): array
    {
        // Remove batch delete to prevent accidental deletion
        unset($actions['delete']);
        
        return $actions;
    }
}