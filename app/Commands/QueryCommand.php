<?php
namespace App\Commands;

class QueryCommand extends AbstractCommand {
    protected $helpText = 'q [soql] - Execute an SOQL query and return the result';
    protected $titleText = 'Query';

    public function aliases() {
        return [
            'q',
            'query',
        ];
    }

    public function __construct($api = null) {
        $this->api = $api;
    }

    public function run($fields = null, $parent = null) {
        $soql = implode(' ', $fields);
        list ($code, $data) = $this->api->query($soql);

        return $data;
    }
}
