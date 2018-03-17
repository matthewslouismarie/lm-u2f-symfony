<?php

namespace App\Form;

use App\FormModel\U2fConfigSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class U2fConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('allowU2fLogin', CheckboxType::class, ['required' => false])
            ->add('nU2fKeysPostAuth', IntegerType::class)
            ->add('nU2fKeysReg', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => U2fConfigSubmission::class,
        ));
    }
}
