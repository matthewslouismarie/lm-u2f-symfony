<?php

namespace App\Tks\UpClearance\Authenticator;

use App\Entity\Member;
use App\Form\UsernameAndPasswordType;
use App\FormModel\UsernameAndPasswordSubmission;
use App\Service\AuthRequestService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\HttpFoundation\RequestStack;

class TksUpAuthenticator extends AbstractFormLoginAuthenticator
{
    private $auth;
    private $formFactory;
    private $om;
    private $router;
    private $encoder;

    public function __construct(
        AuthRequestService $auth,
        FormFactoryInterface $formFactory,
        ObjectManager $om,
        RequestStack $rs,
        RouterInterface $router,
        UserPasswordEncoderInterface $encoder)
    {
        $this->auth = $auth;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->rs = $rs;
        $this->router = $router;
        $this->encoder = $encoder;
    }

    public function getCredentials(Request $request)
    {
        $submission = new UsernameAndPasswordSubmission();
        $form = $this
            ->formFactory
            ->create(UsernameAndPasswordType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $submission;
        }
        throw new AuthenticationException();
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $member = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $credentials->getUsername(),
            ));
        return $member;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $credentials->getPassword();
        $isPasswordValid = $this
            ->encoder
            ->isPasswordValid($user, $credentials->getPassword())
        ;
        return $isPasswordValid;
    }

    /**
     * @todo Prevent the redirect_to option from being manipulated.
     */
    protected function getLoginUrl()
    {
        $target = $this
            ->rs
            ->getCurrentRequest()
            ->getUri()
        ;
        return $this->router->generate('tks_0_authenticate', array(
            'redirect_to' => $target,
        ));
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey)
    {
        $url = $request
            ->query
            ->get('redirect_to')
        ;
        return new RedirectResponse($url);
    }

    public function supports(Request $request): bool
    {
        $currentRoute = $request
            ->attributes
            ->get('_route')
        ;
        $isRouteCorrect = 'tks_0_authenticate' === $currentRoute;
        $isMethodCorrect = $request->isMethod('POST');
        return $isRouteCorrect && $isMethodCorrect;
    }
}
