<?php

namespace Application\ForumBundle;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SpamValidator extends ConstraintValidator
{
    protected $akismet = $akismet;

    public function __construct($akismet)
    {
        $this->akismet = $akismet;
    }

    public function isValid($post, Constraint $constraint)
    {
        if ($this->akismet->isPostSpam($post)) {

            $this->context->addViolationAtSubPath('message', 'Sorry, but your post looks like spam. If you think it is an error, send me an email.', array(), null);

            return true;
        }

        return false;
    }
}

