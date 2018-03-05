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

    public function create(
        ?int $id,
        string $username,
        string $password,
        array $roles)
    {
        $member = new Member($id, $username, $roles);
        $hashed = $this->hasher->encodePassword($member, $password);
        $member->setPassword($hashed);

        return $member;
    }

    public function setPassword(Member &$member, string $password): void
    {
        $hashed = $this->hasher->encodePassword($member, $password);
        $member->setPassword($hashed);
    }
}
