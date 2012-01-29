<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Chess\Board;

class Stack
{
    const MAX_EVENTS = 20;

    /**
     * Events in the stack
     *
     * @var array
     */
    protected $events = array();

    public function __construct(array $events = array())
    {
        $this->events = $events;
    }

    public function hasVersion($version)
    {
        return isset($this->events[$version]);
    }

    public function getVersion()
    {
        if ($this->isEmpty()) {
            return 0;
        }

        end($this->events);

        return key($this->events);
    }

    /**
     * Get events
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Get a version event
     *
     * @return array
     **/
    public function getEvent($version)
    {
        return $this->events[$version];
    }

    /**
     * Add events to the stack
     *
     * @return null
     **/
    public function addEvents(array $events)
    {
        foreach($events as $event) {
            $this->addEvent($event);
        }
    }

    public function addEvent(array $event)
    {
        $this->events[] = $event;
    }

    public function reset()
    {
        $this->events = array();
    }

    public function isEmpty()
    {
        return 0 === $this->getNbEvents();
    }

    /**
     * Remove duplicated possible_moves entry,
     * only keep the last one
     *
     * @return void
     */
    public function optimize()
    {
        $previousIndex = null;
        foreach($this->events as $index => $event) {
            if($event['type'] === 'possible_moves') {
                if($previousIndex) {
                    $this->events[$previousIndex] = array('type' => 'possible_moves');
                }
                $previousIndex = $index;
            }
        }
    }

    public function rotate()
    {
        if(count($this->events) > $this->getMaxEvents()) {
            $this->events = array_slice($this->events, -$this->getMaxEvents(), null, true);
        }
    }

    public function getNbEvents()
    {
        return count($this->events);
    }

    public function getMaxEvents()
    {
        return self::MAX_EVENTS;
    }

    public static function compress(Stack $stack = null)
    {
        if (null === $stack || $stack->isEmpty()) return null;
        $stack->optimize();
        $stack->rotate();

        $ktp = function($key) { return Board::keyToPiotr($key); };
        $aktp = function($keys) use ($ktp) { return array_map($ktp, $keys); };
        $goe = function($arr, $key, $default) { return isset($arr[$key]) ? $arr[$key] : $default; };
        $col = function($color) { return substr($color, 0, 1); };
        $es = array();
        foreach ($stack->getEvents() as $index => $event) {
            $type = $event['type'];
            $t = isset(self::$sepyt[$type]) ? self::$sepyt[$type] : $type;
            switch ($type) {
            case "possible_moves":
                $pms = array();
                foreach($goe($event, 'possible_moves', array()) as $from => $tos) {
                    $pms[] = $ktp($from) . implode('', $aktp($tos));
                }
                $data = implode(',', $pms);
                break;
            case "move":
                $data = $ktp($event['from']) . $ktp($event['to']) . $col($event['color']);
                break;
            case "check":
                $data = $ktp($event['key']);
                break;
            case "enpassant":
                $data = $ktp($event['killed']);
                break;
            case "castling":
                $data = implode('', $aktp($event['king'])) . implode('', $aktp($event['rook'])) . $col($event['color']);
                break;
            case "redirect":
                $data = $event['url'];
                break;
            case "message":
                $data = $event['message'][0] . ' ' . str_replace("|", "/", $event['message'][1]);
                break;
            case "promotion":
                $data = $ktp($event['key']) . Piece::classToLetter(ucfirst($event['pieceClass']));
                break;
            default:
                $data = '';
                break;
            }

            $es[] = $index . $t . $data;
        }

        $str = implode('|', $es);

        return $str;
    }

    public static function extract($str)
    {
        if (empty($str)) return new Stack();
        $ptk = function($key) { return Board::piotrToKey($key); };
        $aptk = function($piotrs) use ($ptk) { return array_map($ptk, $piotrs); };
        $col = function($c) { return $c === 'w' ? 'white' : 'black'; };
        $events = array();
        foreach (explode('|', $str) as $e) {
            preg_match('/^(\d+)(\w)(.*)$/', $e, $info);
            $index = $info[1];
            $type = self::$types[$info[2]];
            $data = $info[3];
            switch ($type) {
            case "possible_moves":
                $pms = array();
                if (empty($data)) {
                    $event = array();
                } else {
                    foreach (explode(',', $data) as $pm) {
                        $pms[$ptk($pm{0})] = $aptk(str_split(substr($pm, 1)));
                    }
                    $event = array('possible_moves' => $pms);
                }
                break;
            case "move":
                $event = array('from' => $ptk($data{0}), 'to' => $ptk($data{1}), 'color' => $col($data{2}));
                break;
            case "check":
                $event = array('key' => $ptk($data));
                break;
            case "enpassant":
                $event = array('killed' => $ptk($data));
                break;
            case "castling":
                $event = array('king' => array($ptk($data{0}), $ptk($data{1})), 'rook' => array($ptk($data{2}), $ptk($data{3})), 'color' => $col($data{4}));
                break;
            case "redirect":
                $event = array('url' => $data);
                break;
            case "message":
                $pos = strpos($data, ' ');
                $event = array('message' => array(substr($data, 0, $pos), substr($data, $pos + 1)));
                break;
            case "promotion":
                $event = array('key' => $ptk($data{0}), 'pieceClass' => strtolower(Piece::letterToClass($data{1})));
                break;
            default:
                $event = array();
                break;
            }
            $event['type'] = $type;
            $events[$index] = $event;
        }

        return new Stack($events);
    }

    private static $types = array(
        's' => 'start',
        'p' => 'possible_moves',
        'P' => 'promotion',
        'r' => 'redirect',
        'R' => 'reload_table',
        'm' => 'move',
        'M' => 'message',
        'c' => 'castling',
        'C' => 'check',
        't' => 'threefold_repetition',
        'e' => 'end',
        'E' => 'enpassant'
    );
    private static $sepyt = array(
        'start' => 's',
        'possible_moves' => 'p',
        'promotion' => 'P',
        'redirect' => 'r',
        'reload_table' => 'R',
        'move' => 'm',
        'message' => 'M',
        'castling' => 'c',
        'check' => 'C',
        'threefold_repetition' => 't',
        'end' => 'e',
        'enpassant' => 'E'
    );
}
