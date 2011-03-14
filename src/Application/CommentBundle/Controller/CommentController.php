<?php

namespace Application\CommentBundle\Controller;

use FOS\CommentBundle\Controller\CommentController as BaseCommentController;

use FOS\CommentBundle\Form\CommentForm;

class CommentController extends BaseCommentController
{
    protected function createForm()
    {
        return $this->container->get('fos_comment.form_factory.comment')->createForm();
    }

    protected function onCreateSuccess(CommentForm $form)
    {
        $this->get('lichess_comment.authorname_persistence')->persistComment($form->getData());

        return parent::onCreateSuccess($form);
    }
}
