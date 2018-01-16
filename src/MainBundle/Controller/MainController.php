<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;

class MainController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/test", name="test")
     */
    public function indexAction()
    {
        $hey = ['oui', 'non'];
        return $hey;
    }
}
