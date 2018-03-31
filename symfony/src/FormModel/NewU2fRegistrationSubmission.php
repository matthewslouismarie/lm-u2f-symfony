<?php

namespace App\FormModel;

use Serializable;
use Symfony\Component\Validator\Constraints as Assert;

class NewU2fRegistrationSubmission implements Serializable
{
    /**
     * @todo Rename to u2fKeyResponse
     * @Assert\NotBlank(message = "You did not validate your U2F key.")
     */
    private $u2fTokenResponse;

    /**
     * @todo Rename to name?
     * @Assert\NotBlank()
     */
    private $u2fKeyName;

    public function __construct(
        ?string $u2fKeyName = null,
        ?string $u2fTokenResponse = null)
    {
        $this->u2fKeyName = $u2fKeyName;
        $this->u2fTokenResponse = $u2fTokenResponse;
    }

    public function getU2fKeyName(): ?string
    {
        return $this->u2fKeyName;
    }

    public function getU2fTokenResponse(): ?string
    {
        return $this->u2fTokenResponse;
    }

    public function setU2fKeyName(?string $u2fKeyName): void
    {
        $this->u2fKeyName = $u2fKeyName;
    }

    public function setU2fTokenResponse(?string $u2fTokenResponse): void
    {
        $this->u2fTokenResponse = $u2fTokenResponse;
    }

    public function serialize(): string
    {
        return serialize([
            $this->u2fKeyName,
            $this->u2fTokenResponse,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->u2fKeyName,
            $this->u2fTokenResponse) = unserialize($serialized);
    }
}
