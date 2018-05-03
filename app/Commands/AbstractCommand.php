<?php
namespace App\Commands;

abstract class AbstractCommand {
    protected $helpText = '';
    protected $titleText = '';

    abstract public function aliases();
    abstract public function run($fields = null, $parent = null);

    public function getHelpText() {
        return $this->helpText;
    }

    public function getTitleText() {
        return $this->titleText;
    }
}
