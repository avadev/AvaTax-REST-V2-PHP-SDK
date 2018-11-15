<?php
namespace Avalara;

use Exception;
use GuzzleHttp\Client;

/**
 * Base AvaTaxClient object that handles connectivity to the AvaTax v2 API server.
 * This class is overridden by the descendant AvaTaxClient which implements all the API methods.
 */
class AvaTaxClientBase
{
    /**
     * @var Client     The Guzzle client to use to connect to AvaTax
     */
    protected $client;

    /**
     * @var array      The authentication credentials to use to connect to AvaTax
     */
    protected $auth;

    /**
     * @var string      The application name as reported to AvaTax
     */
    protected $appName;

    /**
     * @var string      The application version as reported to AvaTax
     */
    protected $appVersion;

    /**
     * @var string      The machine name as reported to AvaTax
     */
    protected $machineName;

    /**
     * @var string      The root URL of the AvaTax environment to contact
     */
    protected $environment;

    /**
     * @var bool        The setting for whether the client should catch exceptions
     */
    protected $catchExceptions;

    /**
     * Construct a new AvaTaxClient
     *
     * @param string $appName      Specify the name of your application here.  Should not contain any semicolons.
     * @param string $appVersion   Specify the version number of your application here.  Should not contain any
     *                             semicolons.
     * @param string $machineName  Specify the machine name of the machine on which this code is executing here.
     *                             Should not contain any semicolons.
     * @param string $environment  Indicates which server to use; acceptable values are "sandbox" or "production", or
     *                             the full URL of your AvaTax instance.
     * @param array  $guzzleParams Extra parameters to pass to the guzzle HTTP client
     *                             (http://docs.guzzlephp.org/en/latest/request-options.html)
     *
     * @throws Exception
     */
    public function __construct($appName, $appVersion, $machineName = "", $environment, $guzzleParams = [])
    {
        // app name and app version are mandatory fields.
        if ($appName == "" || $appName == null || $appVersion == "" || $appVersion == null) {
            throw new Exception('appName and appVersion are manadatory fields!');
        }

        // machine name is nullable, but must be empty string to avoid error when concat in client string.
        if ($machineName == null) {
            $machineName = "";
        }

        // assign client header params to current client object
        $this->appVersion = $appVersion;
        $this->appName = $appName;
        $this->machineName = $machineName;
        $this->environment = $environment;
        $this->catchExceptions = true;

        // Determine startup environment
        $env = 'https://rest.avatax.com';
        if ($environment == "sandbox") {
            $env = 'https://sandbox-rest.avatax.com';
        } else if ((substr($environment, 0, 8) == 'https://') || (substr($environment, 0, 7) == 'http://')) {
            $env = $environment;
        }

        // Prevent overriding the base_uri
        $guzzleParams['base_uri'] = $env;

        // Configure the HTTP client
        $this->client = new Client($guzzleParams);
    }

    /**
     * Configure this client to use the specified username/password security settings
     *
     * @param  string $username The username for your AvaTax user account
     * @param  string $password The password for your AvaTax user account
     *
     * @return AvaTaxClient
     */
    public function withSecurity($username, $password)
    {
        $this->auth = [$username, $password];

        return $this;
    }

    /**
     * Configure this client to use Account ID / License Key security
     *
     * @param  int    $accountId  The account ID for your AvaTax account
     * @param  string $licenseKey The private license key for your AvaTax account
     *
     * @return AvaTaxClient
     */
    public function withLicenseKey($accountId, $licenseKey)
    {
        $this->auth = [$accountId, $licenseKey];

        return $this;
    }

    /**
     * Configure this client to use bearer token
     *
     * @param  string $bearerToken The private bearer token for your AvaTax account
     *
     * @return AvaTaxClient
     */
    public function withBearerToken($bearerToken)
    {
        $this->auth = [$bearerToken];

        return $this;
    }

    /**
     * Configure the client to either catch web request exceptions and return a message or throw the exception
     *
     * @param bool $catchExceptions
     *
     * @return AvaTaxClient
     */
    public function withCatchExceptions($catchExceptions = true)
    {
        $this->catchExceptions = $catchExceptions;

        return $this;
    }

    /**
     * Return the client object, for extended class(es) to retrive the client object
     *
     * @return AvaTaxClient
     */
    public function getClient()
    {
        return $this;
    }

    /**
     * This method is provided to gi
     *
     * @param string $verb
     * @param string $apiUrl
     * @param array  $guzzleParams
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function executeRequest($verb, $apiUrl, $guzzleParams)
    {
        return $this->client->request($verb, $apiUrl, $guzzleParams);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $rawResponse
     * @param bool                                $isJson
     *
     * @return \Psr\Http\Message\StreamInterface|\stdClass
     */
    protected function parseResponse($rawResponse, $isJson)
    {
        $body = $rawResponse->getBody();

        return $isJson ? json_decode($body) : $body;
    }

    /**
     * @param array $guzzleParams
     *
     * @return array
     */
    protected function configureParameters($guzzleParams)
    {
        $guzzleParams['headers'] = array_merge(
            [
                'Accept' => 'application/json',
                'X-Avalara-Client' => "{$this->appName}; {$this->appVersion}; PhpRestClient; 17.5.0-67; {$this->machineName}"
            ],
            isset($guzzleParams['headers']) ? $guzzleParams['headers'] : []
        );

        switch (count($this->auth)) {
            case 1:
                $guzzleParams['headers']['Authorization'] = "Bearer {$this->auth[0]}";
                break;
            // Set authentication on the parameters
            case 2:
            default:
                if (!isset($guzzleParams['auth'])) {
                    $guzzleParams['auth'] = $this->auth;
                }
                break;
        }

        return $guzzleParams;
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Make a single REST call to the AvaTax v2 API server
     *
     * @param string $apiUrl       The relative path of the API on the server
     * @param string $verb         The HTTP verb being used in this request
     * @param array  $guzzleParams The Guzzle parameters for this request, including query string and body parameters
     *
     * @return mixed|string
     * @throws \Exception
     */
    protected function restCall($apiUrl, $verb, $guzzleParams)
    {
        $guzzleParams = $this->configureParameters($guzzleParams);

        // Contact the server
        try {
            // Ignore uncaught Guzzle exception as the interface doesn't extend throwable, even though subclasses do
            /** @noinspection PhpUnhandledExceptionInspection */
            $response = $this->executeRequest($verb, $apiUrl, $guzzleParams);

            return $this->parseResponse($response, $guzzleParams['headers']['Accept'] === 'application/json');
        } catch (\Exception $exception) {
            if ($this->catchExceptions !== true) {
                throw $exception;
            }

            return $exception->getMessage();
        }
    }
}

?>
