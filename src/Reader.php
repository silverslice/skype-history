<?php

namespace Silverslice\SkypeHistory;

/**
 * Skype history reader
 */

class Reader
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param string $dbFile  Skype main.db file
     */
    public function __construct($dbFile)
    {
        $this->load($dbFile);
    }

    /**
     * Returns all contacts
     *
     * @return array
     */
    public function getContacts()
    {
        $stmt = $this->pdo->query("
            SELECT skypename, fullname, given_displayname, birthday,
                gender, availability, lastonline_timestamp
            FROM contacts
            ORDER BY lastonline_timestamp DESC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Returns contact by skype login
     *
     * @param string login
     * @return array
     */
    public function getContactByLogin($login)
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM contacts
            WHERE skypename = ?
        ");
        $stmt->execute(array($login));

        return $stmt->fetch();
    }

    /**
     * Returns active contacts that is contacts having messages in history
     *
     * @return array
     */
    public function getActiveContacts()
    {
        $stmt = $this->pdo->query("
            SELECT skypename, fullname, given_displayname, birthday,
                gender, availability, lastonline_timestamp
            FROM contacts WHERE skypename IN (
                SELECT DISTINCT author FROM messages
            )
            ORDER BY lastonline_timestamp DESC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Returns message's history for contact for a specified period of time
     *
     * @param string $login    Contact login
     * @param int $startDate   Start date (as timestamp) for history
     * @param int $endDate     End date (as timestamp) for history, default now
     *
     * @return array
     */
    public function getHistory($login, $startDate, $endDate = 0)
    {
        if (!$endDate) {
            $endDate = time();
        }
        $stmt = $this->pdo->prepare("
            SELECT
                conversations.id, conversations.displayname, messages.from_dispname,
                messages.timestamp,
                messages.body_xml as text
            FROM conversations
            INNER JOIN messages on conversations.id = messages.convo_id

            WHERE conversations.identity = ?
            AND (timestamp >= ? AND timestamp <= ?)
            ORDER BY messages.timestamp
        ");
        $stmt->execute(array($login, $startDate, $endDate));

        return $stmt->fetchAll();
    }

    /**
     * Returns messages with specified query in the body
     *
     * @param $query
     * @return array
     */
    public function findInHistory($query)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                conversations.id, conversations.displayname, messages.from_dispname,
                messages.timestamp,
                messages.body_xml as text
            FROM conversations
            INNER JOIN messages on conversations.id = messages.convo_id

            WHERE messages.body_xml LIKE ?
            ORDER BY messages.timestamp
        ");

        $stmt->execute(array("%$query%"));

        return $stmt->fetchAll();
    }

    /**
     * Load skype .db file
     *
     * @param string $dbFile
     *
     * @return bool True if file was loaded, false otherwise
     *
     * @throws \Exception
     */
    protected function load($dbFile)
    {
        if (!file_exists($dbFile)) {
            throw new \Exception('Unable to load .db file');

            return false;
        }

        $this->pdo = new \PDO('sqlite:' . $dbFile);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return true;
    }
}