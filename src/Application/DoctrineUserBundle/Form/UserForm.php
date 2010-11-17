<?php

namespace Application\DoctrineUserBundle\Form;

use Bundle\DoctrineUserBundle\Form\UserForm as BaseUserForm;
use Symfony\Component\Form\PasswordField;

class UserForm extends BaseUserForm
{
    public function configure()
    {
        parent::configure();
        $this->remove('email');
        $this->remove('password');
        $this->add(new PasswordField('password'));
    }
}
