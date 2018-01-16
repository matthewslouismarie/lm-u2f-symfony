<?php

namespace App\FormModel;

/**
 * @todo Rename to LoginRequest.
 */
class NewLoginRequest implements ISubmission
{
    private $successRoute;

    public function __construct(string $successRoute)
    {
        $this->successRoute = $successRoute;
    }

    public function getSuccessRoute(): string
    {
        return $this->successRoute;
    }

    public function serialize(): string
    {
        return serialize([
            $this->successRoute,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->successRoute) = unserialize($serialized);
    }
}
