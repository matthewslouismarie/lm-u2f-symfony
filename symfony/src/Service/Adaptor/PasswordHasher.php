<?php

namespace App\Service\Adaptor;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @todo Unused.
 */
class PasswordHasher
{
    private $adaptee;

    public function __construct(UserPasswordEncoderInterface $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    public function isPasswordValid(UserInterface $user, string $password)
    {
        return $this->adaptee->isPasswordValid($user, $password);
    }
}
