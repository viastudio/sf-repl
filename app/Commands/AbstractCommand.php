<?php
namespace App\Commands;

/**
 * Defines the basic structure of a repl command
 */
abstract class AbstractCommand {
    //Output by the ? repl command
    protected $helpText = '';
    protected $titleText = '';

    //The list of aliases that run this command
    abstract public function aliases();

    //Execute the command
    //Data should always be returned as an array since the repl will turn it into json
    abstract public function run($fields = null, $parent = null);

    public function getHelpText() {
        return $this->helpText;
    }

    public function getTitleText() {
        return $this->titleText;
    }
}
