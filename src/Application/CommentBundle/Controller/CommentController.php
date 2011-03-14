<?php

namespace Application\CommentBundle\Controller;

use FOS\CommentBundle\Controller\CommentController as BaseCommentController;

use FOS\CommentBundle\Form\CommentForm;

class CommentController extends BaseCommentController
{
    protected function onCreateSuccess(CommentForm $form)
    {
        $response = parent::onCreateSuccess($form);

        $this->container->get('lichess_comment.authorname_persistence')->persistComment($form->getData(), $response);

        return $response;
    }
}
