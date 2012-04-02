<?php

namespace Application\UserBundle;

use Bundle\LichessBundle\Document\GameRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Application\UserBundle\Document\User;
use Lichess\OpeningBundle\Config\GameConfigView;
use Application\UserBundle\Document\UserRepository;
use Bundle\LichessBundle\Document\Game;

class GameExporter
{
    /**
     * Game repository
     *
     * @var GameRepository
     */
    protected $gameRepository;

    /**
     * User repository
     *
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * Url generator
     *
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(GameRepository $gameRepository, UserRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getData(User $user)
    {
        $userId = $user->getId();
        $usernames = $this->userRepository->getUsernamesIndexedById();
        $games = iterator_to_array($this->gameRepository->createRecentByUserQuery($user)
            ->hydrate(false)
            ->select('createdAt', 'status', 'turns', 'v', 'isRated', 'clock.l', 'clock.i', 'players.user.$id', 'players.c', 'players.w', 'players.elo', 'players.eloDiff')
            ->getQuery()->execute());
        $data = array(array('#', 'Date (RFC 2822)', 'Color', 'Opponent', 'Result', 'Status', 'Plies', 'Variant', 'Mode', 'Time control', 'Your Elo', 'Your Elo change', 'Opponent Elo', 'Opponent Elo Change', 'Game url', 'Analysis url', 'PGN url'));
        $it = 0;
        foreach ($games as $gameId => $game) {
            list($player, $opponent) = $this->findPlayerAndOpponent($game, $userId);
            if (!empty($player['isAi'])) {
                $opponentUsername = sprintf('A.I. level %d', $opponent['aiLevel']);
            } elseif(!empty($opponent['user']['$id'])) {
                $opponentUsername = isset($usernames[(string)$opponent['user']['$id']]) ? $usernames[(string)$opponent['user']['$id']] : 'Anonymous';
            } else {
                $opponentUsername = 'Anonymous';
            }
            $clock = isset($game['clock']) ? $game['clock'] : null;
            $data[] = array(
                ++$it,
                date('r', $game['createdAt']->sec),
                $player['c'],
                $opponentUsername,
                $this->getPgnResult($game),
                $this->getStatusMessage($game['status']),
                $game['turns'] -1,
                $this->getVariantName($game['v']),
                !empty($game['isRated']) ? 'rated' : 'casual',
                $clock ? sprintf('%d %d', round($clock['l']/60, 1), $clock['i']) : 'unlimited',
                (int) $player['elo'],
                (int) isset($player['eloDiff']) ? $player['eloDiff'] : 0,
                (int) isset($opponent['elo']) ? $opponent['elo'] : 0,
                (int) isset($opponent['eloDiff']) ? $opponent['eloDiff'] : 0,
                $this->urlGenerator->generate('lichess_game', array('id' => $gameId, 'color' => $player['c']), true),
                $this->urlGenerator->generate('lichess_pgn_viewer', array('id' => $gameId, 'color' => $player['c']), true),
                $this->urlGenerator->generate('lichess_pgn_export', array('id' => $gameId, 'color' => $player['c']), true),
            );
        }

        return $data;
    }

    protected function findPlayerAndOpponent(array $game, $userId)
    {
        foreach ($game['players'] as $p) {
            if (isset($p['user']['$id']) && $userId == $p['user']['$id']->__toString()) {
                $player = $p;
            } else {
                $opponent = $p;
            }
        }
        if (!isset($opponent)) {
            $opponent = $player;
        }

        return array($player, $opponent);
    }

    protected function getPgnResult(array $game)
    {
        foreach ($game['players'] as $p) {
            if ('white' === $p['c']) {
                $white = $p;
            } else {
                $black = $p;
            }
        }

        if($game['status'] >= Game::MATE) {
            if(!empty($white['w'])) {
                return '1-0';
            } elseif(!empty($black['w'])) {
                return '0-1';
            }
            return '1/2-1/2';
        }
        return '*';
    }

    public function getStatusMessage($status)
    {
        switch($status) {
        case Game::ABORTED: $message   = 'Game aborted'; break;
        case Game::MATE: $message      = 'Checkmate'; break;
        case Game::RESIGN: $message    = 'Resign'; break;
        case Game::STALEMATE: $message = 'Stalemate'; break;
        case Game::TIMEOUT: $message   = 'Leave the game'; break;
        case Game::DRAW: $message      = 'Draw'; break;
        case Game::OUTOFTIME: $message = 'Time out'; break;
        case Game::CHEAT: $message     = 'Cheat detected'; break;
        default: $message              = '';
        }
        return $message;
    }

    public function getVariantName($variant)
    {
        static $variants = array(
            Game::VARIANT_STANDARD => 'standard',
            Game::VARIANT_960 => 'chess960'
        );

        return $variants[$variant];
    }
}
