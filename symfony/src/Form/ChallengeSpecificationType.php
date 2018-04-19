<?php

declare(strict_types=1);

namespace App\Form;

use App\Enum\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Form\CallbackTransformer;

/**
 * @todo Allow user to import a file directly.
 */
class ChallengeSpecificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(Setting::SEC_HIGH_PWD, CheckboxType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('bool'),
                ],
                'required' => false,
            ])
            ->add(Setting::SEC_HIGH_U2F, CheckboxType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('bool'),
                ],
                'required' => false,
            ])
            ->add(Setting::SEC_HIGH_BOTH, CheckboxType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('bool'),
                ],
                'required' => false,
            ])
            ->add(Setting::SEC_HIGH_U2F_N, IntegerType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('integer'),
                    new GreaterThanOrEqual(0),
                ],
                'scale' => 0,
            ])
            ->add(Setting::SEC_MEDM_PWD, CheckboxType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('bool'),
                ],
                'required' => false,
            ])
            ->add(Setting::SEC_MEDM_U2F, CheckboxType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('bool'),
                ],
                'required' => false,
            ])
            ->add(Setting::SEC_MEDM_BOTH, CheckboxType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('bool'),
                ],
                'required' => false,
            ])
            ->add(Setting::SEC_MEDM_U2F_N, IntegerType::class, [
                'constraints' => [
                    new NotNull(),
                    new Type('integer'),
                    new GreaterThanOrEqual(0),
                ],
                'scale' => 0,
            ])
            ->add('submit', SubmitType::class)
            // ->get('sec_high_pwd')
            // ->addModelTransformer(new CallbackTransformer(
            //     function ($property) {
            //         return (bool) $property;
            //     },
            //     function ($property) {
            //         return (bool) $property;
            //     }))
        ;
    }
}
