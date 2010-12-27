<?php

namespace Application\FOS\UserBundle\Form;

use Bundle\FOS\UserBundle\Form\UserForm as BaseUserForm;
use Symfony\Component\Form\PasswordField;

class UserForm extends BaseUserForm
{
    public function configure()
    {
        parent::configure();
        $this->remove('email');
        $this->remove('plainPassword');
        $this->add(new PasswordField('plainPassword'));
    }
}
