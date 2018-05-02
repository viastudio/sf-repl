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

    public function handle() {
        try {
            $this->validate();

            $opts = [
                'SALESFORCE_USER' => '',
                'SALESFORCE_PASS' => '',
                'SALESFORCE_CONSUMER_KEY' => '',
                'SALESFORCE_CONSUMER_SECRET' => '',
                'SALESFORCE_SECURITY_TOKEN' => '',
            ];
        } catch (\App\Exceptions\InvalidConfigException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
