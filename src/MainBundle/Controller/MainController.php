<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * @Rest\Get("/register/{email}/{username}/{password}", name="main_api_register")
     */
    public function RegisterAction($email, $username, $password)
    {
        $request = Request::createFromGlobals();
        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $mailer = $this->container->get('fos_user.mailer');
        $session = new Session();

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(['username' => $username]);

        if ($user !== null)
        {
            return ['error' => "Username already taken"];
        }

        $user = $userManager->findUserBy(['email' => $email]);
        if ($user !== null)
        {
            return ['error' => "Email already taken"];
        }

        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled(true);

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
            return ['error' => 'Bad password'];
        }

        $em = $this->getDoctrine()->getEntityManager();

        $user->setApiKey(hash('sha256', uniqid('', true)));
        $em->flush();

        $data = ['success' => true];

        return $data;
    }
}
