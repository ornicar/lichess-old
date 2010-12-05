<?php

namespace Bundle\LichessBundle\Tests;

use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Generator\StandardPositionGenerator;
use Bundle\LichessBundle\Chess\Generator\Chess960PositionGenerator;
use Bundle\LichessBundle\Chess\Analyser;
use Bundle\LichessBundle\Chess\Manipulator;
use Symfony\Component\DependencyInjection\Container;

abstract class ChessTest extends \PHPUnit_Framework_TestCase
{
    protected function getGenerator()
    {
        $generator = new Generator();

        $container = new Container();
        $container->setParameter('lichess.model.game.class', 'Bundle\LichessBundle\Entity\Game');
        $container->setParameter('lichess.model.player.class', 'Bundle\LichessBundle\Entity\Player');
        $container->setParameter('lichess.model.piece.class', 'Bundle\LichessBundle\Entity\Piece');

        $positionGenerator = new StandardPositionGenerator();
        $positionGenerator->setContainer($container);

        $container->set('lichess_generator_standard', $positionGenerator);

        $positionGenerator = new Chess960PositionGenerator();
        $positionGenerator->setContainer($container);

        $container->set('lichess_generator_960', $positionGenerator);

        $generator->setContainer($container);

        return $generator;
    }

    protected function getManipulator($game, $stack = null)
    {
        $manipulator = new Manipulator($game, $stack ? $stack : new \Bundle\LichessBundle\Entity\Stack());

        $container = new Container();
        $container->setParameter('lichess.model.piece.class', 'Bundle\LichessBundle\Entity\Piece');

        $manipulator->setContainer($container);

        return $manipulator;
    }
}
