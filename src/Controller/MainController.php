<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
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
    public function index()
    {

        $userId = '12345';
        $userName = 'mr.rewolta';

        $account = $this->getIntagramAccountById($userId);
        if (!$account) {
            $account = $this->searchInstagramAccountByName($userName);
        }
        $params = array(
            'name' => "instagram Account",
            'instagramAccount' => $account
        );

        return $this->render('index.html.twig', $params);
    }


    /**
     * @Route("/instagramUser/save")
     */
    public function saveUser()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $userName = 'freakery';
        $userLastName = 'Hoffmann';
        $userFirstName = 'Joanna';
        $userId = '54321';

        $user = new InstagramUser();
        $user->setUserName($userName);
        $user->setUserFirstName($userFirstName);
        $user->setUserLastName($userLastName);
        $user->setUserId($userId);

        //Write user to DB
        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('User not found in DB, added for futre parsing');
    }

    protected function getIntagramAccountById($userId)
    {

        $findBy = ['userId' => $userId];
        $instagramUser = $this->getDoctrine()->getRepository
        (InstagramUser::class)->findOneBy($findBy);

        return $instagramUser;
    }

    public function searchInstagramAccountByName($userName)
    {

        $findBy = ['userName' => $userName];
        $instagramUser = $this->getDoctrine()->getRepository
        (InstagramUser::class)->findBy( $findBy);
        return $instagramUser;
    }

    /**
     * @Route("/show/{idOrName}", name="instagram_account_show")
     */
    public function show($idOrName)
    {
        if ($idOrName !== null) {
            if ($users = $this->searchInstagramAccountByName($idOrName)) {
                if (count($users) > 1) {
                    return $this->render('showUsers.html.twig', array
                    (
                        'name' => 'multiple accounts',
                        'instagramAccount' => $users
                    ));
                } else {
                    $user = $users[0];
                    return $this->render('showUser.html.twig', array
                    (
                        'name' => $user->getUserName(),
                        'instagramAccount' => $user
                    ));
                }
            } elseif ($user = $this->getIntagramAccountById($idOrName)) {
                return $this->render('showUser.html.twig', array
                (
                    'name' => $user->getUserName(),
                    'instagramAccount' => $user
                ));
            }
        }
        return $this->render('showUser.html.twig', array
            (
                'name' => 'empty',
                'instagramAccount' => null
            ));

    }

    /**
     * @Route("/searchUser", name="instagram_account_search")
     */
    public function searchUser()
    {
        $request = Request::createFromGlobals();

        $idOrName = $request->query->get('search');

        if ($idOrName !== null ){
            if ($user = $this->checkIfExistInDB($idOrName)) {

                return $this->redirect('show/' . $idOrName);
            }else{
                $this->findUserOnInstagram($idOrName);
            }
        }

        return $this->render('showUser.html.twig', array
        (
            'name' => 'empty',
            'instagramAccount' => null
        ));
    }

    protected function checkIfExistInDB($idOrName){

        if ($users = $this->searchInstagramAccountByName($idOrName)) {
            if (count($users) > 1) {

                return $users;
            } else {
                $user = $users[0];

                return $user;
            }
        } elseif ($user = $this->getIntagramAccountById($idOrName)) {
            return $user;
        }

        return null;
    }

    protected function findUserOnInstagram($idOrName){

        

    }
}