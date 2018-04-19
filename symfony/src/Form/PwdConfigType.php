<?php

declare(strict_types=1);

namespace App\Form;

use App\FormModel\PwdConfigSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PwdConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("allowPwdAuthentication", CheckboxType::class, [
                "required" => false,
                "label" => "Allow password login?",
            ])
            ->add('minimumLength', IntegerType::class, [
                'empty_data' => null,
                'required' => false,
            ])
            ->add('enforceMinimumLength', CheckboxType::class, ['required' => false])
            ->add('requireNumbers', CheckboxType::class, ['required' => false])
            ->add('requireSpecialCharacters', CheckboxType::class, ['required' => false])
            ->add('requireUppercaseLetters', CheckboxType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PwdConfigSubmission::class,
        ));
    }
}
