<?php

declare(strict_types=1);

namespace App\Tests;

use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Service\PasswordValidator;

class ValidatorTest extends TestCaseTemplate
{
    public function testValidNewPasswordValidator()
    {
        $validator = $this->get(PasswordValidator::class);
        $config = $this->get(AppConfigManager::class);

        $config->set(Setting::PWD_SPECIAL_CHARS, true);

        $this->assertTrue($validator->hasSpecialChars('#'));
        $this->assertTrue($validator->hasSpecialChars(','));
        $this->assertTrue($validator->hasSpecialChars('«'));
        $this->assertTrue($validator->hasSpecialChars('é'));
        $this->assertTrue($validator->hasSpecialChars('_'));
        $this->assertTrue($validator->hasSpecialChars(','));
        $this->assertTrue($validator->hasSpecialChars('£'));

        $this->assertFalse($validator->hasSpecialChars('AEeui'));
        $this->assertFalse($validator->hasSpecialChars('99234'));
        $this->assertFalse($validator->hasSpecialChars('3'));
        $this->assertFalse($validator->hasSpecialChars('a'));
        $this->assertFalse($validator->hasSpecialChars('23euiAEU'));
    }
}
