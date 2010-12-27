<?php

namespace Application\ForumBundle\Form;

use Bundle\ForumBundle\Form\PostForm as BasePostForm;
use Symfony\Component\Form\TextField;

class PostForm extends BasePostForm
{
    public function configure()
    {
        parent::configure();
        $this->add(new TextField('authorName'));
        $this->add(new TextField('trap'));
    }
}
