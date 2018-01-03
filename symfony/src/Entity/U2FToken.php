<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\U2FTokenRepository")
 */
class U2FToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @todo rename to owner
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Member")
     */
    private $member;

    /**
     * @ORM\Column(type="string", length=788)
     */
    private $attestation;

    /**
     * @ORM\Column(type="integer")
     */
    private $counter;

    /**
     * @ORM\Column(type="string", length=88)
     */
    private $keyHandle;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    private $registrationDateTime;

    /**
     * @ORM\Column(type="string", length=88)
     */
    private $publicKey;

    public function __construct(
        string $attestation,
        int $counter,
        string $keyHandle,
        Member $member,
        string $name,
        \DateTimeImmutable $registrationDateTime,
        string $publicKey)
    {
        $this->attestation = $attestation;
        $this->counter = $counter;
        $this->keyHandle = $keyHandle;
        $this->member = $member;
        $this->name = $name;
        $this->registrationDateTime = $registrationDateTime;
        $this->publicKey = $publicKey;
    }
    
    public function getAttestation(): string
    {
        return $this->attestation;
    }
    
    public function getCounter(): int
    {
        return $this->counter;
    }
    
    public function getKeyHandle(): string
    {
        return $this->keyHandle;
    }

    public function getRegistrationDateTime(): \DateTimeImmutable
    {
        return $this->registrationDateTime;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}
