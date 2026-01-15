<?php

namespace App\Admin;

use App\Entity\Evenement;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class EvenementAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Général')
                ->with('Informations', ['class' => 'col-md-8'])
                    ->add('libelle', TextType::class, ['label' => 'Titre'])
                    ->add('theme', TextType::class, ['required' => false])
                ->end()
                ->with('Calendrier', ['class' => 'col-md-4'])
                    ->add('dateDebut', DateTimePickerType::class, [
                        'required' => true,
                        'format' => 'dd/MM/yyyy HH:mm'
                    ])
                    ->add('dateFin', DateTimePickerType::class, [
                        'required' => false,
                        'format' => 'dd/MM/yyyy HH:mm'
                    ])
                ->end()
            ->end()
            ->tab('Détails')
                ->with('Contenu')
                    ->add('objectifs', CKEditorType::class, ['required' => false])
                ->end()
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('libelle')
            ->add('dateDebut', null, ['format' => 'd M Y', 'label' => 'Début'])
            ->add('visites_count', null, [
                'label' => 'Structures visitées',
                'template' => '@SonataAdmin/CRUD/evenement/list_visites_count.html.twig',
                'virtual_field' => true
            ])
            ->add('status', null, [
                'label' => 'État',
                'template' => '@SonataAdmin/CRUD/evenement/list_status.html.twig',
                'virtual_field' => true
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Vue d\'ensemble')
                ->with('Infos')
                    ->add('libelle')
                    ->add('theme')
                    ->add('dateDebut')
                    ->add('dateFin')
                    ->add('objectifs', null, ['safe' => true])
                ->end()
            ->end()
            
            // 2. The Creative Part: Map Tab
            ->tab('Cartographie & Itinéraire')
                ->with('Carte des visites', ['class' => 'col-md-12'])
                    ->add('map_route', null, [
                        'mapped' => false,
                        'template' => '@SonataAdmin/CRUD/evenement/show_map_route.html.twig'
                    ])
                ->end()
            ->end()
            
            ->tab('Liste des Visites')
                ->with('Détails des structures')
                    ->add('visites', null, [
                        'associated_property' => 'structure.nameFr',
                        'label' => 'Liste simple'
                    ])
                ->end()
            ->end();
    }
}