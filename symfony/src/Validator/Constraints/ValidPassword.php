<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidPassword extends Constraint
{
    public $message = 'Either your username or your password is incorrect.';
}
