<?php

namespace App\Factory;

use App\Entity\Member;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MemberFactory
{
    private $hasher;

    public function __construct(UserPasswordEncoderInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function create(string $username, string $password)
    {
        $member = new Member($username);
        $hashed = $this->hasher->encodePassword($member, $password);
        $member->setPassword($hashed);
        return $member;
    }
}