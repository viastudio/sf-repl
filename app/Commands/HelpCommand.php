<?php
namespace App\Commands;

class HelpCommand extends AbstractCommand {
    protected $helpText = "? - Display help information";
    protected $titleText = 'Help';

    public function aliases() {
        return [
            'h',
            'help',
            '?'
        ];
    }

    public function __construct($api = null) {
        //
    }

    /**
     * Gather up all the Commands help & title text and display it as a table
     */
    public function run($fields = null, $parent = null) {
        $headers = ['Command', 'Description'];
        $commands = [];

        $commandList = array_unique(array_values($parent->commandMap));

        foreach ($commandList as $class) {
            $cmd = new $class();

            $commands[] = [$cmd->getTitleText(), $cmd->getHelpText()];
        }

        $parent->table($headers, $commands);
    }
}
