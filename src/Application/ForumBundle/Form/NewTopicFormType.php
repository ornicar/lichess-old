<?php

namespace Application\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Security\Core\SecurityContext;

class NewTopicFormType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
        $builder->add('subject');
        $builder->add('category');
        $builder->add('firstPost', 'lichess_forum.post');
    }

    public function getName()
    {
        return 'forum_post';
    }
}
