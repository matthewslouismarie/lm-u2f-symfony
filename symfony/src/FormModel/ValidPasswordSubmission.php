<?php

namespace App\FormModel;

use App\Validator\Constraints\ValidPassword;

/**
 * @todo Check password is valid.
 */
class ValidPasswordSubmission implements ISubmission
{
    /**
     * @ValidPassword()
     */
    public $password;

    public function __construct(
        ?string $password = null)
    {
        $this->password = $password;
    }

    public function serialize(): string
    {
        return serialize([
            $this->password,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->password) = unserialize($serialized);
    }
}
