<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidPassword extends Constraint
{
    public $message = "The password is incorrect.";
}
