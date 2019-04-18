<?php

/**
 * Created by PhpStorm.
 * User: mkowal
 * Date: 17.04.2019
 * Time: 20:51
 */

namespace App\Controller;

use App\Entity\InstagramHashes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\InstagramUser;
use App\Helpers\InstagramConnector\Connector;


class HashesController extends Controller
{

    /**
     * @Route("/hashes")
     * @Method({"GET", "POST"})
     * @return Response
     */
    public function index()
    {

        $params = array(
            'name' => "instagram Account",
            'instagramAccount' => false,
            'infoText' => 'Search for baned hashtags',
        );

        return $this->render('hashes.html.twig', $params);
    }

    /**
     * @Route("/searchHashes", name="instagram_hashes_search")
     */
    public function searchHashes()
    {
        $request = Request::createFromGlobals();

        $hashes = $request->query->get('search');

        $hashesArray = explode(',', $hashes);

        if ($hashesArray !== null ){
            foreach ($hashesArray as $hashName) {
                if ($hashData = $this->checkIfExistInDB($hashName)) {

                    var_dump($hashData);

                    return $this->redirect('show/' . $idOrName);
                } else {
                    $this->findHashOnInsta($hashName);
                    $this->saveHashesToDB();
                    $this->findUserOnInstagram($idOrName);
                }
            }
        }

        return $this->render('showUser.html.twig', array
        (
            'name' => 'empty',
            'instagramAccount' => null
        ));
    }

    protected function findHashOnInsta($hashName){

        $instagramConnector = new Connector();
        $hashInfo = $instagramConnector->getInstagramTagInfo($hashName);
var_dump($hashInfo);die();
        return $this;
    }

    protected function saveHashesToDB(){



        return $this;
    }
    protected function checkIfExistInDB($hashName){

        $findBy = ['hashName' => $hashName];
        $hashData = $this->getDoctrine()->getRepository(InstagramHashes::class)->findBy($findBy);
        return $hashData;

    }

}