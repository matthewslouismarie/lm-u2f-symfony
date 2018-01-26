<?php

namespace App\Tests\Controller;

use App\Entity\U2fToken;
use App\Service\Form\Filler\CredentialAuthenticationFiller;
use App\Service\Form\Filler\LoginRequestFiller;
use Firehed\U2F\SignRequest;

/**
 * @todo Delete.
 */
abstract class AbstractAccessManagementTestCase extends TestCaseTemplate
{
    private $u2fCount = 0;

    public function logIn(string $username, string $password): void
    {
        $this->doGet('/not-authenticated/start-login');
        $this->followRedirect();
        $formFiller = $this->get('App\Service\Form\Filler\CredentialAuthenticationFiller');
        $this->submit(
            $formFiller->fillForm($this->getCrawler(), $password, $username)
        );

        if (!$this->isRedirect()) {
            return;
        }
        $this->followRedirect();
        $u2fButton = $this
            ->getCrawler()
            ->selectButton('new_u2f_authentication[submit]')
        ;
        $cycle = $this
            ->getU2fAuthenticationMocker()
            ->getNewCycle()
        ;
        $u2fForm = $u2fButton->form([
            'new_u2f_authentication[u2fTokenResponse]' => $cycle->getResponse(),
        ]);
        $sid = $this->getUriLastPart();
        $this->getSubmissionStack()->set($sid, 2, $cycle->getRequest());
        $this->submit($u2fForm);
        $this->assertIsRedirect();
        $this->followRedirect();
        if ("http://localhost/not-authenticated/finalise-login/{$sid}" !== $this->getUri()) {
            return;
        }
        $loginRequestFiller = $this->get('App\Service\Form\Filler\LoginRequestFiller');
        $this->submit($loginRequestFiller->fillForm($this->getClient()->getCrawler()));
    }

    public function logOut()
    {
        $logout = $this
            ->getClient()
            ->request('GET', '/authenticated/log-out');
        $button = $logout->selectButton('user_confirmation[submit]');
        $form = $button->form();
        $this->getClient()->submit($form);
    }

    public function runLoggedOutTests()
    {
        $this->checkUrlStatusCode(
            '/authenticated/change-password',
            302)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/log-out',
            302)
        ;
    }

    public function runLoggedInTests()
    {
        $this->checkUrlStatusCode(
            '/authenticated/change-password',
            200)
        ;
        $this->checkUrlStatusCode(
            '/authenticated/log-out',
            200)
        ;
    }

    public function resetU2fCounter()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $ubs = $this->getContainer()->get('App\Factory\U2fTokenFactory');
        $repo = $doctrine->getRepository(U2fToken::class);
        $u2fTokens = $repo->findAll();
        $om = $doctrine->getManager();
        foreach ($u2fTokens as $u2fToken) {
            $u2fToken->setCounter(0);
        }
        $om->flush();
    }

    public function upLogInFromUpPage(
        string $username,
        string $password)
    {
        $button = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('credential_authentication[submit]')
        ;
        $form = $button->form(array(
            'credential_authentication[username]' => $username,
            'credential_authentication[password]' => $password,
        ));
        $secondCrawler = $this->getClient()->submit($form);
    }

    public function ukLogInFromUkPage(string $requestId)
    {
        $content = $this
            ->getClient()
            ->getResponse()
            ->getContent()
        ;
        $postUpLoginButton = $this
            ->getClient()
            ->getCrawler()
            ->selectButton('u2f_authentication[submit]')
        ;
        $form = $postUpLoginButton->form($this->getValidU2fTokenResponse());

        $validateLogin = $this
            ->getClient()
            ->submit($form)
        ;
        $this->checkU2fTokens();
    }

    public function storeInSessionU2fToken(bool $isValid): string
    {
        $sSession = $this
            ->getContainer()
            ->get('App\Service\SecureSession')
        ;
        $signRequests = array();
        $signRequest = new SignRequest();
        if ($isValid) {
            $signRequest->setAppId('https://172.16.238.10');
        } else {
            $signRequest->setAppId('https://172.15.238.10');
        }
        $signRequest->setChallenge('lXaq82clJBmXNnNWL1W6GA');
        $signRequest->setKeyHandle(base64_decode('v8IplXz0zSQUXVYjvSWNcP/70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg=='));
        $signRequests[1] = $signRequest;

        return $sSession->storeArray($signRequests);
    }

    public function getValidU2fTokenResponse(): array
    {
        $u2fAuthenticationRequestId = $this->storeInSessionU2fToken(true);

        return array(
            'u2f_authentication[u2fAuthenticationRequestId]' => $u2fAuthenticationRequestId,
            'u2f_authentication[u2fTokenResponse]' => '{"keyHandle":"v8IplXz0zSQUXVYjvSWNcP_70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoibFhhcTgyY2xKQm1YTm5OV0wxVzZHQSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAAIkwRgIhAN1YRiOqMs1fOCOm7MuOxfYJ6qN7A8PdXrhEzejtw3gNAiEAgi0JJmODYRTN8qflhBNsAjuDkJz06hTUZi2LNbaU4gk"}',
        );
    }

    public function storeInSessionSecondU2fToken(bool $isValid): string
    {
        $sSession = $this
            ->getContainer()
            ->get('App\Service\SecureSession')
        ;
        $signRequests = array();
        $signRequest = new SignRequest();
        if ($isValid) {
            $signRequest->setAppId('https://172.16.238.10');
        } else {
            $signRequest->setAppId('https://172.15.238.10');
        }
        $signRequest->setChallenge('LKXEXoGL1X4yWVFfwGNhdQ');
        $signRequest->setKeyHandle(base64_decode('SlhahqO2AGMqu1KZwwVVFgLhkUaOwcuWRWVn1ITLmeq/V38yn1kfANGGrZCVl8qZSm8UF8qgyp8bGEWAVKWe1g=='));
        $signRequests[2] = $signRequest;

        return $sSession->storeArray($signRequests);
    }

    public function getValidSecondU2fTokenResponse(): array
    {
        $u2fAuthenticationRequestId = $this->storeInSessionSecondU2fToken(true);

        return array(
            'u2f_authentication[u2fAuthenticationRequestId]' => $u2fAuthenticationRequestId,
            'u2f_authentication[u2fTokenResponse]' => '{"keyHandle":"SlhahqO2AGMqu1KZwwVVFgLhkUaOwcuWRWVn1ITLmeq_V38yn1kfANGGrZCVl8qZSm8UF8qgyp8bGEWAVKWe1g","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZ2V0QXNzZXJ0aW9uIiwiY2hhbGxlbmdlIjoiTEtYRVhvR0wxWDR5V1ZGZndHTmhkUSIsIm9yaWdpbiI6Imh0dHBzOi8vMTcyLjE2LjIzOC4xMCIsImNpZF9wdWJrZXkiOiJ1bnVzZWQifQ","signatureData":"AQAAALgwRQIgWysRasdeUTrdy_ngxYHrM9FXv4o5_wNUQW0sfs7Hf30CIQDlg9P1NcRY0oAyUmHqwuYsH9W2TSw1msTOLGyWYiSRIg"}',
        );
    }

    public function checkU2fTokens()
    {
        $u2fToken = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->find(1);
        $this->assertNotNull($u2fToken);
    }
}
