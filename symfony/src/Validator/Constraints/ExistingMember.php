<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExistingMember extends Constraint
{
    public $message = 'Your username or your password is not valid';
}
