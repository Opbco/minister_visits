<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Document;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;

final class DocumentAdmin extends AbstractAdmin
{

    public function toString(object $object): string
    {
        return $object instanceof Document
            ? $object->getFileWebPath()
            : 'Document'; // shown in the breadcrumb on the create view
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('mimeType', null, ['label' => 'Mime Type'])
            ->add('fileName', null, ['label' => 'Name of the document']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('fileName', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'Name of the document',
                'template' => '@SonataAdmin/CRUD/list_file.html.twig',
            ])
            ->add('mimeType', null, ['label' => 'Mime Type'])
            ->add('context', null, ['label' => 'Contexte'])
            ->add('updated', null, ['label' => 'Last Modified'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {

        $doc = $this->getSubject();

        // use $fileFormOptions so we can add other options to the field
        $fileFormOptions = ['required' => false];

        if ($doc && ($webPath = $doc->getFileWebPath())) {
            $fullPath = $doc->getFileAbsolutePath();
            // add a 'help' option containing the preview's img tag
            $fileFormOptions['help'] = is_file($fullPath . '') ? '<a target="_blank" style="display:block;" href="' . $webPath . '">' . $doc->getFileName() . '</a>' : 'document unavailable';
            $fileFormOptions['help_html'] = true;
        }
        $form
            ->add('file', FileType::class, $fileFormOptions);
    }

    public function prePersist(object $doc): void
    {
        $this->manageFileUpload($doc);
    }

    public function preUpdate(object $doc): void
    {
        $this->manageFileUpload($doc);
    }

    private function manageFileUpload(object $doc): void
    {
        if ($doc->getFile()) {
            $doc->refreshUpdated();
        }
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('fileName', 'file', ['label' => 'Nom document', 'template' => '@SonataAdmin/CRUD/base_show_file.html.twig'])
            ->add('mimeType', null, ['label' => 'Mime Type'])
            ->add('context', null, ['label' => 'Contexte'])
            ->add('updated', null, ['label' => 'Last Modified']);
    }
}
