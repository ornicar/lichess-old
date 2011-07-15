<?php

namespace Application\UserBundle;

use Bundle\LichessBundle\Document\GameRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Application\UserBundle\Document\User;
use Bundle\LichessBundle\Notation\PgnDumper;
use Lichess\OpeningBundle\Config\GameConfigView;
use Application\UserBundle\Document\UserRepository;

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
     * PGN dumper
     *
     * @var PgnDumper
     */
    protected $pgnDumper;

    /**
     * Url generator
     *
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(GameRepository $gameRepository, UserRepository $userRepository, PgnDumper $pgnDumper, UrlGeneratorInterface $urlGenerator)
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
        $this->pgnDumper = $pgnDumper;
        $this->urlGenerator = $urlGenerator;
    }

    public function getData(User $user)
    {
        $usernames = $this->userRepository->getUsernamesIndexedById();
        //$games = $this->gameRepository->findRecentByUser($user);
        $games = $this->gameRepository->createRecentByUserQuery($user)
            ->getQuery()->execute();
        $data = array(array('#', 'Date (RFC 2822)', 'Color', 'Opponent', 'Result', 'Status', 'Plies', 'Variant', 'Mode', 'Time control', 'Your Elo', 'Your Elo change', 'Opponent Elo', 'Opponent Elo Change', 'Game url', 'Analysis url', 'PGN url'));
        $it = 0;
        foreach ($games as $game) {
            $userIds = $game->getUserIds();
            $player = $game->getPlayerByUser($user);
            $opponent = $player->getOpponent();
            if ($opponent->getIsAi()) {
                $opponentUsername = sprintf('A.I. level %d', $opponent->getAiLevel());
            } elseif($opponent->getElo()) {
                $opponentUserId = (string) array_diff($userIds, array($user->getId()));
                if ($opponentUserId && isset($usernames[$opponentUserId])) {
                    $opponentUsername = $usernames[$opponentUsername];
                } else {
                    $opponentUsername = 'Anonymous';
                }
            } else {
                $opponentUsername = 'Anonymous';
            }
            $clock = $game->getClock();
            $data[] = array(
                ++$it,
                $game->getCreatedAt()->format('r'),
                $player->getColor(),
                $opponentUsername,
                $this->pgnDumper->getPgnResult($game),
                $game->getStatusMessage(),
                $game->getTurns() -1,
                $game->getVariantName(),
                $game->getIsRated() ? 'rated' : 'casual',
                $clock ? sprintf('%d %d', $clock->getLimitInMinutes(), $clock->getIncrement()) : 'unlimited',
                (int) $player->getElo(),
                (int) $player->getEloDiff(),
                (int) $opponent->getElo(),
                (int) $opponent->getEloDiff(),
                $this->urlGenerator->generate('lichess_game', array('id' => $game->getId(), 'color' => $player->getColor()), true),
                $this->urlGenerator->generate('lichess_pgn_viewer', array('id' => $game->getId(), 'color' => $player->getColor()), true),
                $this->urlGenerator->generate('lichess_pgn_export', array('id' => $game->getId(), 'color' => $player->getColor()), true),
            );
        }

        return $data;
    }
}
