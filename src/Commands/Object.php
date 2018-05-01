<?php
namespace SFR\Commands;

class Object {
    const HELP_TEXT = "o <Object> <Id>";

    public static function run($api, $fields) {
        $url = "/services/data/v42.0/sobjects/{$fields[1]}/{$fields[2]}";

        list ($code, $data) = $api->get($url);

        echo print_r($data, true) . "\n";
    }
}
