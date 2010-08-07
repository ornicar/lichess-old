<?php

namespace Bundle\LichessBundle\SessionStorage;

/**
 * TestSessionStorage.
 */
class TestSessionStorage implements SessionStorageInterface
{
    /**
     * @throws \InvalidArgumentException When "session_path" option is not provided
     */
    public function __construct($options = null)
    {
        if (!array_key_exists('session_path', $options)) {
            throw new \InvalidArgumentException('You must provide the "session_path" option for a TestSessionStorage.');
        }

        $options = array_merge(array(
            'session_id' => null,
        ), $options);

        $this->options = $options;
    }

    /**
     * Starts the session.
     */
    public function start()
    {
        if (self::$sessionStarted) {
            return;
        }

        $this->options['session_id'] = null !== $this->options['session_id'] ? $this->options['session_id'] : (array_key_exists('session_id', $_SERVER) ? $_SERVER['session_id'] : null);

        if ($this->sessionId)
        {
            // we read session data from temp file
            $file = $this->options['session_path'].DIRECTORY_SEPARATOR.$this->sessionId.'.session';
            $this->sessionData = file_exists($file) ? unserialize(file_get_contents($file)) : array();
        }
        else
        {
            $this->sessionId   = md5(uniqid(rand(), true));
            $this->sessionData = array();
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
        // get table/column
        $db_table  = $this->options['db_table'];
        $db_id_col = $this->options['db_id_col'];

        // delete the record associated with this id
        $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_id_col.'= ?';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $id, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }

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
        // get table/column
        $db_table    = $this->options['db_table'];
        $db_time_col = $this->options['db_time_col'];

        // delete the record associated with this id
        $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_time_col.' < '.(time() - $lifetime);

        try {
            $this->db->query($sql);
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }

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
        // get table/columns
        $db_table    = $this->options['db_table'];
        $db_data_col = $this->options['db_data_col'];
        $db_id_col   = $this->options['db_id_col'];
        $db_time_col = $this->options['db_time_col'];

        try {
            $sql = 'SELECT '.$db_data_col.' FROM '.$db_table.' WHERE '.$db_id_col.'=?';

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $id, \PDO::PARAM_STR, 255);

            $stmt->execute();
            // it is recommended to use fetchAll so that PDO can close the DB cursor
            // we anyway expect either no rows, or one row with one column. fetchColumn, seems to be buggy #4777
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            if (count($sessionRows) == 1) {
                return $sessionRows[0][0];
            } else {
                // session does not exist, create it
                $sql = 'INSERT INTO '.$db_table.'('.$db_id_col.', '.$db_data_col.', '.$db_time_col.') VALUES (?, ?, ?)';

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(1, $id, \PDO::PARAM_STR);
                $stmt->bindValue(2, '', \PDO::PARAM_STR);
                $stmt->bindValue(3, time(), \PDO::PARAM_INT);
                $stmt->execute();

                return '';
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }
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
        // get table/column
        $db_table    = $this->options['db_table'];
        $db_data_col = $this->options['db_data_col'];
        $db_id_col   = $this->options['db_id_col'];
        $db_time_col = $this->options['db_time_col'];

        $sql = 'UPDATE '.$db_table.' SET '.$db_data_col.' = ?, '.$db_time_col.' = '.time().' WHERE '.$db_id_col.'= ?';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $data, \PDO::PARAM_STR);
            $stmt->bindParam(2, $id, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
        }

        return true;
    }
}
