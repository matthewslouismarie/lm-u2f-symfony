<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class U2fLoginSubmission
{
    /**
     * @Assert\NotBlank()
     */
    public $username;

    /**
     * @Assert\NotBlank()
     */
    public $password;

    /**
     * @Assert\NotBlank()
     */
    public $u2fTokenResponse;

    /**
     * @Assert\NotBlank()
     */
    public $requestId;

    public function __construct(
        ?string $username = null,
        ?string $password = null,
        ?string $u2fTokenResponse = null,
        ?string $requestId = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->u2fTokenResponse = $u2fTokenResponse;
        $this->requestId = $requestId;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}