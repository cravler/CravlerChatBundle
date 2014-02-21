<?php

namespace Cravler\ChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class ChatController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        if($session instanceof Session && !$session->getId()) {
            $session->start();
        }

        return $this->render('CravlerChatBundle:Chat:index.html.twig', array());
    }
}
