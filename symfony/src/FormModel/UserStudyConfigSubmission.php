<?php

namespace App\FormModel;

use App\Validator\Constraints\ValidUserStudyConfig;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @ValidUserStudyConfig()
 */
class UserStudyConfigSubmission
{
    /**
     * @NotNull()
     * @Type("bool")
     */
    public $isUserStudyModeActive;

    /**
     * @Type("string")
     */
    public $participantId;

    public function __construct(
        ?bool $isUserStudyModeActive = null,
        ?string $participantId = null
    ) {
        $this->isUserStudyModeActive = $isUserStudyModeActive;
        $this->participantId = $participantId;
    }
}
