<?php

/**
 * Skype history displaying examle.
 * Place your main.db file in 'data' directory.
 */

require __DIR__ . '/../vendor/autoload.php';

use Silverslice\SkypeHistory\Reader;

date_default_timezone_set('Asia/Vladivostok');
header('Content-Type: text/html; charset=utf-8');

$url = $_SERVER['PHP_SELF'];
$login = isset($_GET['login']) ? $_GET['login'] : '';

$reader = new Reader('data/main.db');
$contacts = $reader->getActiveContacts();
$messages = $reader->getHistory($login, strtotime('-5 year'), time());

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skype</title>
</head>
<body>
    <style>
        body {
            margin: 20px;
        }
        a {
            color: black;
        }
        a:visited {
            color: darkslategrey;
        }
        .date {
            margin: 0 20px;
            color: darkred;
        }
        .contact {
            margin-bottom: 7px;
            font-size: 0.9em;
        }
        .message {
            margin: 30px 40px;
        }
        .message.author {
            color: darkblue;
            margin-left: 30px;
        }

        .contacts {
            width: 15%;
            float: left;
            border-right: 1px solid silver;
        }

        .messages {
            width: 80%;
            float: left;
        }
    </style>

    <div class="contacts">
        <?php foreach ($contacts as $c): ?>
            <div class="contact"><a href="<?= $url ?>?login=<?= $c['skypename'] ?>" title="<?= htmlspecialchars($c['fullname']) ?>">
                    <?php if ($c['skypename'] == $login): ?>
                        <strong><?= htmlspecialchars($c['skypename']) ?></strong>
                    <?php else: ?>
                        <?= htmlspecialchars($c['skypename']) ?>
                    <?php endif; ?>
            </a></div>
        <?php endforeach; ?>
    </div>

    <div class="messages">
        <?php $last_date = 0; ?>
        <?php foreach ($messages as $m) : ?>
            <?php $date = date('d.m.Y', $m['timestamp']); ?>
            <?php if ($date != $last_date): ?>
                <div class="date"><?= $date ?></div>
            <?php endif; ?>
            <div class="message <?php if ($m['displayname'] != $m['from_dispname']): ?>author<?php endif; ?>">
                <?= htmlspecialchars($m['text']) ?>
            </div>
            <?php $last_date = $date ?>
        <?php endforeach; ?>
    </div>
</body>
</html>
