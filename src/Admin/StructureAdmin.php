<?php

namespace App\Admin;

use App\Entity\Structure;
use App\Entity\SubDivision;
use App\Enum\Cycle;
use App\Enum\StructureCategory;
use App\Enum\StructureEducation;
use App\Enum\StructureOrdre;
use App\Enum\StructureRank;
use App\Enum\StructureType;
use App\Enum\Subsystem;
use App\Form\Type\MapCoordsType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class StructureAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Informations Générales')
                ->with('Identification', ['class' => 'col-md-8'])
                    ->add('nameFr', TextType::class, ['label' => 'Nom (FR)'])
                    ->add('nameEn', TextType::class, ['label' => 'Name (EN)'])
                    ->add('acronym', TextType::class, ['label' => 'Acronyme', 'required' => false])
                    ->add('parent', EntityType::class, [
                        'class' => Structure::class,
                        'choice_label' => 'nameFr',
                        'label' => 'Structure Parente (Hiérarchie)',
                        'required' => false,
                        'placeholder' => 'Aucun parent (Structure racine)',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('s')
                                ->orderBy('s.nameFr', 'ASC');
                        }
                    ])
                ->end()
                ->with('Classification', ['class' => 'col-md-4'])
                    ->add('category', EnumType::class, [
                        'label' => 'Catégorie',
                        'class' => StructureCategory::class,
                        'choice_label' => function (StructureCategory $category) {
                            return $category->label();
                        },
                        'expanded' => false,
                        'required' => true,
                    ])
                    ->add('type', EnumType::class, [
                        'label' => 'Type',
                        'class' => StructureType::class,
                        'choice_label' => function (StructureType $type) {
                            return $type->label();
                        },
                        'expanded' => false,
                        'required' => true,
                    ])
                    ->add('levelRank', EnumType::class, [
                        'label' => 'Rang',
                        'class' => StructureRank::class,
                        'choice_label' => function (StructureRank $rank) {
                            return $rank->label();
                        },
                        'expanded' => false,
                        'required' => false,
                    ])
                    ->add('codeHierarchique', TextType::class, [
                        'label' => 'Code Hiérarchique',
                        'required' => false,
                    ])
                ->end()
            ->end()

            ->tab('Localisation & Carte')
                ->with('Adresse', ['class' => 'col-md-4'])
                    ->add('subdivision', EntityType::class, [
                        'class' => SubDivision::class,
                        'choice_label' => 'name',
                        'label' => 'Arrondissement/Subdivision',
                    ])
                    ->add('adress', TextareaType::class, ['label' => 'Adresse / Quartier', 'required' => false])
                ->end()
                ->with('Géolocalisation (Cliquer sur la carte)', ['class' => 'col-md-8'])
                    ->add('location_map', MapCoordsType::class, [
                        'mapped' => false, // Not in DB
                        'label' => false   // Label is inside the widget template
                    ])
                    // Hidden fields that store the actual data, updated by JS
                    ->add('latitude', HiddenType::class, ['attr' => ['class' => 'js-latitude']])
                    ->add('longitude', HiddenType::class, ['attr' => ['class' => 'js-longitude']])
                    ->add('altitude', NumberType::class, ['label' => 'Altitude (m)', 'required' => false])
                ->end()
            ->end()

            ->tab('Détails Éducation')
                ->with('Spécificités', ['class' => 'col-md-6'])
                    ->add('education', EnumType::class, [
                        'label' => 'Type d\'enseignement',
                        'class' => StructureEducation::class,
                        'choice_label' => function (StructureEducation $education) {
                            return $education->label();
                        },
                        'expanded' => false,
                        'required' => false,
                    ])
                    ->add('ordre', EnumType::class, [
                        'label' => 'Ordre',
                        'class' => StructureOrdre::class,
                        'choice_label' => function (StructureOrdre $ordre) {
                            return $ordre->label();
                        },
                        'expanded' => false,
                        'required' => true,
                    ])
                    ->add('subsystem', EnumType::class, [
                        'label' => 'Sous-système',
                        'class' => Subsystem::class,
                        'choice_label' => function (Subsystem $subsystem) {
                            return $subsystem->label();
                        },
                        'expanded' => false,
                        'required' => false,
                    ])
                    ->add('cycle', EnumType::class, [
                        'label' => 'Cycle',
                        'class' => Cycle::class,
                        'choice_label' => function (Cycle $cycle) {
                            return $cycle->label();
                        },
                        'expanded' => false,
                        'required' => false,
                    ])
                ->end()
                ->with('Options', ['class' => 'col-md-6'])
                    ->add('isBilingual', CheckboxType::class, ['label' => 'Bilingue ?', 'required' => false])
                    ->add('hasIndustrial', CheckboxType::class, ['label' => 'Filière Industrielle', 'required' => false])
                    ->add('hasCommercial', CheckboxType::class, ['label' => 'Filière Commerciale', 'required' => false])
                    ->add('hasAgricultural', CheckboxType::class, ['label' => 'Filière Agricole', 'required' => false])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nameFr', null, ['label' => 'Nom'])
            ->add('acronym', null, ['label' => 'Acronyme'])
            ->add('subdivision', null, [
                'label' => 'Arrondissement',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => SubDivision::class,
                    'choice_label' => 'name',
                ],
            ])
            ->add('category', null, ['field_type' => EnumType::class, 'field_options' => ['class' => StructureCategory::class]])
            ->add('type', null, ['field_type' => EnumType::class, 'field_options' => ['class' => StructureType::class]])
            ->add('isBilingual')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('nameFr', null, ['label' => 'Nom (FR)'])
            ->add('acronym', null, ['label' => 'Acronyme'])
            ->add('type', FieldDescriptionInterface::TYPE_ENUM, ['label' => 'Type', 'enum_code' => StructureType::class]) 
            ->add('subdivision.name', null, ['label' => 'Localisation'])
            ->add('parent.nameFr', null, ['label' => 'Parent'])
            ->add('isBilingual', null, ['editable' => true, 'label' => 'Bilingue'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Informations')
                ->with('Général')
                    ->add('nameFr', null, ['label' => 'Nom (FR)'])
                    ->add('nameEn', null, ['label' => 'Nom (EN)'])
                    ->add('acronym', null, ['label' => 'Acronyme'])
                    ->add('category', FieldDescriptionInterface::TYPE_ENUM, ['label' => 'Catégorie', 'enum_code' => StructureCategory::class])
                    ->add('type', FieldDescriptionInterface::TYPE_ENUM, ['label' => 'Type', 'enum_code' => StructureType::class])
                    ->add('codeHierarchique', null, ['label' => 'Code Hiérarchique'])
                ->end()
            ->end()
            ->tab('Localisation')
                ->with('Map')
                    ->add('locationMap', null, [
                        'mapped' => false, 
                        'template' => '@SonataAdmin/CRUD/structure/show_map.html.twig'
                    ])
                    ->add('subdivision')
                    ->add('adress')
                ->end()
            ->end()
        ;
    }
}