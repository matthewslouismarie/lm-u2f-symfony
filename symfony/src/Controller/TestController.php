<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\U2FToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    /**
     * @Route("/test/{id}", name="test")
     */
    public function index(int $id)
    {
        $u2f_token = $this->getDoctrine()->getRepository(U2FToken::class)->find($id);

        ob_start();
        var_dump($u2f_token);
        return new Response('<pre>'.ob_get_clean().'</pre>');
    }

    /**
     * @Route("/add", name="add")
     */
    public function add()
    {
        $em = $this->getDoctrine()->getEntityManager();
    
        $u2f_token = new U2FToken(
            1
            ,0
            ,'MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc='
            ,'BJgVpRMlaiWxLZ7Mk0J2KixFW7NL+B9UjvMhun1Qa/JNFa5RKjlI70CQv4dGgFatXACd90xpdgGdaENuWZp/Dbs='
            ,'KyK51//LGbm/0hmZ5gxXXwv2ATRrGLTNOmf48TBnQOqYBqtvCA53rjGgkIfFqoiRuC/B0RyD/RTZgrtGLI/2nw=='
        );

        $em->persist($u2f_token);
        $em->flush();

        return new Response('Hello');
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(int $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $u2f_token = $em->getRepository(U2FToken::class)->find($id);
        $em->remove($u2f_token);
        $em->flush();
        return new Response('hello');
    }

    /**
     * @Route("/re", name="re")
     */
    public function re()
    {
        $members_repo = $this->getDoctrine()->getRepository(Member::class);
        $u2f_repo = $this->getDoctrine()->getRepository(U2FToken::class);

        $member = $members_repo->findOneBy(array('username' => 'louis'));
        ob_start();
        var_dump($u2f_repo->getMemberRegistrations($member->getId()));
        $reg = ob_get_clean();
        return new Response('<pre>'.$reg.'</pre>');
    }
}
