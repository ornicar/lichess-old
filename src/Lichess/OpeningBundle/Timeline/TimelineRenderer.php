<?php

namespace Lichess\OpeningBundle\Timeline;

use Symfony\Component\Translation\TranslatorInterface;
use Lichess\OpeningBundle\Document\EntryRepository;
use Lichess\OpeningBundle\Document\Entry;

class TimelineRenderer
{
    public function __construct(EntryRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function render($clientEntryId = null)
    {
        $entries = array_values(iterator_to_array(
            $clientEntryId ? $this->repository->findSince($clientEntryId) : $this->repository->findRecent(10)
        ));

        $escape = function($string) {
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        };

        // php...
        $linkPlayer = function($player) use ($escape)
        {
            if (empty($player['u'])) { return $escape($player['ue']); }
            $url = '/@/' . $player['u'];

            return sprintf('<a class="user_link" href="%s">%s</a>', $url, $escape($player['ue']));
        };

        $rows = array();
        foreach ($entries as $entry) {
            $data = $entry['data'];
            $opponents = implode(" vs ", array_map(function($player) use ($linkPlayer) {
                return $linkPlayer($player);
            }, $data['players']));
            $gameUrl = '/' . $data['id'];
            $clock = isset($data['clock']) ? $data['clock'] : null;

            $rows[] = sprintf(
                '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                sprintf('<a class="watch" href="%s"></a>', $gameUrl),
                $opponents,
                ucfirst($this->translator->trans($data['variant'])),
                $this->translator->trans(empty($data['rated']) ? "Casual" : "Rated"),
                $clock ? sprintf('%d + %d', $clock[0], $clock[1]) : $this->translator->trans("Unlimited")
            );
        }

        $data = array(
            'id' => empty($entries) ? $clientEntryId : $entries[0]['_id'],
            'entries' => array_reverse($rows)
        );

        return $data;
    }
}
