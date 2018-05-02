<?php
namespace App\Commands;

class UsageCommand {
    const HELP_TEXT = "u [field] - Display Salesforce API usage information (limited to 'field', if specified)";

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
        $type = count($fields) > 1 ? $fields[1] : '';

        return json_encode($api->usage($type), JSON_PRETTY_PRINT);
    }
}
