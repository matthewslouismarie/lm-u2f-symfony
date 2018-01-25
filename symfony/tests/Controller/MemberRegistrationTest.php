<?php

namespace App\Tests\Controller;

class MemberRegistrationTest extends TestCaseTemplate
{
    public function testRegistration(): void
    {
        $this->doGet('/not-authenticated/register');
        $this->assertEquals(200, $this->getHttpStatusCode());
    }
}
