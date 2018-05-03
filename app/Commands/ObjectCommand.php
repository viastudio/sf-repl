<?php
namespace App\Commands;

class ObjectCommand extends AbstractCommand {
    protected $helpText = 'o [object] - Display the details of a Salesforce object';
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
        //
    }
}
