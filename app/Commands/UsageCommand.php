<?php
namespace App\Commands;

class UsageCommand extends AbstractCommand {
    protected $helpText = "u [field] - Display Salesforce API usage information (limited to 'field', if specified)";
    protected $titleText = 'Usage';

    public function aliases() {
        return [
            'u',
            'usage'
        ];
    }

    public function __construct($api = null) {
        $this->api = $api;
    }

    public function run($fields = null, $parent = null) {
        $type = count($fields) > 0 ? $fields[0] : '';

        return json_encode($this->api->usage($type), JSON_PRETTY_PRINT);
    }
}
