<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use MainBundle\Entity\Code;

class MainController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/", name="main_api_index")
     */
    public function indexAction()
    {
        return new Response("Bienvenu sur l'API de Unleased, la super app secrète dont faut pas trop parler.");
    }

    /**
     * @Rest\View()
     * @Rest\Post("/register", name="main_api_register")
     */
    public function RegisterAction(Request $request)
    {
        $postVars = $request->request->all();
        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $mailer = $this->container->get('fos_user.mailer');
        $session = new Session();

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(['username' => $postVars['username']]);

        if ($user !== null)
        {
            return ['error' => "Username already taken"];
        }

        $user = $userManager->findUserBy(['email' => $postVars['email']]);
        if ($user !== null)
        {
            return ['error' => "Email already taken"];
        }

        $em = $this->getDoctrine()->getManager();
        $key = $em->getRepository('MainBundle:Code')->findOneBy(['code' => $postVars['code']]);

        if(!$key || !$key->getEnable()) {
            return ['error' => "Incorrect code"];
        }

        $key->setEnable(false);

        $newCode1 = new Code();
        $newCode2 = new Code();

        $user = $userManager->createUser();
        $user->setUsername($postVars['username']);
        $user->setEmail($postVars['email']);
        $user->setPlainPassword($postVars['password']);
        $user->setEnabled(true);
        $user->addCode($newCode1);
        $user->addCode($newCode2);

        $em->flush();

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $userManager->updateUser($user);

        $data = ['success' => true];

        return $data;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/login/{username}/{password}", name="main_api_login")
     */
    public function LoginAction($username, $password)
    {
        $request = Request::createFromGlobals();

        $user = $this->getDoctrine()
            ->getRepository('UserBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            $user = $this->getDoctrine()
                ->getRepository('UserBundle:User')
                ->findOneBy(['email' => $username]);
            if(!$user){
                return ['error' => 'Username or email not found'];
            }
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $password);

        if (!$isValid) {
            return ['error' => 'Incorrect password'];
        }

        $em = $this->getDoctrine()->getEntityManager();

        $user->setApiKey(hash('sha256', uniqid('', true)));
        $em->flush();

        $data = ['success' => true];

        return $data;
    }
}
