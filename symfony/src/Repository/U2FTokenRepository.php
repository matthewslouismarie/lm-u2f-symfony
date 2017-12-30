<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\U2FToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Firehed\U2F\Registration;

class U2FTokenRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, U2FToken::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('u')
            ->where('u.something = :value')->setParameter('value', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function getMemberRegistrations(int $member_id): array
    {
        $qb = $this->createQueryBuilder('u2ftoken');
        $u2f_tokens = $qb->where('u2ftoken.member = :member_id')
                      ->setParameter('member_id', $member_id)
                      ->getQuery()
                      ->getResult();

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
}
