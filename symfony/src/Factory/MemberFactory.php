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

    public function create(?int $id, string $username, string $password)
    {
        $member = new Member($id, $username);
        $hashed = $this->hasher->encodePassword($member, $password);
        $member->setPassword($hashed);

        return $member;
    }

    public function setId(Member $member, int $id): Member
    {
        $modifiedMember = new Member($id, $member->getUsername());
        $modifiedMember->setPassword($member->getPassword());

        return $modifiedMember;
    }

    public function setPassword(Member &$member, string $password): void
    {
        $hashed = $this->hasher->encodePassword($member, $password);
        $member->setPassword($hashed);
    }
}
