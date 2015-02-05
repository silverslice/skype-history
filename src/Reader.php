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
     * Skype main.db file
     *
     * @param string $dbFile
     */
    public function __construct($dbFile)
    {
        $this->load($dbFile);
    }

    /**
     * Return all contacts
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

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Return active contacts that is contacts having messages in history
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

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Return message's history for contact for a specified period of time
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

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

        return true;
    }
}