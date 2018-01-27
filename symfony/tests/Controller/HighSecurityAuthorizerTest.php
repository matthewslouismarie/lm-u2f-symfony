<?php

namespace App\Tests\Controller;

use App\Entity\Member;
use App\Entity\U2fToken;
use Firehed\U2F\RegisterRequest;

class HighSecurityAuthorizerTest extends AbstractAccessManagementTestCase
{
    private $sSession;

    public function setUp()
    {
        parent::setUp();
        $this->sSession = $this
            ->getContainer()
            ->get('App\Service\SecureSession')
        ;
    }

    private function confirmPasswordReset()
    {
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('user_confirmation[submit]')
        ;
        $form = $submitButton->form();
        $this
            ->getClient()
            ->submit($form)
        ;
    }

    public function testPasswordReset()
    {
        $this
            ->getClient()
            ->request('GET', '/not-authenticated/request-password-reset')
        ;
        $this->enterValidUsername();
        $this->authorize();
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('password_update[submit]')
        ;
        $form = $submitButton->form(array(
            'password_update[password]' => 'mega',
            'password_update[passwordConfirmation]' => 'mega',
        ));
        $this
            ->getClient()
            ->submit($form)
        ;
        $member = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Member::class)
            ->find(1)
        ;
        $this->resetU2fCounter();
        $this->logIn('louis', 'mega');
        $this->runLoggedInTests();
    }

    public function testU2fTokenReset()
    {
        $this->logIn('louis', 'hello');
        $this->runLoggedInTests();
        $this
            ->getClient()
            ->request('GET', '/authenticated/request-u2f-token-reset')
        ;

        $this->assertTrue($this->isRedirection());
        $this
            ->getClient()
            ->followRedirect()
        ;
        $this->assertTrue($this->isRedirection());
        $this->authorize(true);

        $registration = $this->getU2fTokenRegistration();
        $registerRequestSid = $this
            ->sSession
            ->storeObject($registration['registerRequest'], RegisterRequest::class)
        ;
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_registration[submit]')
        ;
        $form = $submitButton->form([
            'u2f_registration[requestId]' => $registerRequestSid,
            'u2f_registration[u2fKeyName]' => 'Just a name.',
            'u2f_registration[u2fTokenResponse]' => $registration['u2fTokenResponse'],
        ]);
        $this
            ->getClient()
            ->submit($form)
        ;
        $deletedU2fToken = $this
            ->getContainer()
            ->get('doctrine')
            ->getRepository(U2fToken::class)
            ->find(3)
        ;
        $this->assertNull($deletedU2fToken);

        $newToken = $this
            ->getContainer()
            ->get('doctrine')
            ->getRepository(U2fToken::class)
            ->findOneBy([
                'publicKey' => 'BDjHma+8hI7VV1gk9lBDmg3YXWbNLcDM0GCY+94Y/87YctZ666cTwvvLOhSAgjmQfg2X8sT9P1HNsQggfP45fp8=',
            ])
        ;
        $this->assertNotNull($newToken);
    }

    private function enterValidUsername()
    {
        $this
            ->getClient()
            ->followRedirect()
        ;
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('existing_username[submit]')
        ;
        $usernameForm = $submitButton->form(array(
            'existing_username[username]' => 'louis',
        ));
        $this->getClient()->submit($usernameForm);
        $this->assertTrue($this->getClient()->getResponse()->isRedirection());
    }

    private function enterValidU2fTokenResponse()
    {
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_authentication[submit]')
        ;
        $u2fAuthenticationForm = $submitButton
            ->form($this->getValidU2fTokenResponse())
        ;
        $this
            ->getClient()
            ->submit($u2fAuthenticationForm)
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirection());
    }

    private function enterValidSecondU2fTokenResponse()
    {
        $submitButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_authentication[submit]')
        ;
        $u2fAuthenticationForm = $submitButton
            ->form($this->getValidSecondU2fTokenResponse())
        ;
        $this
            ->getClient()
            ->submit($u2fAuthenticationForm)
        ;
        $this->assertTrue($this->getClient()->getResponse()->isRedirection());
    }

    private function checkNSignRequests(int $expectedNSignRequests)
    {
        $inlineScript = $this
            ->getClient()
            ->getCrawler()
            ->filter('script:contains("version")')
            ->text()
        ;
        $this->assertEquals(
            $expectedNSignRequests,
            substr_count($inlineScript, '{"version":"U2F_V2","challenge"'))
        ;
    }

    private function authorize(bool $usernameAlreadySet = false)
    {
        $this
            ->getClient()
            ->followRedirect()
        ;
        $this->checkNSignRequests(3);
        $this->assertEquals(3, count($this->getContainer()->get('doctrine')->getManager()->getRepository(U2fToken::class)->getMemberRegistrations(1)));
        $this->enterValidSecondU2fTokenResponse();
        $this->getClient()->followRedirect();
        $this->checkNSignRequests(2);
        $this->resetU2fCounter();
        $this->enterValidU2fTokenResponse();
        $this->getClient()->followRedirect();
    }

    private function isRedirection(): bool
    {
        return $this
            ->getClient()
            ->getResponse()
            ->isRedirection()
        ;
    }

    private function getU2fTokenRegistration(): array
    {
        $registerRequest = new RegisterRequest();
        $registerRequest->setAppId('https://172.16.238.10');
        $registerRequest->setChallenge('MffuQZVLIWGqgS_PKuINvg');
        $u2fTokenResponse = '{"registrationData":"BQQ4x5mvvISO1VdYJPZQQ5oN2F1mzS3AzNBgmPveGP_O2HLWeuunE8L7yzoUgII5kH4Nl_LE_T9RzbEIIHz-OX6fQGsDSHR-i7mj0GsK8-bqUk7coFV23zdHj0c0034dc9IYtLhCxmnwDyk4p62VlKljrfM0kfNMjL7hj72LXqsuT4cwggJKMIIBMqADAgECAgQSSnL-MA0GCSqGSIb3DQEBCwUAMC4xLDAqBgNVBAMTI1l1YmljbyBVMkYgUm9vdCBDQSBTZXJpYWwgNDU3MjAwNjMxMCAXDTE0MDgwMTAwMDAwMFoYDzIwNTAwOTA0MDAwMDAwWjAsMSowKAYDVQQDDCFZdWJpY28gVTJGIEVFIFNlcmlhbCAyNDk0MTQ5NzIxNTgwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAAQ9ixu9L8v2CG4QdHFgFGhIQVPBxtO0topehV5uQHV-4ivNiYi_O-_XzfIcsL9dehUNhEr-mBA8bGYH2fquKHwCozswOTAiBgkrBgEEAYLECgIEFTEuMy42LjEuNC4xLjQxNDgyLjEuMTATBgsrBgEEAYLlHAIBAQQEAwIFIDANBgkqhkiG9w0BAQsFAAOCAQEAoU8e6gB29rhHahCivnLmDQJxu0ZbLfv8fBvRLTUZiZFwMmMdeV0Jf6MKJqMlY06FchvC0BqGMD9rwHXlmXMZ4SIUiwSW7sjR9PlM9BEN5ibCiUQ9Hw9buyOcoT6B0dWqnfWvjjYSZHW_wjrwYoMVclJ2L_aIebzw71eNVdZ_lRtPMrY8iupbD5nGfX2BSn_1pvUt-D6JSjpdnIuC5_i8ja9MgBdf-Jcv2nkzPsRl2AbqzJSPG6siBFqVVYpIwgIm2sAD1B-8ngXqKKa7XhCkneBgoKT2omdqNNaMSr6MYYdDVbkCfoKMqeBksALWLo2M8HRJIXU9NePIfF1XeUU-dzBFAiAc9EmRGx3W8vwGlb9QsS3I-T68Mhtq-x9_LmQ6Ma-uaAIhAMtw_rP3C7e86ycjpBP0oDPxCJVkp3cpHeaX5ebmv6s7","version":"U2F_V2","challenge":"MffuQZVLIWGqgS_PKuINvg","appId":"https://172.16.238.10","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZmluaXNoRW5yb2xsbWVudCIsImNoYWxsZW5nZSI6Ik1mZnVRWlZMSVdHcWdTX1BLdUlOdmciLCJvcmlnaW4iOiJodHRwczovLzE3Mi4xNi4yMzguMTAiLCJjaWRfcHVia2V5IjoidW51c2VkIn0"}';

        return [
            'registerRequest' => $registerRequest,
            'u2fTokenResponse' => $u2fTokenResponse,
            'publicKey' => 'BDjHma+8hI7VV1gk9lBDmg3YXWbNLcDM0GCY+94Y/87YctZ666cTwvvLOhSAgjmQfg2X8sT9P1HNsQggfP45fp8=',
        //     'u2fToken' => new U2fToken(
        //         null,
        //         'MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc=',
        //         0,
        //         'awNIdH6LuaPQawrz5upSTtygVXbfN0ePRzTTfh1z0hi0uELGafAPKTinrZWUqWOt8zSR80yMvuGPvYteqy5Phw==',
        //         $this->getContainer()
        //         ["registrationDateTime":"App\Entity\U2fToken":private]=>
        //         object(DateTimeImmutable)#189 (3) {
        //           ["date"]=>
        //           string(26) "2018-01-12 13:30:31.429694"
        //           ["timezone_type"]=>
        //           int(3)
        //           ["timezone"]=>
        //           string(3) "UTC"
        //         }
        //         ["publicKey":"App\Entity\U2fToken":private]=>
        //         string(88) "BDjHma+8hI7VV1gk9lBDmg3YXWbNLcDM0GCY+94Y/87YctZ666cTwvvLOhSAgjmQfg2X8sT9P1HNsQggfP45fp8="
        //       }
        //     ),
        ];
    }
}
