<?php

namespace App\Tests;

use App\Entity\U2fToken;
use App\Service\Form\Filler\U2fDeviceRegistrationFiller;
use App\Tests\TestCaseTemplate;
use App\Tests\LoginTrait;

class U2fDeviceRegistrationTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testU2fDeviceRegistration()
    {
        $this->login();
        $u2fTokenRepository = $this
            ->getObjectManager()
            ->getRepository(U2fToken::class);
        $nU2fKeys = count(
            $u2fTokenRepository->getU2fTokens($this->getLoggedInMember())
        );
        $this->doGet('/authenticated/register-u2f-device');
        $this->followRedirect();
        $this->submit(
            $this
                ->get(U2fDeviceRegistrationFiller::class)
                ->fillForm($this->getCrawler(), $this->getUriLastPart())
        );
        $this->assertEquals(
            $nU2fKeys + 1,
            count(
                $u2fTokenRepository->getU2fTokens($this->getLoggedInMember())
            )
        );
    }
}
