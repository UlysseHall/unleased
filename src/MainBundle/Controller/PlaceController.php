<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use MainBundle\Entity\Place;
use UserBundle\Entity\User;
use MainBundle\Entity\Level;

class PlaceController extends Controller
{

  /**
   * @Rest\View()
   * @Rest\Get("/places", name="get_all_places")
   */
    public function getPlacesAction(Request $request,$enabled=true)
    {
      $em = $this->get('doctrine')->getManager();
      $places = $em->getRepository('MainBundle:Place')->findBy(['enabled'=>$enabled]);
      return $places;
  }

  /**
   * @Rest\View()
   * @Rest\Get("/places/id/{id}", name="get_places_by_id")
   */
    public function getPlacesByIdAction($id,$enabled=true)
    {
      $em = $this->get('doctrine')->getManager();
      $places = $em->getRepository('MainBundle:Place')->findBy(['id'=>$id,'enabled'=>$enabled]);
      if (empty($places)) {
        return ['id' => $id.' => non trouvé'];
      }
      return $places;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/places/level/{level_position}", name="get_places_by_level")
     */
      public function getPlacesByLevelAction($level_position,$enabled=true)
      {
        $em = $this->get('doctrine')->getManager();
        
        $places = $em->getRepository('MainBundle:Place')->getPlaceByPosition($level_position, $enabled);
        
        return $places;
      }

      /**
       * @Rest\View()
       * @Rest\Get("/places/user/{user_id}", name="get_places_by_user")
       */
        public function getPlacesByUserAction($user_id,$enabled=true)
        {
          $em = $this->get('doctrine')->getManager();
          $places = $em->getRepository('MainBundle:Place')->findBy(['user'=>$user_id,'enabled'=>$enabled]);
          if (empty($places)) {
            return ['id' => $user_id.' => non trouvé'];
          }
          return $places;
        }

        /**
         * @Rest\View()
         * @Rest\Post("/add_place", name="post_add_a_place")
         */
          public function addPlaceAction(Request $request)
          {

            $postVars = $request->request->all();

            $em = $this->get('doctrine')->getManager();

            $user = $em->getRepository('UserBundle:User')->findOneBy(['id'=>$postVars['user_id']]);

            if(!$user) {
              return ["error"=>"user not found"];
            }

            $level = $em->getRepository('MainBundle:Level')->findOneBy(['id'=>$postVars['level_id']]);

            if(!$level) {
              return ["error"=>"level not found"];
            }

            $place = new Place();

            $place->setName($postVars['name']);
            $place->setDescription($postVars['description']);
            $place->setLevel($level);
            $place->setLatitude($postVars['latitude']);
            $place->setLongitude($postVars['longitude']);
            $user->addPlace($place);

            $em->persist($place);
            $em->flush();

            return ["success"=> true];

          }

}
