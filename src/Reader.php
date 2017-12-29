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
     * Returns info about conversation
     *
     * @param int id
     * @return array
     */
    public function getConversation($id)
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM conversations
            WHERE id = ?
        ");
        $stmt->execute(array($id));

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

    public function getConversations($lastActivityTimestamp = 0)
    {
        $stmt = $this->pdo->query("
            SELECT id, identity, displayname
            FROM conversations 
            WHERE last_activity_timestamp > ?
        ");
        $stmt->execute(array($lastActivityTimestamp));

        return $stmt->fetchAll();
    }

    public function getConversationsOrderedByMessageCount()
    {
        $stmt = $this->pdo->query("
            SELECT
                conversations.id, 
                max(conversations.identity) as identity, 
                max(conversations.displayname) as displayname, count(messages.id) as num
            FROM conversations
            INNER JOIN messages on conversations.id = messages.convo_id
            GROUP by conversations.id
            ORDER BY num desc
        ");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Returns history of messages for the conversation id in a specified period of time
     *
     * @param int $conversationId    Conversation Id
     * @param int $startDate   Start date (as timestamp) for history
     * @param int $endDate     End date (as timestamp) for history, default now
     *
     * @return array
     */
    public function getHistory($conversationId, $startDate, $endDate = 0)
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

            WHERE conversations.id = ?
            AND (timestamp >= ? AND timestamp <= ?)
            ORDER BY messages.timestamp
        ");
        $stmt->execute(array($conversationId, $startDate, $endDate));

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