<?php

namespace Bundle\LichessBundle;

use Symfony\Foundation\Bundle\Bundle as BaseBundle;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Components\DependencyInjection\BuilderConfiguration;

/**
 * Reduce usage of class loader for performance reasons
 */
require_once __DIR__.'/Stack.php';
require_once __DIR__.'/Chess/Square.php';
require_once __DIR__.'/Chess/Board.php';
require_once __DIR__.'/Chess/Synchronizer.php';
require_once __DIR__.'/Entities/Piece.php';
require_once __DIR__.'/Entities/Piece/Bishop.php';
require_once __DIR__.'/Entities/Piece/King.php';
require_once __DIR__.'/Entities/Piece/Knight.php';
require_once __DIR__.'/Entities/Piece/Pawn.php';
require_once __DIR__.'/Entities/Piece/Queen.php';
require_once __DIR__.'/Entities/Piece/Rook.php';
require_once __DIR__.'/Entities/Player.php';
require_once __DIR__.'/Entities/Game.php';
require_once __DIR__.'/Entities/Chat/Room.php';
require_once __DIR__.'/Persistence/PersistenceInterface.php';
require_once __DIR__.'/Persistence/FilePersistence.php';
require_once __DIR__.'/Logger/LichessLogger.php';

class LichessBundle extends BaseBundle
{
    public function buildContainer(ContainerInterface $container)
    {
        $configuration = new BuilderConfiguration();
        
        $loader = new XmlFileLoader(__DIR__.'/Resources/config');
        $configuration->merge($loader->load('config.xml'));
        $configuration->merge($loader->load('logger.xml'));

        return $configuration;
    }
}
