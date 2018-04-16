<?php

namespace App\Factory;

use App\Entity\Member;
use LM\Authentifier\Model\IMember;
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

    public function createFrom(IMember $member): Member
    {
        $newMember = new Member(
            null,
            $member->getUsername(),
            [
                'ROLE_USER',
            ])
        ;
        $newMember->setPassword($member->getHashedPassword());

        return $newMember;
    }

    public function setPassword(Member &$member, string $password): void
    {
        $hashed = $this->hasher->encodePassword($member, $password);
        $member->setPassword($hashed);
    }
}
