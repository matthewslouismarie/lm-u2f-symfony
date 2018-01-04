<?php

namespace App\Tests\Controller\TripleKeyStrategy;

use App\Tests\DbWebTestCase;

class RegistrationTest extends DbWebTestCase
{
    public function testFirstKeyRegistration()
    {
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/mkps/master-pair-first-key');
        $button = $firstCrawler->selectButton('u2_f_token_registration[submit]');
        $form = $button->form(array(
            'u2_f_token_registration[name]' => 'My First Key!!',
            'u2_f_token_registration[u2fTokenResponse]' => 'invalid response'
        ));
        $secondCrawler = $this->getClient()->submit($form);
        
        $this->assertContains(
            'error',
            $this->getClient()->getResponse()->getContent()
        );
    }
}