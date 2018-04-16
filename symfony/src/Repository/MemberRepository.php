<?php

namespace App\Repository;

use App\Entity\Member;
use App\Exception\InvalidPasswordException;
use App\Exception\NonexistentMemberException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MemberRepository extends ServiceEntityRepository
{
    private $upEncoder;

    public function __construct(
        RegistryInterface $registry,
        UserPasswordEncoderInterface $upEncoder
    ) {
        parent::__construct($registry, Member::class);
        $this->upEncoder = $upEncoder;
    }

    /**
     * @todo Username nullable?
     */
    public function getMember(?string $username): Member
    {
        $member = $this->findOneBy([
                'username' => $username,
            ])
        ;
        if (null === $member) {
            throw new NonexistentMemberException();
        }

        return $member;
    }

    public function checkPassword(Member $member, ?string $password): void
    {
        $isPasswordValid = $this
            ->upEncoder
            ->isPasswordValid($member, $password)
        ;
        if (false === $isPasswordValid) {
            throw new InvalidPasswordException();
        }
    }
}
