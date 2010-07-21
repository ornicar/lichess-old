<?php

namespace Bundle\LichessBundle;

use Symfony\Framework\Bundle\Bundle as BaseBundle;

use Bundle\LichessBundle\DependencyInjection\LichessExtension;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;
use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\HttpKernel\HttpKernelInterface; 
use Symfony\Components\EventDispatcher\Event;

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
require_once __DIR__.'/Persistence/FilePersistence.php';

class LichessBundle extends BaseBundle
{
    public function buildContainer(ParameterBagInterface $parameterBag)
    {
        ContainerBuilder::registerExtension(new LichessExtension());
    }

    public function boot(ContainerInterface $container)
    {
        parent::boot($container);
        $container->getEventDispatcherService()->connect('core.request', function(Event $event) use ($container) {
            if(HttpKernelInterface::MASTER_REQUEST === $event['request_type']) {
                $session = $container->getSessionService();
                $session->start();
                $translator = $container->getLichessTranslatorService();
                if(!$session->getLocale()) {
                    $request = $container->getRequestService();
                    $locales = array_keys($translator->getLocales());
                    $session->setLocale($request->$this->getPreferredLanguage($locales));
                }
                $translator->setLocale($session->getLocale());
            }
        });
    }
}
