<?php
namespace App\Exceptions;

class InvalidConfigException extends \Exception {
    protected $message = "No valid configuration was found. Either place your configuration in config.ini inside the project root or specify a config file with the --config option.";
}
