<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MapCoordsType extends AbstractType
{
    public function getParent(): string
    {
        return FormType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false, // This field doesn't map directly to a database column
            'label' => false,  // We handle the label in the template or admin
            'inherit_data' => true, // Allows access to the parent form data (the Structure entity)
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'map_coords';
    }
}