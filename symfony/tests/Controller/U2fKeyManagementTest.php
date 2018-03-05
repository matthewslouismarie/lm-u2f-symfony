<?php

namespace App\Tests\Controller;

use App\Entity\U2fToken;
use App\Tests\TestCaseTemplate;

class U2fKeyManagementTest extends TestCaseTemplate
{
    use AuthenticationTrait;

    public function testManagementPage()
    {
        $this->authenticateAsLouis();
        $this->doGet('/authenticated/manage-u2f-keys');

        $louis = $this
            ->get('security.token_storage')
            ->getToken()
            ->getUser();
        $u2fTokens = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($louis->getId())
        ;
        $this->assertEquals(
            count($u2fTokens),
            $this->getCrawler()->filter('.item')->count()
        );
    }
}
