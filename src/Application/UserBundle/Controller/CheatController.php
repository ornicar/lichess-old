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
        $this->get('lila')->chatBan($username);

        return new RedirectResponse($this->generateUrl("fos_user_user_show", array("username" => $username)));
    }
}
