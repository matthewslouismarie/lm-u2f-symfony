<?php

namespace App\Tests\Controller;

use App\Form\Filler\CredentialRegistrationFiller;

class MemberRegistrationTest extends TestCaseTemplate
{
    public function testRegistration(): void
    {
        $this->doGet('/not-authenticated/register');
        $this->assertEquals(200, $this->getHttpStatusCode());
        $filler = new CredentialRegistrationFiller(
            $this->getCrawler(),
            'pwd',
            'pwd',
            'chat')
        ;
        $this->submit($filler->fillForm());
    }
}
