<?php

namespace App\Validator\Constraints;

use App\Entity\Member;
use App\Exception\InvalidPasswordException;
use App\Exception\NonexistentMemberException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ValidCredentialValidator extends ConstraintValidator
{
    private $om;
    private $upEncoder;

    public function __construct(
        ObjectManager $om,
        UserPasswordEncoderInterface $upEncoder)
    {
        $this->om = $om;
        $this->upEncoder = $upEncoder;
    }

    public function validate($up, Constraint $constraint)
    {
        try {
            $member = $this->getMember($up->getUsername());
            $this->checkPassword($member, $up->getPassword());
        } catch (NonexistentMemberException | InvalidPasswordException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $up->getUsername())
                ->addViolation()
            ;
        }
    }

    /**
     * @todo Move in another class.
     */
    private function getMember(string $username): Member
    {
        $member = $this
            ->om
            ->getRepository(Member::class)->findOneBy([
                'username' => $username,
            ])
        ;
        if (null === $member) {
            throw new NonexistentMemberException();
        }
        return $member;
    }

    /**
     * @todo Move in another class.
     */
    private function checkPassword(Member $member, string $password): void
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
