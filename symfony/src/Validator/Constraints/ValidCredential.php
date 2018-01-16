<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidCredential extends Constraint
{
    public $message = 'Either your username or your password is incorrect.';

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
