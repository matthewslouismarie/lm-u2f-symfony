<?php

namespace App\Security\Authentication\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

class UPFormAuthProvider implements AuthenticationProviderInterface
{
    private $provider;
    private $encoder;

    public function __construct(EntityUserProvider $provider, UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->provider = $provider;
    }

    /**
     * @todo Find an elegant and robust way to detect if the request comes from
     * the login page.
     */
    public function authenticate(TokenInterface $token)
    {
        try {
            $user = $this->provider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            // CAUTION: this message will be returned to the client
            // (so don't put any un-trusted messages / error strings here)
            throw new CustomUserMessageAuthenticationException('Invalid username.');
        }
        
        if (null === $token->getCredentials()) {
            return $token;
        }
        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());

        if ($passwordValid) {
            return $token;
        }

        // CAUTION: this message will be returned to the client
        // (so don't put any un-trusted messages / error strings here)
        throw new CustomUserMessageAuthenticationException('Auth problem.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken;
            // && $token->getProviderKey() === '';
    }
}
