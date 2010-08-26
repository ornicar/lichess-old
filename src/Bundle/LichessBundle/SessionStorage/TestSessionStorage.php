<?php

namespace Bundle\LichessBundle\SessionStorage;
use Symfony\Component\HttpFoundation\SessionStorage\SessionStorageInterface;

/**
 * TestSessionStorage.
 */
class TestSessionStorage implements SessionStorageInterface
{
    static protected $sessionIdRegenerated = false;
    static protected $sessionStarted       = false;

    protected $sessionData = array();
    protected $options;

    /**
     * Constructor.
     *
     * @param array $options  An associative array of options
     */
    public function __construct(array $options)
    {
        if (!isset($options['session_path']))
        {
            throw new \InvalidArgumentException('The "session_path" option is mandatory for the TestSessionStorage class.');
        }

        $this->options = array_merge(array(
            'session_id' => 'test'
        ), $options);
    }

    public function start()
    {
        if (self::$sessionStarted) {
            return;
        }

        // use this object as the session handler
        session_set_save_handler(
            array($this, 'sessionOpen'),
            array($this, 'sessionClose'),
            array($this, 'sessionRead'),
            array($this, 'sessionWrite'),
            array($this, 'sessionDestroy'),
            array($this, 'sessionGC')
        );

        self::$sessionStarted = true;
    }

    /**
     * Opens a session.
     *
     * @param  string $path  (ignored)
     * @param  string $name  (ignored)
     *
     * @return boolean true, if the session was opened, otherwise an exception is thrown
     */
    public function sessionOpen($path = null, $name = null)
    {
        return true;
    }

    /**
     * Closes a session.
     *
     * @return boolean true, if the session was closed, otherwise false
     */
    public function sessionClose()
    {
        // do nothing
        return true;
    }

    /**
     * Destroys a session.
     *
     * @param  string $id  A session ID
     *
     * @return bool   true, if the session was destroyed, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be destroyed
     */
    public function sessionDestroy($id)
    {
        $this->sessionWrite($id, null);
        return true;
    }

    /**
     * Cleans up old sessions.
     *
     * @param  int $lifetime  The lifetime of a session
     *
     * @return bool true, if old sessions have been cleaned, otherwise an exception is thrown
     *
     * @throws \RuntimeException If any old sessions cannot be cleaned
     */
    public function sessionGC($lifetime)
    {
        return true;
    }

    /**
     * Reads a session.
     *
     * @param  string $id  A session ID
     *
     * @return string      The session data if the session was read or created, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session cannot be read
     */
    public function sessionRead($id)
    {
        // we read session data from temp file
        $file = $this->options['session_path'].DIRECTORY_SEPARATOR.$id.'.session';
        $this->sessionData = file_exists($file) ? unserialize(file_get_contents($file)) : array();
    }

    /**
     * Writes session data.
     *
     * @param  string $id    A session ID
     * @param  string $data  A serialized chunk of session data
     *
     * @return bool true, if the session was written, otherwise an exception is thrown
     *
     * @throws \RuntimeException If the session data cannot be written
     */
    public function sessionWrite($id, $data)
    {
        var_dump($data);
        $current_umask = umask(0000);
        if (!is_dir($this->options['session_path']))
        {
            mkdir($this->options['session_path'], 0777, true);
        }
        umask($current_umask);
        file_put_contents($this->options['session_path'].DIRECTORY_SEPARATOR.$id.'.session', serialize($this->sessionData));
        return true;
    }

    /**
     * Reads data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function read($key, $default = null)
    {
        return array_key_exists($key, $this->sessionData) ? $this->sessionData[$key] : $default;
    }

    /**
     * Removes data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param  string $key  A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function remove($key)
    {
        $retval = null;

        if (isset($this->sessionData[$key])) {
            $retval = $this->sessionData[$key];
            unset($this->sessionData[$key]);
        }

        return $retval;
    }

    /**
     * Writes data to this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string $key   A unique key identifying your data
     * @param mixed  $data  Data associated with your key
     *
     */
    public function write($key, $data)
    {
        $this->sessionData[$key] = $data;
    }

    /**
     * Regenerates id that represents this storage.
     *
     * @param  boolean $destroy Destroy session when regenerating?
     *
     * @return boolean True if session regenerated, false if error
     *
     */
    public function regenerate($destroy = false)
    {
        if($destroy) {
            $this->sessionData = array();
        }

        return true;
    }
}
