<?php

namespace App\Tests;

use App\Entity\U2fToken;

class U2fDeviceManagementTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testManagementPage()
    {
        $this->login();
        $this->doGet('/authenticated/manage-u2f-keys');

        $member = $this->getLoggedInMember();
        $u2fTokens = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($member)
        ;
        $this->assertEquals(
            count($u2fTokens),
            $this->getCrawler()->filter('.item-list > .item')->count()
        );
    }

    public function testDeviceRemoval()
    {
        $this->login();
        $member = $this->getLoggedInMember();
        $u2fTokens = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($member)
        ;
        $originalNOfU2fKeys = count($u2fTokens);
        $this->doGet('/authenticated/manage-u2f-keys');
        $firstLink = $this
            ->getCrawler()
            ->filter('.item-list > .item > .link')
            ->first()
            ->link()
        ;
        $this->doGet($firstLink->getUri());
        $this->submit($this
            ->getUserConfirmationFiller()
            ->fillForm($this->getCrawler()))
        ;
        $this->performHighSecurityIdCheck();
        $newNOfU2fKeys = count($this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($member))
        ;
        $this->assertEquals(
            $originalNOfU2fKeys - 1,
            $newNOfU2fKeys)
        ;
    }
}
