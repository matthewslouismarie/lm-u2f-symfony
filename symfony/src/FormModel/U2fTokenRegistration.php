<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Add validation for key certificate.
 */
class U2fTokenRegistration
{
    /**
     * @Assert\NotBlank()
     */
    private $u2fTokenResponse;

    /**
     * @Assert\NotBlank()
     */
    private $requestId;

    public function __construct(?string $requestId = null)
    {
        $this->requestId = $requestId;
    }

    public function getU2fTokenResponse(): ?string
    {
        return $this->u2fTokenResponse;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setU2fTokenResponse(?string $u2fTokenResponse): void
    {
        $this->u2fTokenResponse = $u2fTokenResponse;
    }

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }
}
