<?php

namespace Bundle\LichessBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Reduce usage of class loader for performance reasons
 */
require_once __DIR__.'/Stack.php';
require_once __DIR__.'/Chess/Square.php';
require_once __DIR__.'/Chess/Board.php';
require_once __DIR__.'/Chess/Synchronizer.php';
require_once __DIR__.'/Notation/PgnDumper.php';
require_once __DIR__.'/Entities/Piece.php';
require_once __DIR__.'/Entities/Piece/Bishop.php';
require_once __DIR__.'/Entities/Piece/King.php';
require_once __DIR__.'/Entities/Piece/Knight.php';
require_once __DIR__.'/Entities/Piece/Pawn.php';
require_once __DIR__.'/Entities/Piece/Queen.php';
require_once __DIR__.'/Entities/Piece/Rook.php';
require_once __DIR__.'/Entities/Player.php';
require_once __DIR__.'/Entities/Game.php';
require_once __DIR__.'/Persistence/MongoDBPersistence.php';
require_once __DIR__.'/I18N/Translator.php';

class LichessBundle extends BaseBundle
{
    public function boot()
    {
        parent::boot();
        $container = $this->container;
        $container->getEventDispatcherService()->connect('core.request', function(Event $event) use ($container) {
            if(HttpKernelInterface::MASTER_REQUEST === $event->getParameter('request_type')) {
                $translator = $container->getLichessTranslatorService();
                $session = $container->getSessionService();
                if(!$session->has('lichess.sound.enabled')) {
                    $session->set('lichess.sound.enabled', true);
                }
                if(!$session->get('lichess.user_id')) {
                    $languages = $container->getRequestService()->getLanguages() ?: array();
                    $bestLocale = $translator->getBestLocale($languages);
                    $session->setLocale($bestLocale);
                    $session->set('lichess.user_id', $this->generateId());
                }
                $translator->setLocale($session->getLocale());
            }
        });
    }

    protected function generateId()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        for ( $i = 0; $i < 6; $i++ ) {
            $id .= $chars[mt_rand( 0, 63 )];
        }

        return $id;
    }
}
