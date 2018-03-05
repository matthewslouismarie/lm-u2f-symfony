<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\U2fToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Firehed\U2F\Registration;

class U2fTokenRepository extends ServiceEntityRepository
{
    private $om;

    public function __construct(
        ObjectManager $om,
        RegistryInterface $registry)
    {
        parent::__construct($registry, U2fToken::class);
        $this->om = $om;
    }

    public function getMemberRegistrations(int $member_id): array
    {
        $u2f_tokens = $this->getU2fTokens($member_id);
        $registrations = array();
        foreach ($u2f_tokens as $tkn) {
            $registration = new Registration();
            $registration->setCounter($tkn->getCounter());
            $registration->setAttestationCertificate($tkn->getAttestation());
            $registration->setPublicKey(base64_decode($tkn->getPublicKey()));
            $registration->setKeyHandle(base64_decode($tkn->getKeyHandle()));
            $registrations[$tkn->getId()] = $registration;
        }

        return $registrations;
    }

    public function getExcept(Member $member, array $ids)
    {
        $qb = $this
            ->createQueryBuilder('u2ftoken')
        ;

        return $qb
            ->where('u2ftoken.member = :member_id')
            ->setParameter('member_id', $member->getId())
            ->andWhere($qb->expr()->notIn('u2ftoken.id', $ids))
            ->getQuery()
            ->getResult()
        ;
    }

    public function getU2fTokens(int $memberId): array
    {
        $qb = $this->createQueryBuilder('u2ftoken');
        $u2fTokens = $qb
            ->where('u2ftoken.member = :member_id')
            ->setParameter('member_id', $memberId)
            ->getQuery()
            ->getResult()
        ;

        return $u2fTokens;
    }

    /**
     * @todo Delete?
     */
    public function resetCounters()
    {
        $u2fTokens = $this->findAll();
        foreach ($u2fTokens as $u2fToken) {
            $u2fToken->setCounter(0);
        }
        $this
            ->om
            ->flush()
        ;
    }
}
