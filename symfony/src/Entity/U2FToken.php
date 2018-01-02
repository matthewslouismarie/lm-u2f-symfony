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
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @todo rename to owner
     * @ORM\ManyToOne(targetEntity="Member")
     */
    private $member;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $counter;

    /**
     * @ORM\Column(type="string", length=788)
     */
    private $attestation;

    /**
     * @ORM\Column(type="string", length=88)
     */
    private $public_key;

    /**
     * @ORM\Column(type="string", length=88)
     */
    private $key_handle;

    public function __construct(
        ?int $id,
        string $attestation,
        int $counter,
        string $key_handle,
        Member $member,
        string $name,
        string $public_key)
    {
        $this->id = $id;
        $this->attestation = $attestation;
        $this->counter = $counter;
        $this->key_handle = $key_handle;
        $this->member = $member;
        $this->name = $name;
        $this->public_key = $public_key;
    }

    public function getId(): int
    {
        return $this->id;
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
        return $this->key_handle;
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
        return $this->public_key;
    }
    
    /**
     * @todo to replace with a builder class
     */
    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }
}
