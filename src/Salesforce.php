<?php
namespace SFR;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Salesforce {
    public $client = null;
    public $token = null;

    private $baseUrl = "";

    /**
     * @param bool $authSoap - If false, don't authenticate against the SOAP api (default = true)
     */
    public function __construct($authSoap = true) {
        $this->client = new Client();
        $this->auth();

        if ($authSoap) {
            $this->auth_soap();
        }
    }

    /**
     * Get current Daily API request data from Salesforce
     * @return array    The important properties of the response are `Max` and `Remaining`
     */
    public function usage($type = '') {
        list($code, $data) = $this->get('/services/data/v37.0/limits');

        if (empty($type)) {
            return $data;
        }

        return $data[$type];
    }

    /**
     * Delete the sobject pointed to by $url
     * @param string $url
     * @throws \Exception
     */
    public function delete($url) {
        $resp = $this->client->request('DELETE', "{$this->baseUrl}$url", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json'
            ]
        ]);
        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();

        if ($code >= 200 && $code < 300) {
            return [$code, $ret];
        } else {
            throw new \Exception("Unable to delete $url ($code) ($ret)");
        }
    }

    public function update($url, $fields) {
        if (is_array($fields)) {
            $fields = json_encode($fields);
        }

        $resp = $this->client->request('PATCH', "{$this->baseUrl}$url", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json'
            ],
            'body' => $fields
        ]);
        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();
        if ($code >= 200 && $code < 300) {
            return [$code, $ret];
        } else {
            throw new \Exception("Unable to update fields ($code) ($ret)");
        }
    }

    public function create($url, $fields) {
        if (is_array($fields)) {
            $fields = json_encode($fields);
        }

        $resp = $this->client->request('POST', "{$this->baseUrl}$url", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json'
            ],
            'body' => $fields
        ]);
        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();
        if ($code >= 200 && $code < 300) {
            return [$code, $ret];
        } else {
            throw new \Exception("Unable to create record ($code) ($ret)");
        }
    }

    /**
     * Authenticate with Salesforce using the login credentials in .env
     * @return string   The auth token for future requests
     * @throws \Exception
     */
    private function auth() {
        $salesforceUser = getenv('SALESFORCE_USER');
        $salesforcePass = getenv('SALESFORCE_PASS');
        $salesforceKey = getenv('SALESFORCE_CONSUMER_KEY');
        $salesforceSecret = getenv('SALESFORCE_CONSUMER_SECRET');

        $params = [
            'grant_type' => 'password',
            'client_id' => $salesforceKey,
            'client_secret' => $salesforceSecret,
            'username' => $salesforceUser,
            'password' => $salesforcePass
        ];

        $resp = $this->client->request('POST', 'https://login.salesforce.com/services/oauth2/token', [
                'form_params' => $params
                ]);

        $code = $resp->getStatusCode();
        if ($code != 200) {
            throw new \Exception("Unable to authenticate with Salesforce ($code)");
        }

        $rawBody = (string) $resp->getBody();
        $body = json_decode($rawBody, true);
        if (!isset($body['access_token']) || empty($body['access_token'])) {
            throw new \Exception("Invalid access token received ($rawBody)");
        }

        $this->token = $body['access_token'];
        $this->baseUrl = $body['instance_url'];

        return $this->token;
    }

    /**
     * Authorize with the SOAP api and get the current session ID for future SOAP requests
     * @return string $sessionId        (also sets $this->sessionId)
     * @throws \Exception
     */
    private function auth_soap() {
        $salesforceUser = getenv('SALESFORCE_USER');
        $salesforcePass = getenv('SALESFORCE_PASS');
        $salesforceToken = getenv('SALESFORCE_SECURITY_TOKEN');

        $payload = <<<EOT
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
    <env:Body>
        <n1:login xmlns:n1="urn:partner.soap.sforce.com">
            <n1:username>{$salesforceUser}</n1:username>
            <n1:password>{$salesforcePass}{$salesforceToken}</n1:password>
        </n1:login>
    </env:Body>
</env:Envelope>
EOT;

        $resp = $this->client->request('POST', 'https://login.salesforce.com/services/Soap/u/42.0', [
            'body' => $payload,
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF-8',
                'SOAPAction' => 'login'
            ]
        ]);
        $code = $resp->getStatusCode();
        if ($code != 200) {
            throw new \Exception("Unable to authenticate with Salesforce SOAP api($code)");
        }

        $rawBody = (string) $resp->getBody();
        $matches = [];
        if (!preg_match_all("/<sessionId>(.*?)<\/sessionId>/", $rawBody, $matches)) {
            throw new \Exception("Invalid SOAP response ($rawBody)");
        }

        $this->sessionId = $matches[1];

        return $this->sessionId;
    }

    public function query($soql) {
        $url = "/services/data/v37.0/query/?q=" . urlencode($soql);

        return $this->get($url);
    }

    public function get($url, $decode = true) {
        $resp = $this->client->request('GET', "{$this->baseUrl}$url", [
            'headers' => [
            'Authorization' => "Bearer {$this->token}"
            ]
        ]);

        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();

        if ($decode) {
            $ret = json_decode($ret, true);
        }

        return [$code, $ret];
    }
}
