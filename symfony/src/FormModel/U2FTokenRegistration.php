<?php

namespace App\FormModel;

use Symfony\Component\Validator\Constraints as Assert;

class U2FTokenRegistration
{
    /**
     * @Assert\NotBlank()
     */
    private $u2fTokenResponse;

    /**
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @Assert\NotBlank()
     */
    private $requestId;
    
    public function getU2fTokenResponse()
    {
        return $this->u2fTokenResponse;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getRequestId()
    {
        return $this->requestId;
    }

    public function setU2fTokenResponse($challenge)
    {
        $this->challenge = $challenge;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }
}