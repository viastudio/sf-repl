<?php
namespace App\Commands;

class ObjectCommand {
    const HELP_TEXT = 'Display the details of a Salesforce object';

    public static function aliases() {
        return [
            'o',
            'object',
        ];
    }

    public function __construct($api = null) {
        $this->api = $api;
    }

    public static function run($fields = null) {
        //
    }
}
