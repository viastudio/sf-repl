<?php
namespace App\Commands;

class ObjectCommand extends AbstractCommand {
    protected $helpText = 'o [all|describe|object] [salesforce id] - Display the details of a Salesforce object';
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

    /**
     * Either return the details of a specific object, a list of all sobjects, or the description of an sobject
     */
    public function run($fields = null, $parent = null) {
        $url = "/services/data/v42.0/sobjects";

        //If the command is 'all', return all sobjects
        if ($fields[0] != 'all') {
            if ($fields[0] == 'describe') {
                $url .= "/{$fields[1]}/describe";
            } else {
                $url .= "/{$fields[0]}/{$fields[1]}";
            }
        }

        list ($code, $data) = $this->api->get($url);

        return $data;
    }
}
