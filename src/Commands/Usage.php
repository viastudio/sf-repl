<?php
namespace SFR\Commands;

class Usage {
    const HELP_TEXT = "u [field] - Display usage information (limited to 'field' if specified)";

    public static function run($api, $fields) {
        $type = count($fields) > 1 ? $fields[1] : '';
        $data = $api->usage($type);

        echo print_r($data, true) . "\n";
    }
}
