<?php

namespace App\Admin;

use App\Entity\Document;
use App\Entity\Evenement;
use App\Entity\Structure;
use App\Entity\Visite;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class VisiteAdmin extends AbstractAdmin
{
    public function toString(object $object): string
    {
        return $object instanceof Visite
            ? $object->__toString()
            : 'Visite'; // shown in the breadcrumb on the create view
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isChild = $this->isChild();

        $form
            ->tab('Informations de la Visite')
                ->with('Contexte', ['class' => 'col-md-6'])
                    ->ifTrue(!$isChild)
                        ->add('evenement', EntityType::class, [
                            'class' => Evenement::class,
                            'choice_label' => 'libelle',
                            'label' => 'Événement lié',
                            'placeholder' => 'Sélectionner un événement'
                        ])
                    ->ifEnd()
                    ->add('structure', EntityType::class, [
                        'class' => Structure::class,
                        'choice_label' => 'nameFr', // Using the getter we defined earlier
                        'label' => 'Structure visitée',
                        'placeholder' => 'Sélectionner une structure'
                    ])
                ->end()
                ->with('Calendrier', ['class' => 'col-md-6'])
                    ->add('dateArrivee', DateTimePickerType::class, [
                        'label' => 'Arrivée',
                        'format' => 'dd/MM/yyyy HH:mm'
                    ])
                    ->add('dateDepart', DateTimePickerType::class, [
                        'label' => 'Départ',
                        'required' => false,
                        'format' => 'dd/MM/yyyy HH:mm'
                    ])
                ->end()
                ->with('Compte Rendu', ['class' => 'col-md-12'])
                    ->add('details', CKEditorType::class, [
                        'label' => 'Notes rapides / Résumé', 
                        'required' => false,
                    ])
                ->end()
            ->end()

            ->tab('Documents & Rapports')
                ->with('Rapport Officiel', ['class' => 'col-md-4'])
                    // OneToOne: ModelListType is perfect for uploading a single related entity inline
                    ->add('rapport', ModelListType::class, [
                        'btn_add' => 'Uploader le rapport',
                        'btn_list' => false,
                        'btn_delete' => 'Supprimer',
                        'label' => 'Fichier du Rapport (PDF)'
                    ])
                ->end()
                ->with('Preuves & Annexes (Photos, Scans)', ['class' => 'col-md-8'])
                    // OneToMany: CollectionType to add multiple documents
                    ->add('photos', CollectionType::class, [
                        'by_reference' => false,
                        'label' => false
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('evenement', null, ['label' => 'Événement'])
            ->add('structure', null, ['label' => 'Structure'])
            ->add('dateArrivee', null, ['label' => 'Date'])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('dateArrivee', null, ['format' => 'd M Y', 'label' => 'Date'])
            ->add('structure.nameFr', null, ['label' => 'Structure'])
            ->add('evenement.libelle', null, ['label' => 'Cadre (Événement)'])
            
            // CUSTOM: Smart Report Status Column
            ->add('rapport_status', null, [
                'label' => 'Rapport',
                'virtual_field' => true,
                'template' => '@SonataAdmin/CRUD/visite/list_report_status.html.twig'
            ])
            
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
            ->tab('Détails de la visite')
                ->with('Infos')
                    ->add('evenement')
                    ->add('structure')
                    ->add('dateArrivee')
                    ->add('dateDepart')
                    ->add('details', null, ['safe' => true])
                ->end()
            ->end()

            // CUSTOM: The Creative Part - Embedded Viewer
            ->tab('Salle de lecture (Documents)')
                ->with('Rapport & Galerie', ['class' => 'col-md-12'])
                    ->add('photos', null, [
                        'mapped' => false,
                        'label' => false,
                        'template' => '@SonataAdmin/CRUD/visite/show_documents_viewer.html.twig'
                    ])
                ->end()
            ->end()
        ;
    }

    protected function prePersist($visite): void
    {
        // Ensure documents are linked to this visite
        foreach ($visite->getPhotos() as $document) {
            $document->setVisite($visite);
        }
    }

    protected function preUpdate($visite): void
    {
        // Ensure documents are linked to this visite
        foreach ($visite->getPhotos() as $document) {
            $document->setVisite($visite);
        }
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_sort_order'] = 'DESC';
        $sortValues['_sort_by'] = 'dateArrivee';
    }
}