<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use LM\Authentifier\Model\IMember;

/**
 * @todo Make immutable.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 */
class Member implements IMember, UserInterface, Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $roles;

    /**
     * @ORM\Column(length=25, unique=true)
     */
    private $username;

    public function __construct(?int $id, string $username, array $roles)
    {
        $this->id = $id;
        $this->username = $username;
        $this->roles = $roles;
    }

    public function eraseCredentials()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getHashedPassword(): string
    {
        return $this->password;
    }

    public function getSalt()
    {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->password,
            $this->roles,
            $this->username,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->password,
            $this->roles,
            $this->username,
        ) = unserialize($serialized);
    }
}
