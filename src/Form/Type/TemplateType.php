<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => null,
            'mapped' => false,
            'label' => false,
            'required' => false,
        ]);

        $resolver->setAllowedTypes('template', ['string', 'null']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['template'] = $options['template'];
    }

    public function getBlockPrefix(): string
    {
        return 'template';
    }
}
