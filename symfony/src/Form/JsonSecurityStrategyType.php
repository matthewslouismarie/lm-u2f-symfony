<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\Constraints\JsonSecurityStrategy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class JsonSecurityStrategyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('json', TextAreaType::class, [
                'constraints' => [
                    new JsonSecurityStrategy(),
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
