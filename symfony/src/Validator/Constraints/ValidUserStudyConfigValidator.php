<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidUserStudyConfigValidator extends ConstraintValidator
{
    public function validate($submission, Constraint $constraint)
    {
        if (true === $submission->isUserStudyModeActive &&
        empty($submission->participantId)) {
            $this->context->buildViolation($constraint->message)
                // ->setParameter('{{ string }}', $submission->getUsername())
                ->addViolation()
            ;
        }
    }
}
