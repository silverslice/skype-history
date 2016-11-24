<?php

/**
 * Skype history search examle.
 * Place your main.db file in 'data' directory.
 */

require __DIR__ . '/../vendor/autoload.php';

use Silverslice\SkypeHistory\Reader;

date_default_timezone_set('Asia/Vladivostok');
header('Content-Type: text/html; charset=utf-8');

$messages = [];
$query = isset($_POST['search_query']) ? $_POST['search_query'] : '';
if ($query) {
    $reader = new Reader('data/main.db');
    $messages = $reader->findInHistory($query);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find in skype history</title>
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
        .date .author {
            color: black;
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
        form {
            margin-bottom: 40px;
        }
    </style>

    <form action="" method="post">
        <input type="text" name="search_query" placeholder="Your query" value="<?php if ($query): ?><?= htmlspecialchars($query) ?><?php endif; ?>"> <input type="submit" value="find"/>
    </form>


    <?php if ($messages): ?>
        <div class="messages">
            <?php $last_date = 0; ?>
            <?php foreach ($messages as $m) : ?>
                <?php $dateTime = date('d.m.Y H:i', $m['timestamp']); ?>
                <?php $startDate = date('d.m.Y', $m['timestamp']); ?>
                <?php $endDate = date('d.m.Y', strtotime('+1 day', $m['timestamp'])); ?>
                <div class="date"><?= $dateTime ?>,
                    <span class="author"><?= $m['from_dispname'] . ' - ' . $m['displayname'] ?></span>
                    <a href="history.php?id=<?= $m['id'] ?>&start=<?= $startDate ?>&end=<?= $endDate ?>">&rarr;</a>
                </div>

                <div class="message <?php if ($m['displayname'] != $m['from_dispname']): ?>author<?php endif; ?>">
                    <?= $m['text'] ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        No messages found
    <?php endif; ?>
</body>
</html>
