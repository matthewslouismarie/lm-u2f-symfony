<?php

declare(strict_types=1);

namespace App\Form;

use App\FormModel\NewU2fRegistrationSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewU2fRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('u2fTokenResponse', HiddenType::class)
            ->add('u2fKeyName')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NewU2fRegistrationSubmission::class,
        ));
    }
}
