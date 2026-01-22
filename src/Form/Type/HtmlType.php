<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'html' => '',
            'mapped' => false,
            'disabled' => true,
            'label' => false,
            'required' => false,
        ]);

        $resolver->setAllowedTypes('html', 'string');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['html'] = $options['html'];
    }

    public function getBlockPrefix(): string
    {
        return 'html';
    }
}
