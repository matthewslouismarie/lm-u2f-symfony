<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class UriHelper
{
    private $uriArray;

    /**
     * @todo Check that RequestStack is concurrency-safe.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->uriArray = explode("/", $requestStack
            ->getCurrentRequest()
            ->getUri()
        );
    }

    public function getLastElement(): string
    {
        return $this->uriArray[count($this->uriArray) - 1];
    }
}
