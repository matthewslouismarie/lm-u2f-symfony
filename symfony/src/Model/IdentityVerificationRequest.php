<?php

namespace App\Model;

/**
 * @todo URL or URI?
 */
class IdentityVerificationRequest
{
    private $sid;

    private $url;

    public function __construct(string $sid, string $url)
    {
        $this->sid = $sid;
        $this->url = $url;
    }

    public function getSid(): string
    {
        return $this->sid;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
