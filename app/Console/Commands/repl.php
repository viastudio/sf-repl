<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Salesforce;

class repl extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repl {--config=} {profile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Salesforce REST API exploration command-line tool';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();

        $this->mePath = __DIR__ . '/../../../';
        $this->defaultConfig = "{$this->mePath}/config.ini";
    }

    private function validate() {
        if (!$this->option('config') && !$this->defaultConfigFileExists()) {
            throw new \App\Exceptions\InvalidConfigException();
        }
    }

    private function defaultConfigFileExists() {
        return file_exists($this->defaultConfig);
    }

    private function getConfigFile() {
        return $this->option('config') ? $this->option('config') : $this->defaultConfig;
    }

    private function parseConfig() {
        $vars = [];
        $data = parse_ini_file($this->getConfigFile(), true);

        foreach ($data as $section => $items) {
            $fields = explode(' ', $section);

            if ($fields[0] == 'profile' && count($fields) > 1 && $fields[1] == $this->argument('profile')) {
                $vars = $items;
                break;
            }
        }

        return $vars;
    }

    private function createVars($vars) {
        if (count($vars) <= 0) {
            throw new \App\Exceptions\MissingConfigException();
        }

        foreach ($vars as $name => $val) {
            $this->$name = $val;
        }
    }

    public function handle() {
        try {
            $this->validate();
            $this->createVars($this->parseConfig());
        } catch (\App\Exceptions\InvalidConfigException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function __toString() {
        return print_r([
            'salesforce_user' => $this->salesforce_user,
            'salesforce_pass' => $this->salesforce_pass,
            'salesforce_consumer_key' => $this->salesforce_consumer_key,
            'salesforce_consumer_secret' => $this->salesforce_consumer_secret,
            'salesforce_security_token' => $this->salesforce_security_token,
        ], true);
    }
}
