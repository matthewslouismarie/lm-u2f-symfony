<?php

namespace App\Validator\Constraints;

use Symfony\Component\HttpFoundation\RequestStack;
use App\DataStructure\TransitingDataManager;
use App\Entity\Member;
use App\Exception\InvalidPasswordException;
use App\Exception\NonexistentMemberException;
use App\Repository\MemberRepository;
use App\Service\AuthenticationManager;
use App\Service\SecureSession;
use App\Service\UriHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidPasswordValidator extends ConstraintValidator
{
    private $authenticationManager;

    private $memberRepository;

    private $secureSession;

    private $uriHelper;

    public function __construct(
        AuthenticationManager $authenticationManager,
        MemberRepository $memberRepository,
        SecureSession $secureSession,
        UriHelper $uriHelper)
    {
        $this->authenticationManager = $authenticationManager;
        $this->memberRepository = $memberRepository;
        $this->secureSession = $secureSession;
        $this->uriHelper = $uriHelper;
    }

    public function validate($password, Constraint $constraint)
    {
        $member = null;
        $uri = $this->uriHelper->getLastElement();
        $tdm = $this->secureSession->getObject($uri, TransitingDataManager::class);
        $username = $this
            ->authenticationManager
            ->getUsername($tdm)
        ;

        try {
            $member = $this->memberRepository->getMember($username);
            $this->memberRepository->checkPassword($member, $password);
        } catch (NonexistentMemberException | InvalidPasswordException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $password)
                ->addViolation()
            ;
        }
    }
}
