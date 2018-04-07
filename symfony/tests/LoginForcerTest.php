<?php

namespace App\Tests;

use App\DataFixtures\MembersFixture;
use App\Entity\Member;
use App\Enum\Setting;
use App\Tests\LoginTrait;
use App\Tests\TestCaseTemplate;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\RequestDatum;
use LM\Common\Model\BooleanObject;
use Symfony\Component\HttpFoundation\Request;

class LoginForcerTest extends TestCaseTemplate
{
    public function testLogInForcer()
    {
        $this->assertNull($this->getLoggedInMember());
        $loginForcer = $this->get('App\Service\LoginForcer');
        $user = $this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->findOneBy([
                'username' => MembersFixture::USERNAME,
            ])
        ;
        $loginForcer->logUserIn(new Request(), $user);
        $this->assertNotNull($this->getLoggedInMember());
    }
}
