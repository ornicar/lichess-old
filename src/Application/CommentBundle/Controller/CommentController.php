<?php

namespace Application\CommentBundle\Controller;

use FOS\CommentBundle\Controller\CommentController as BaseCommentController;

use FOS\CommentBundle\Form\CommentForm;

class CommentController extends BaseCommentController
{
    protected function onCreateSuccess(CommentForm $form)
    {
        $this->container->get('lichess_comment.authorname_persistence')->persistCommentInSession($form->getData());

        $response = parent::onCreateSuccess($form);

        $this->container->get('lichess_comment.authorname_persistence')->persistCommentInCookie($form->getData(), $response);

        return $response;
    }
}
