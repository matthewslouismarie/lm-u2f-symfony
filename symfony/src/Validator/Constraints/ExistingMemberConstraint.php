<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @todo Remove Constraint from name.
 * 
 * @Annotation
 */
class ExistingMemberConstraint extends Constraint
{
    public $message = 'Your username or your password is not valid';
}
