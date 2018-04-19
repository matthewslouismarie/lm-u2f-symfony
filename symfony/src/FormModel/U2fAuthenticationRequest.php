<?php

declare(strict_types=1);

namespace App\FormModel;

use Firehed\U2F\SignRequest;
use InvalidArgumentException;

class U2fAuthenticationRequest implements ISubmission
{
    private $signRequests;

    public function __construct(array $signRequests)
    {
        foreach ($signRequests as $signRequest) {
            if (!$signRequest instanceof SignRequest) {
                throw new InvalidArgumentException();
            }
        }
        $this->signRequests = $signRequests;
    }

    public function getSignRequests(): array
    {
        return $this->signRequests;
    }

    public function getJsonSignRequests(): string
    {
        return json_encode(array_values($this->signRequests));
    }

    public function serialize(): string
    {
        return serialize([
            $this->signRequests,
        ]);
    }

    public function unserialize($serialized): void
    {
        list($this->signRequests) = unserialize($serialized);
    }
}
