# SkypeHistory

Easy library to view your skype conversation history

## What for?

After update skype in Ubuntu your can find that all your skype conversation history is empty. Your can try to use this
small library to get unavailable history.

## Installation

1. Require library as a dependency using Composer:

    `php composer.phar require silverslice/skype-history`

1. Install SkypeHistory:

    `php composer.phar install`

## Usage

Look at the `examples/history.php` file in the installed library. You can copy it or write your own page to display
history.

All you need is to find skype's **main.db** file in `~/.Skype/your_login/` directory, place it in your project directory
and open page in browser.

## Example of usage

```php
// require composer autoload file
require __DIR__ . '/vendor/autoload.php';

use Silverslice\SkypeHistory\Reader;

// create reader passing the path to main.db skype file
$reader = new Reader('data/main.db');

// get all contacts having messages in history
$contacts = $reader->getActiveContacts();

// get all conversation history for contact with login 'silver_slice' for last 1 year
$messages = $reader->getHistory('silver_slice', strtotime('-1 year'), time());
```