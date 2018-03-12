<?php

namespace App\Tests\Controller;

use App\Entity\U2fToken;
use App\Tests\TestCaseTemplate;
use App\Tests\Controller\AuthenticationTrait;

class U2fRegistrationTest extends TestCaseTemplate
{
    use AuthenticationTrait;

    public function testU2fRegistration()
    {
        $this->authenticateAsLouis();
        $u2fTokenRepository = $this
            ->getObjectManager()
            ->getRepository(U2fToken::class);
        $nU2fKeys = count(
            $u2fTokenRepository->getU2fTokens($this->getLoggedInMember())
        );
        $this->doGet('/authenticated/register-u2f-key');
        $this->followRedirect();
        $this->submit(
            $this
                ->get('App\Service\Form\Filler\U2fKeyRegistrationFiller')
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
