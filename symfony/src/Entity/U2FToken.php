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
     * @ORM\ManyToOne(targetEntity="Member")
     */
    private $member;

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

    public function __construct(Member $member, int $counter,
                                string $attestation, string $public_key,
                                string $key_handle)
    {
        $this->member = $member;
        $this->counter = $counter;
        $this->attestation = $attestation;
        $this->public_key = $public_key;
        $this->key_handle = $key_handle;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }
    
    public function getAttestation(): string
    {
        return $this->attestation;
    }
    
    public function getPublicKey(): string
    {
        return $this->public_key;
    }
    
    public function getKeyHandle(): string
    {
        return $this->key_handle;
    }
    
    /**
     * @todo to replace with a builder class
     */
    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }
}
