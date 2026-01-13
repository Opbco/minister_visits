<?php

namespace App\Admin;

use App\Entity\Country;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CountryAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id')
            ->add('nom')
            ->add('date_created', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'created at',
                'sortable' => true,
            ])
            ->add('date_updated', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'updated at',
                'sortable' => true,
            ])
            ->add('user_created', FieldDescriptionInterface::TYPE_MANY_TO_ONE, [
                'label' => 'created by',
            ])
            ->add('user_updated', FieldDescriptionInterface::TYPE_MANY_TO_ONE, [
                'label' => 'updated by',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'Actions',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ]
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('nom', null, [
                'label' => 'Nom',
                'required' => true,
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('nom', null, [
                'label' => 'Nom',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('nom')
            ->add('date_created', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'created at',
            ])
            ->add('date_updated', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'updated at',
            ])
            ->add('user_created', FieldDescriptionInterface::TYPE_MANY_TO_ONE, [
                'label' => 'created by',
            ])
            ->add('user_updated', FieldDescriptionInterface::TYPE_MANY_TO_ONE, [
                'label' => 'updated by',
            ])
        ;
    }
}
