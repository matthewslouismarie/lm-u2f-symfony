<?php

namespace App\Repository;

use App\Entity\AppSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AppSettingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AppSetting::class);
    }

    public function set(string $id, $value): void
    {
        $em = $this->getEntityManager();
        $appSetting = $this->find($id);
        if (null === $appSetting) {
            $em->persist(new AppSetting($id, $value));
        } else {
            $appSetting->setValue($value);
        }
        $em->flush();
    }

    /**
     * @todo Use more specific exception.
     */
    public function get(string $id)
    {
        $appSetting = $this->find($id);
        if (null !== $appSetting) {
            return $appSetting->getValue();
        } else {
            return;
        }
    }
}
