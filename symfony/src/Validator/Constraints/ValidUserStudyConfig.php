<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidUserStudyConfig extends Constraint
{
    public $message = "You must provide a participant id in user study mode.";

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
