<?php

/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Application\CommentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Security\Core\SecurityContext;

class CommentFormType extends AbstractType
{
    protected $securityContext;

    /**
     * Instanciates a new comment type
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getName()
    {
        return 'comment';
    }

    /**
     * Configures a Comment form.
     *
     * @param FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('body', 'textarea');

        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $builder->add('authorName', 'text');
        }
    }
}
