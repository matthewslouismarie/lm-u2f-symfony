<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use TypeError;
use App\Service\SecurityStrategyUnserializer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class JsonSecurityStrategyValidator extends ConstraintValidator
{
    private $unserializer;

    public function __construct(SecurityStrategyUnserializer $unserializer)
    {
        $this->unserializer = $unserializer;
    }

    /**
     * @todo Exception.
     */
    public function validate($inputStr, Constraint $constraint)
    {
        $inputArray = json_decode($inputStr);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->addError(
                'The submitted value is not a valid JSON string.',
                $inputStr
            );
            return;
        }

        try {
            $this->unserializer->unserialize($inputArray);
        } catch (TypeError $e) {
            $this->addError(
                'The submitted value is not a valid security strategy specification.',
                $inputStr
            );
        } catch (UnexpectedValueException $e) {
            $this->addError(
                'One of the challenge type is not recognised.',
                $inputStr
            );
        }
    }

    private function addError(string $message, string $input): void
    {
        $this
            ->context
            ->buildViolation($message)
            ->setParameter('{{ string }}', $input)
            ->addViolation()
        ;
    }
}
