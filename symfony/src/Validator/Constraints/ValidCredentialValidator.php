<?php

namespace App\Validator\Constraints;

use App\Repository\MemberRepository;
use App\Exception\InvalidPasswordException;
use App\Exception\NonexistentMemberException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidCredentialValidator extends ConstraintValidator
{
    private $memberRepository;

    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    public function validate($up, Constraint $constraint)
    {
        try {
            $member = $this->memberRepository->getMember($up->getUsername());
            $this->memberRepository->checkPassword($member, $up->getPassword());
        } catch (NonexistentMemberException | InvalidPasswordException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $up->getUsername())
                ->addViolation()
            ;
        }
    }
}
