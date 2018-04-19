<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AvailableUsername extends Constraint
{
    public $message = 'The username is already taken.';
}
