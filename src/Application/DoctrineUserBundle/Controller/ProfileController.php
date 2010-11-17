<?php

namespace Application\DoctrineUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as Controller;
use Bundle\DoctrineUserBundle\Model\User;

/**
 * RESTful controller managing user CRUD
 */
class ProfileController extends Controller
{
    /**
     * Show the authenticated user
     **/
    public function showAction()
    {
        return $this->createResponse('yeah');
    }
}
