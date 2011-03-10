<?php

namespace Application\ForumBundle\Form;

use Bundle\ForumBundle\Form\PostForm as BasePostForm;
use Symfony\Component\Form\TextField;

class PostForm extends BasePostForm
{
    public function __construct($name = null, array $options = array())
    {
        $this->addRequiredOption('security_context');

        parent::__construct($name, $options);
    }

    public function configure()
    {
        parent::configure();

        if (!$this->getOption('security_context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->add(new TextField('authorName'));
        }
        $this->add(new TextField('trap'));
    }
}
