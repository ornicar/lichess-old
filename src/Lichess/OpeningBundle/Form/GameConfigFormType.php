<?php

namespace Lichess\OpeningBundle\Form;

use Symfony\Component\Form\AbstractType;
use Lichess\OpeningBundle\Config\GameConfig;
use Symfony\Component\Form\FormBuilder;

class GameConfigFormType extends AbstractType
{
    protected $ratable;
    protected $timeable;
    protected $rangeable;

    public function __construct($ratable, $timeable, $rangeable)
    {
        $this->ratable = $ratable;
        $this->timeable = $timeable;
        $this->rangeable = $rangeable;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('color', 'text');
        $builder->add('variant', 'choice', array(
            'choices' => array(1 => 'Standard', 2 => 'Chess960'),
            'multiple' => false,
            'expanded' => true
        ));
        if ($this->timeable) {
            $builder->add('clock', 'checkbox');
            $builder->add('time', 'number');
            $builder->add('increment', 'number');
        }
        if ($this->ratable) {
            $builder->add('mode', 'choice', array(
                'choices' => array(0 => 'Casual', 1 => 'Rated'),
                'multiple' => false,
                'expanded' => true
            ));
            if ($this->rangeable) {
                $builder->add('eloRange', 'hidden');
            }
        }
    }

    public function getName()
    {
        return 'config';
    }
}
