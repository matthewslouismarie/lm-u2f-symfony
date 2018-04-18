<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use LM\Authentifier\Model\IU2fRegistration;

/**
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(
 *            columns={"member_id", "name"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\U2fTokenRepository")
 */
class U2fToken implements IU2fRegistration
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     */
    private $member;

    /**
     * @ORM\Column(type="string", length=2000)
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

    /**
     * @todo Should be a slug and unique for the user.
     * @ORM\Column()
     */
    private $name;

    public function __construct(
        ?int $id,
        string $attestation,
        int $counter,
        string $keyHandle,
        Member $member,
        \DateTimeImmutable $registrationDateTime,
        string $publicKey,
        string $name
    ) {
        $this->id = $id;
        $this->attestation = $attestation;
        $this->counter = $counter;
        $this->keyHandle = $keyHandle;
        $this->member = $member;
        $this->registrationDateTime = $registrationDateTime;
        $this->publicKey = $publicKey;
        $this->name = $name;
    }

    public function getAttestationCertificate(): string
    {
        return $this->attestation;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getU2fKeyName(): string
    {
        return $this->name;
    }

    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->attestation,
            $this->counter,
            $this->keyHandle,
            $this->member,
            $this->name,
            $this->publicKey,
            $this->registrationDateTime,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->attestation,
            $this->counter,
            $this->keyHandle,
            $this->member,
            $this->name,
            $this->publicKey,
            $this->registrationDateTime) = unserialize($serialized);
    }
}
