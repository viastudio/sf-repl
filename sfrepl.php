<?php
ini_set('display_errors', true);

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('STDIN')) {
    die("This is a command-line-only script.");
}

use Symfony\Component\Dotenv\Dotenv;
use SFR\Salesforce;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$api = new Salesforce();

$bdone = false;

$commandMap = [
    'u' => 'Usage',
    'usage' => 'Usage'
];

$exits = ['q', 'exit', 'quit'];
while (!$bdone) {
    $line = readline('>');
    if (!empty($line)) {
        readline_add_history($line);
    }

    if (in_array($line, $exits)) {
        $bdone = true;
    } else {
        $fields = explode(' ', $line);
        $cmd = $fields[0];

        $cmdClass = $commandMap[$cmd];

        if (empty($cmdClass)) {
            echo "Command not found\n";
            continue;
        }

        $class = "SFR\\Commands\\$cmdClass";
        $class::run($api, $fields);
    }
}
