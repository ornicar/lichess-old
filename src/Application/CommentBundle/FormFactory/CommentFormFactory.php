<?php

namespace Application\CommentBundle\FormFactory;

use Symfony\Component\Form\FormContext;
use Symfony\Component\Form\TextField;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\CommentBundle\Form\ValueTransformer\ThreadValueTransformer;
use FOS\CommentBundle\FormFactory\CommentFormFactory as BaseCommentFormFactory;

class CommentFormFactory extends BaseCommentFormFactory
{
    protected $securityContext;

    public function __construct(FormContext $formContext, ThreadValueTransformer $threadValueTransformer, SecurityContext $securityContext, $class, $name)
    {
        $this->securityContext = $securityContext;

        parent::__construct($formContext, $threadValueTransformer, $class, $name);
    }

    public function createForm()
    {
        $form = parent::createForm();
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $form->add(new TextField('authorName'));
        }

        return $form;
    }
}
