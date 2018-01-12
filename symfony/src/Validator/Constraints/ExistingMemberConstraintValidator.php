<?php

namespace App\Validator\Constraints;

use App\Entity\Member;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExistingMemberConstraintValidator extends ConstraintValidator
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function validate($username, Constraint $constraint)
    {
        $member = $this
            ->om
            ->getRepository(Member::class)
            ->findOneBy(array(
                'username' => $username,
            ))
        ;
        if (null === $member) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $username)
                ->addViolation();
        }
    }
}
