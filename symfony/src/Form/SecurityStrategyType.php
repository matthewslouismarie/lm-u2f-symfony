<?php

namespace App\Form;

use App\Enum\SecurityStrategy;
use App\FormModel\SecurityStrategySubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityStrategyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('securityStrategyId', ChoiceType::class, [
                'choices' => [
                    'Password' => SecurityStrategy::PWD,
                    'U2F' => SecurityStrategy::U2F,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SecurityStrategySubmission::class,
        ));
    }
}
