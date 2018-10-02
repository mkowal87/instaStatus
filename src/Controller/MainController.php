<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\InstagramUser;
/**
 * Created by PhpStorm.
 * User: mkowal
 * Date: 30.09.2018
 * Time: 23:24
 */
class MainController extends Controller
{

    /**
     * @Route("/")
     * @Method({"GET", "POST"})
     * @return Response
     */
    public function index(){

        $userId = '12345';
        $userName = 'mr.rewolta';

        $account = $this->getIntagramAccount($userId);
        if (!$account){
            $account = $this->searchInstagramAccount($userName);
        }
        $params = array(
            'name' => "instagram Account",
            'instagramAccount' => $account);

        return $this->render('index.html.twig', $params);
    }


    /**
     * @Route("/instagramUser/save")
     */
    public function saveUser() {
        $entityManager = $this->getDoctrine()->getManager();

        $userName = 'mr.rewolta';
        $userSurName = 'Kowal';
        $userFirstName = 'Maciej';
        $userId = '12345';

        $user = new InstagramUser();
        $user->setUserName($userName);
        $user->setUserFirstName($userFirstName);
        $user->setSurName($userSurName);
        $user->setUserId($userId);

        //Write user to DB
        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('User not found in DB, added for futre parsing');
    }

    protected function getIntagramAccount($userId){

        $findBy = ['userId' => $userId];
        $instagramUser = $this->getDoctrine()->getRepository
        (InstagramUser::class)->findBy($findBy);

        return $instagramUser;
    }

    public function searchInstagramAccount($userName){

        $findBy = ['userName' => $userName];
        $instagramUser = $this->getDoctrine()->getRepository
        (InstagramUser::class)->findBy('%'.$findBy.'%');

        return $instagramUser;
    }

}