<?php

namespace App\TransitingUserInput;

use Serializable;
use App\FormModel\U2fLoginSubmission;

/**
 * @todo The RP request - token challenge should be kept instead of the id, and
 * the id should be dynamically determined from that.
 */
class U2fToU2fUserInput implements Serializable
{
    private $u2fLoginSubmission;

    private $usedU2fTokenId;

    private $uToU2fUserInput;

    public function __construct(
        U2fLoginSubmission $u2fLoginSubmission,
        int $usedU2fTokenId,
        UToU2fUserInput $uToU2fUserInput)
    {
        $this->u2fLoginSubmission = $u2fLoginSubmission;
        $this->usedU2fTokenId = $usedU2fTokenId;
        $this->uToU2fUserInput = $uToU2fUserInput;
    }

    public function getU2fLoginSubmission(): U2fLoginSubmission
    {
        return $this->u2fLoginSubmission;
    }

    public function getUsedU2fTokenId(): int
    {
        return $this->usedU2fTokenId;
    }

    public function getUToU2fUserInput(): UToU2fUserInput
    {
        return $this->uToU2fUserInput;
    }

    public function serialize(): string
    {
        return serialize([
            $this->u2fLoginSubmission,
            $this->usedU2fTokenId,
            $this->uToU2fUserInput,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->u2fLoginSubmission,
            $this->usedU2fTokenId,
            $this->uToU2fUserInput) = unserialize($serialized);
    }
}
