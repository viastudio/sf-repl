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
    protected $signature = 'repl {--config=} {--e|exec=} {profile}';

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

        //The path to the root of the project
        $this->mePath = __DIR__ . '/../../..';
        $this->defaultConfig = "{$this->mePath}/config.ini";
        $this->historyFile = "{$this->mePath}/.history";

        //The list of command aliases and the command class they correspond to
        $this->commandMap = $this->buildCommandMap();
    }

    /**
     * Save the readline history to a file
     */
    private function writeHistory() {
        readline_write_history($this->historyFile);
    }

    /**
     * Load the readline history from a file
     */
    private function readHistory() {
        readline_read_history($this->historyFile);
    }

    /**
     * Split a repl line entry into pieces
     */
    private function parseCommand($line) {
        //If possible, split off the pipe portion of a command
        //For example:
        //If $line = `o all | jq '.' //we save `| jq '.'` to $pipe
        $pipe = null;
        $pipeIdx = strpos($line, '|');
        if ($pipeIdx !== false) {
            $pipe = substr($line, $pipeIdx);
            $line = substr($line, 0, $pipeIdx - 1);
        }

        //Split the non-pipe portion of the line by spaces
        $fields = explode(' ', $line);

        return [
            'command' => $fields[0],
            'fields' => array_slice($fields, 1),
            'pipe' => $pipe
        ];
    }

    /**
     * Loop over all command class files in app/Commands.
     * Load the aliases for each class and build a map of aliases to class name
     * @return array    alias => Class
     */
    private function buildCommandMap() {
        $map = [];
        $files = glob("{$this->mePath}/app/Commands/*.php");

        foreach ($files as $file) {
            //Ignore the AbstractCommand class
            if (strpos($file, 'AbstractCommand') !== false) {
                continue;
            }

            $className = pathinfo($file, PATHINFO_FILENAME);
            $class = "\\App\\Commands\\$className";

            $cmd = new $class();

            foreach ($cmd->aliases() as $alias) {
                $map[$alias] = $class;
            }
        }

        return $map;
    }

    /**
     * Check if the command exists in the map
     * @return bool
     */
    private function commandExists($command) {
        return isset($this->commandMap[$command]);
    }

    /**
     * Validate the config.ini we're loading
     * @throws \App\Exceptions\InvalidConfigException
     */
    private function validate() {
        //Throw an exception if the default config doesn't exist and they haven't specified an alternate file
        if (!$this->option('config') && !$this->defaultConfigFileExists()) {
            throw new \App\Exceptions\InvalidConfigException();
        }

        //TODO - Other validation?
    }

    /**
     * Check if the default config file exists
     * @return bool
     */
    private function defaultConfigFileExists() {
        return file_exists($this->defaultConfig);
    }

    /**
     * Get the full path to the config file to load.
     * @return string   Either the value of --config or the default config file if --config isn't set
     */
    private function getConfigFile() {
        return $this->option('config') ? $this->option('config') : $this->defaultConfig;
    }

    /**
     * Parse the specified config ini file
     * We assume that we are loading the profile section specified by the `profile` argument.
     * @return array    key/value pairs of the variables in the file
     */
    private function parseConfig() {
        $vars = [];
        $data = parse_ini_file($this->getConfigFile(), true);

        foreach ($data as $section => $items) {
            //We assume profile sections are named "profile <name>"
            //Split the section name by space to check the name
            $fields = explode(' ', $section);

            //See if the profile name matches the one specified
            if ($fields[0] == 'profile' && count($fields) > 1 && $fields[1] == $this->argument('profile')) {
                $vars = $items;
                break;
            }
        }

        return $vars;
    }

    /**
     * Given an array of variables, create class properties of each variable
     * @param array     Array of variables from parseConfig.
     * @throws \App\Exceptions\MissingConfigException
     */
    private function createVars($vars) {
        if (count($vars) <= 0) {
            throw new \App\Exceptions\MissingConfigException();
        }

        foreach ($vars as $name => $val) {
            $this->$name = $val;
        }
    }

    /**
     * Return the salesforce config variables as an array.
     * @return array    An array suitable to pass to the Salesforce.php API class
     */
    private function getOpts() {
        return [
            'salesforce_user' => $this->salesforce_user,
            'salesforce_pass' => $this->salesforce_pass,
            'salesforce_consumer_key' => $this->salesforce_consumer_key,
            'salesforce_consumer_secret' => $this->salesforce_consumer_secret,
            'salesforce_security_token' => $this->salesforce_security_token,
        ];
    }

    /**
     * Create a new Salesforce API instance and authenticate it
     */
    private function initSalesforceApi() {
        $this->api = new Salesforce($this->getOpts());
    }

    /**
     * Check to see if $line is an exit command (including CTRL-D)
     * @param string    A line from readline
     * @return bool
     */
    private function cmdIsExit($line) {
        $exits = ['q', 'exit', 'quit'];

        return in_array($line, $exits) || $line === false;
    }

    private function processCommand($line) {
        //Otherwise add the line to the history
        readline_add_history($line);

        //Parse the command out into pieces
        $ret = $this->parseCommand($line);

        //Make sure it's a valid command
        if (!$this->commandExists($ret['command'])) {
            throw new \Exception("Command '{$ret['command']}' not found");
        }

        //If it's valid, instantiate the command object
        $cmd = new $this->commandMap[$ret['command']]($this->api);

        //And run the command
        $resp = $cmd->run($ret['fields'], $this);

        if (isset($ret['pipe'])) {
            //If there's a pipe command, execute that
            $json = json_encode($resp);

            //There can be issues directly outputting large json blos with echo and piping them to whatever command
            //So we write the json payload to a temp file and cat it to the pipe command
            $temp = tempnam('/tmp', 'sfr_');
            file_put_contents($temp, $json);

            $cmd = "cat $temp {$ret['pipe']}";
            $json = shell_exec($cmd);

            unlink($temp);
        } else {
            //If there's no pipe command, just print the formatted json
            $json = json_encode($resp, JSON_PRETTY_PRINT);
        }

        if (!empty($json)) {
            $this->line($json);
        }
    }

    /**
     * The main repl loop
     * @throws \Exception
     */
    private function repl() {
        $bdone = false;

        while (!$bdone) {
            try {
                //TODO - tab completion? http://php.net/manual/en/function.readline-completion-function.php

                //Read in a command
                $line = readline('>');

                //Check to see if we need to exit
                if ($this->cmdIsExit($line)) {
                    //Write out the readline history on exit
                    $this->writeHistory();
                    $bdone = true;
                    continue;
                }

                //Redraw if there's no input
                if (empty($line)) {
                    readline_redisplay();
                    continue;
                }

                $this->processCommand($line);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    public function exec($line) {
        if (empty($line)) {
            return;
        }

        $this->processCommand($line);
    }

    /**
     * The main command execution function
     * @throws Exception
     */
    public function handle() {
        try {
            $this->validate();
            $this->createVars($this->parseConfig());
            $this->initSalesforceApi();
            $this->readHistory();

            if ($this->option('exec')) {
                $this->exec($this->option('exec'));
            } else {
                $this->repl();
            }
        } catch (\App\Exceptions\InvalidConfigException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function __toString() {
        return print_r($this->getOpts(), true);
    }
}
