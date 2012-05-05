<?php

/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Application\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Security\Core\SecurityContext;

class PostFormType extends AbstractType
{
    protected $securityContext;

    /**
     * Instanciates a new comment type
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Configures a Comment form.
     *
     * @param FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('message', 'textarea');

        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $builder->add('authorName', 'text');
        }
        $builder->add('checkmateId', 'hidden');
        $builder->add('checkmateMove', 'text');
    }

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\ForumBundle\Document\Post',
		);
	}

    public function getName()
    {
        return 'forum_post';
    }
}
