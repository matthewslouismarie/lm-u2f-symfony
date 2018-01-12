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

    public function getU2fTokenResponse()
    {
        return $this->u2fTokenResponse;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    public function setU2fTokenResponse($u2fTokenResponse)
    {
        $this->u2fTokenResponse = $u2fTokenResponse;
    }

    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }
}