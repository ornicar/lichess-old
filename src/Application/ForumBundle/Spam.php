<?php

namespace Application\ForumBundle;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Spam extends Constraint
{
    public function validatedBy()
    {
        return 'forum.spam_validator';
    }
}
