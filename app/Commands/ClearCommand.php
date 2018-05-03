<?php
namespace App\Commands;

/**
 * Clear the readline history
 */
class ClearCommand extends AbstractCommand {
    protected $helpText = 'clear - Clear the command history';
    protected $titleText = 'Clear';

    public function aliases() {
        return [
            'clear',
        ];
    }

    public function run($fields = null, $parent = null) {
        readline_clear_history();

        return "History cleared";
    }
}
