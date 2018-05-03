<?php
namespace App\Commands;

class ObjectCommand extends AbstractCommand {
    protected $helpText = 'o [object] [salesforce id] - Display the details of a Salesforce object';
    protected $titleText = 'Object';

    public function aliases() {
        return [
            'o',
            'object',
        ];
    }

    public function __construct($api = null) {
        $this->api = $api;
    }

    public function run($fields = null, $parent = null) {
        $url = "/services/data/v42.0/sobjects/{$fields[0]}/{$fields[1]}";

        list ($code, $data) = $this->api->get($url);

        return $data;
    }
}
