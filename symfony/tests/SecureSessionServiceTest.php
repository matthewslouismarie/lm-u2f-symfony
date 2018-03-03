<?php

namespace App\Tests;

use App\Entity\Member;

class SecureSessionTest extends TestCaseTemplate
{
    public function testSecureSession()
    {
        $sSession = $this->getSecureSession();
        $member = new Member(null, 'louis');
        $sid = $sSession->storeObject($member, Member::class);
        $sessionMember = $sSession->getObject($sid, Member::class);
        $this->assertEquals($member, $sessionMember);
    }
}
