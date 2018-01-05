<?php

namespace App\Tests\Controller\TripleKeyStrategy;

use App\Entity\U2FToken;
use App\Entity\Member;
use App\Factory\MemberFactory;
use App\Tests\DbWebTestCase;

class RegistrationTest extends DbWebTestCase
{
    private function usernameAndPassword()
    {
        $session = $this->getContainer()->get('session');
        $hasher = $this->getContainer()->get('security.password_encoder');
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/tks/username-and-password');
        $button = $firstCrawler->selectButton('registration[submit]');
        $form = $button->form(array(
            'registration[username]' => 'johndoe',
            'registration[password]' => 'password',
        ));
        $secondCrawler = $this->getClient()->submit($form);
        $sessionMember = $session->get('tks_member');
        $this->assertEquals(
            'johndoe',
            $sessionMember->getUsername()
        );
        $this->assertTrue($hasher->isPasswordValid($sessionMember, 'password'));
        $this->assertFalse($hasher->isPasswordValid($sessionMember, 'pssword'));
        $this->checkUrlStatusCode('/tks/key-1', 200);
    }

    private function key(int $keyNo)
    {
        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/tks/key-'.$keyNo);
        $button = $firstCrawler->selectButton('u2_f_token_registration[submit]');
        $form = $button->form(array(
            'u2_f_token_registration[u2fTokenResponse]' => 'invalid response'
        ));
        $secondCrawler = $this->getClient()->submit($form);
        
        $this->assertContains(
            'error',
            $this->getClient()->getResponse()->getContent()
        );
    }

    private function enterDetails()
    {
        $session = $this->getContainer()->get('session');
        $mf = $this->getContainer()->get('App\Factory\MemberFactory');

        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 302);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);     
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 302);

        $member = $mf->create(2, 'johndoe2', 'password');
        $session->set('tks_member', $member);
        $session->save();
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 200);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);     
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 200);

        $firstU2fToken = new U2FToken(
            2,
            "MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc=",
            0,
            "g3q8SBz3SCHtyRatxx5+mrq77o4vIXKhhvixkYwkYIHUL/4fzO4TSBcvks63fJw49C3Gp31UY7o0TLPXWHsaNA==",
            $session->get('tks_member'),
            new \DateTimeImmutable(),
            "BMrpcsz/LJ/4L8LlaeyJNryU4RHKCGHZyiQubzOCK0FtRbdiCyTUeQ1ZVsIkqU7wut0mpt7tLDG7zRSB2wXK+Q8="
        );
        $session->set('tks_u2f_token_1', $firstU2fToken);
        $session->save();
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 200);
        $this->checkUrlStatusCode('/tks/key-2', 200);
        $this->checkUrlStatusCode('/tks/key-3', 302);     
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 200);

        $secondU2fToken = new U2FToken(
            3,
            "MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc=",
            0,
            "g3q8SBz3SCHtyRatxx5+mrq77o4vIXKhhvixkYwkYIHUL/4fzO4TSBcvks63fJw49C3Gp31UY7o0TLPXWHsaNA==",
            $session->get('tks_member'),
            new \DateTimeImmutable(),
            "BMrpcsz/LJ/4L8LlaeyJNryU4RHKCGHZyiQubzOCK0FtRbdiCyTUeQ1ZVsIkqU7wut0mpt7tLDG7zRSB2wXK+Q8="
        );
        $session->set('tks_u2f_token_2', $secondU2fToken);
        $session->save();
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 200);
        $this->checkUrlStatusCode('/tks/key-2', 200);
        $this->checkUrlStatusCode('/tks/key-3', 200);     
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 200);

        $thirdU2fToken = new U2FToken(
            4,
            "MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc=",
            0,
            "g3q8SBz3SCHtyRatxx5+mrq77o4vIXKhhvixkYwkYIHUL/4fzO4TSBcvks63fJw49C3Gp31UY7o0TLPXWHsaNA==",
            $session->get('tks_member'),
            new \DateTimeImmutable(),
            "BMrpcsz/LJ/4L8LlaeyJNryU4RHKCGHZyiQubzOCK0FtRbdiCyTUeQ1ZVsIkqU7wut0mpt7tLDG7zRSB2wXK+Q8="
        );
        $session->set('tks_u2f_token_3', $thirdU2fToken);
        $session->save();
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 200);
        $this->checkUrlStatusCode('/tks/key-2', 200);
        $this->checkUrlStatusCode('/tks/key-3', 200);     
        $this->checkUrlStatusCode('/tks/finish-registration', 200);
        $this->checkUrlStatusCode('/tks/reset-registration', 200);

        return array(
            'tks_member' => $member,
            'tks_u2f_token_1' => $firstU2fToken,
            'tks_u2f_token_2' => $secondU2fToken,
            'tks_u2f_token_3' => $thirdU2fToken,
        );
    }

    public function testSteps()
    {
        $variables = $this->enterDetails();

        $firstU2fToken = $variables['tks_u2f_token_1'];
        $secondU2fToken = $variables['tks_u2f_token_2'];
        $thirdU2fToken = $variables['tks_u2f_token_3'];
        
        $firstCrawler = $this
        ->getClient()
        ->request('GET', '/tks/finish-registration');
        $button = $firstCrawler->selectButton('user_confirmation[confirmation]');
        $form = $button->form();
        $secondCrawler = $this->getClient()->submit($form);
        $session = $this->getContainer()->get('session');

        $this->assertNull($session->get('tks_member'));
        $this->assertNull($session->get('tks_u2f_token_1'));
        $this->assertNull($session->get('tks_u2f_token_2'));
        $this->assertNull($session->get('tks_u2f_token_3'));

        $doctrine = $this->getContainer()->get('doctrine');
        $dbMember = $doctrine->getRepository(Member::class)->find(2);
        $hasher = $this->getContainer()->get('security.password_encoder');
        $this->assertTrue($hasher->isPasswordValid($dbMember, 'password'));        
        $this->assertFalse($hasher->isPasswordValid($dbMember, ''));

        $dbU2fTokens = $doctrine->getRepository(U2FToken::class)->findBy(array(
            'member' => $dbMember,
        ));
        $this->assertEquals(3, count($dbU2fTokens));
        $this->assertEquals($firstU2fToken, $dbU2fTokens[0]);
        $this->assertEquals($secondU2fToken, $dbU2fTokens[1]);
        $this->assertEquals($thirdU2fToken, $dbU2fTokens[2]);
        
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 302);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);     
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 302);
    }

    public function testResetButton()
    {
        $variables = $this->enterDetails();

        $firstU2fToken = $variables['tks_u2f_token_1'];
        $secondU2fToken = $variables['tks_u2f_token_2'];
        $thirdU2fToken = $variables['tks_u2f_token_3'];

        $firstCrawler = $this
            ->getClient()
            ->request('GET', '/tks/reset-registration');
        $button = $firstCrawler->selectButton('user_confirmation[confirmation]');
        $form = $button->form();
        $secondCrawler = $this->getClient()->submit($form);

        $session = $this->getContainer()->get('session');

        $this->assertNull($session->get('tks_member'));
        $this->assertNull($session->get('tks_u2f_token_1'));
        $this->assertNull($session->get('tks_u2f_token_2'));
        $this->assertNull($session->get('tks_u2f_token_3'));
        
        $this->checkUrlStatusCode('/tks/username-and-password', 200);
        $this->checkUrlStatusCode('/tks/key-1', 302);
        $this->checkUrlStatusCode('/tks/key-2', 302);
        $this->checkUrlStatusCode('/tks/key-3', 302);     
        $this->checkUrlStatusCode('/tks/finish-registration', 302);
        $this->checkUrlStatusCode('/tks/reset-registration', 302);
    }
}