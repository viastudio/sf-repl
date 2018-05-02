<?php
namespace App\Commands;

class UsageCommand {
    const HELP_TEXT = 'Display Salesforce API usage information';

    public static function aliases() {
        return [
            'u',
            'usage'
        ];
    }

    public function __construct($api = null) {
        $this->api = $api;
    }

    public static function run($fields = null) {
        //
    }
}
