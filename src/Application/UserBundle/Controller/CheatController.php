<?php

namespace Application\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CheatController extends Controller
{
    public function adjustAction($username)
    {
        $this->get('lila')->adjust($username);

        return new RedirectResponse($this->generateUrl("fos_user_user_show", array("username" => $username)));
    }

    public function chatBanAction($username)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByUsername($username);
        if (!$user) {
            throw new NotFoundHttpException('No user with username '.$username);
        }
        $user->toggleChatBan();
        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        return new RedirectResponse($this->generateUrl("fos_user_user_show", array("username" => $username)));
    }
}
