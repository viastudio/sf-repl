<?php
namespace App\Exceptions;

class MissingConfigException extends \Exception {
    protected $message = <<<EOT
The specified configuration seems to be invalid. We expected the following profile variables:

* salesforce_user
* salesforce_pass
* salesforce_consumer_key
* salesforce_consumer_secret
* salesforce_security_token
EOT;
}
