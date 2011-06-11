<?php

namespace Application\CommentBundle\Controller;

use FOS\CommentBundle\Controller\CommentController as BaseCommentController;
use Symfony\Component\Form\Form;

class CommentController extends BaseCommentController
{
    protected function onCreateSuccess(Form $form)
    {
        $this->container->get('lichess_comment.authorname_persistence')->persistCommentInSession($form->getData());

        $response = parent::onCreateSuccess($form);

        $this->container->get('lichess_comment.authorname_persistence')->persistCommentInCookie($form->getData(), $response);

        return $response;
    }
}
