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
$id = isset($_GET['id']) ? $_GET['id'] : '';
$firstActive = isset($_GET['firstActive']) ? 1 : 0;
$startDate = isset($_GET['start']) ? strtotime($_GET['start']) : strtotime('-5 year');
$endDate   = isset($_GET['end'])   ? strtotime($_GET['end']) : time();

$reader = new Reader('data/main.db');
if (!$firstActive) {
    $conversations = $reader->getConversations($startDate);
} else {
    $conversations = $reader->getConversationsOrderedByMessageCount();
}

$messages = $reader->getHistory($id, $startDate, $endDate);
$conversation = $reader->getConversation($id);

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skype history</title>
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
        .message .login {
            color: grey;
            font-size: 0.9em;
        }

        .contacts {
            width: 15%;
            float: left;
            border-right: 1px solid silver;
        }

        .messages {
            width: 80%;
            float: left;
            padding-left: 20px;
        }
    </style>

    <div class="contacts">
        <?php foreach ($conversations as $c): ?>
            <div class="contact"><a href="<?= $url ?>?id=<?= $c['id'] ?>" title="<?= htmlspecialchars($c['identity']) ?>">
                <?php if ($c['id'] == $id): ?>
                    <strong><?= htmlspecialchars($c['displayname']) ?></strong>
                <?php else: ?>
                    <?= htmlspecialchars($c['displayname']) ?>
                <?php endif; ?>
            </a></div>
        <?php endforeach; ?>
    </div>

    <div class="messages">
        <?php if ($messages): ?>
            <h3 style="display: none"><img src="http://api.skype.com/users/<?= $conversation['identity'] ?>/profile/avatar" alt=""> <?= $conversation['identity'] ?>, <?= $conversation['displayname'] ?></h3>
            <?php $last_date = 0; ?>
            <?php foreach ($messages as $m) : ?>
                <?php $date = date('d.m.Y', $m['timestamp']); ?>
                <?php if ($date != $last_date): ?>
                    <div class="date"><?= $date ?></div>
                <?php endif; ?>
                <div class="message <?php if ($m['displayname'] != $m['from_dispname']): ?>author<?php endif; ?>">
                    <span class="login"><?= $m['from_dispname'] ?>: </span>
                    <?= nl2br(strip_tags($m['text'])) ?>
                </div>
                <?php $last_date = $date ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php
        $start = date('d.m.Y', $endDate);
        $end = date('d.m.Y', strtotime('+1 month', $endDate));
        $nextMonthUrl = "$url?id=$id&start=$start&end=$end";

        $endWeek = date('d.m.Y', strtotime('+1 week', $endDate));
        $nextWeekUrl = "$url?id=$id&start=$start&end=$endWeek";

        $endDay = date('d.m.Y', strtotime('+1 day', $endDate));
        $nextDayUrl = "$url?id=$id&start=$start&end=$endDay";
        ?>
        <p><a href="<?= $nextMonthUrl ?>">next month</a>,
            <a href="<?= $nextWeekUrl ?>">next week</a>
            <a href="<?= $nextDayUrl ?>">next day</a>
        </p>
    </div>
</body>
</html>
