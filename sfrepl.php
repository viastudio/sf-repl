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
