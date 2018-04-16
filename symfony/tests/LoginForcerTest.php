<?php

namespace App\Tests;

use App\DataFixtures\AppFixture;
use App\Entity\Member;
use App\Service\LoginForcer;
use Symfony\Component\HttpFoundation\Request;

class LoginForcerTest extends TestCaseTemplate
{
    public function testLogInForcer()
    {
        $this->assertNull($this->getLoggedInMember());
        $loginForcer = $this->get(LoginForcer::class);
        $user = $this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->findOneBy([
                'username' => AppFixture::ADMIN_USERNAME,
            ])
        ;
        $loginForcer->logUserIn(new Request(), $user);
        $this->assertNotNull($this->getLoggedInMember());
    }
}
