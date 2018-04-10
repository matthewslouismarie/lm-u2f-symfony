<?php

namespace App\Validator\Constraints;

use App\Enum\Setting;
use App\Exception\InvalidPasswordException;
use App\Service\AppConfigManager;
use App\Service\PasswordValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidNewPasswordValidator extends ConstraintValidator
{
    private $config;

    private $pwdValidator;

    public function __construct(
        AppConfigManager $config,
        PasswordValidator $pwdValidator)
    {
        $this->config = $config;
        $this->pwdValidator = $pwdValidator;
    }

    /**
     * @todo Exception.
     */
    public function validate($password, Constraint $constraint)
    {
        if (true === $this->config->getBoolSetting(Setting::PWD_ENFORCE_MIN_LENGTH)) {
            $pwdMinLength = $this->config->getIntSetting(Setting::PWD_MIN_LENGTH);
            if (mb_strlen($password, 'utf-8') < $pwdMinLength) {
                $this->addError("Your password needs to be at least {$pwdMinLength} characters long", $password);
            }
        }
        if (true === $this->config->getBoolSetting(Setting::PWD_NUMBERS)) {
            switch (preg_match('/[0-9]/', $password)) {
                case 0:
                    $this->addError('Your password needs to contain numbers.', $password);
                    break;

                case false:
                    throw new Exception();
                    break;
            }
        }
        if (true === $this->config->getBoolSetting(Setting::PWD_SPECIAL_CHARS)) {
            if (false === $this->pwdValidator->hasSpecialChars($password)) {
                    $this->addError('Your password needs to contain special characters', $password);
            }
        }
        if (true === $this->config->getBoolSetting(Setting::PWD_UPPERCASE)) {
            switch (preg_match('/[A-Z]/', $password)) {
                case 0:
                    $this->addError('Your password needs to contain uppercase letters.', $password);
                    break;

                case false:
                    throw new Exception();
                    break;
            }
        }
    }

    private function addError(string $message, string $password): void
    {
        $this
            ->context
            ->buildViolation($message)
            ->setParameter('{{ string }}', $password)
            ->addViolation()
        ;
    }
}
