<?php
namespace Avalara;
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
    private $client;

    /**
     * @var array      The authentication credentials to use to connect to AvaTax
     */
    private $auth;

    /**
     * @var string      The application name as reported to AvaTax
     */
    private $appName;

    /**
     * @var string      The application version as reported to AvaTax
     */
    private $appVersion;

    /**
     * @var string      The machine name as reported to AvaTax
     */
    private $machineName;

    /**
     * @var string      The root URL of the AvaTax environment to contact
     */
    private $environment;

    /**
     * Construct a new AvaTaxClient 
     *
     * @param string $appName      Specify the name of your application here.  Should not contain any semicolons.
     * @param string $appVersion   Specify the version number of your application here.  Should not contain any semicolons.
     * @param string $machineName  Specify the machine name of the machine on which this code is executing here.  Should not contain any semicolons.
     * @param string $environment  Indicates which server to use; acceptable values are "sandbox" or "production", or the full URL of your AvaTax instance.
     * @param array $guzzleParams  Extra parameters to pass to the guzzle HTTP client (http://docs.guzzlephp.org/en/latest/request-options.html)
     */
    public function __construct($appName, $appVersion, $machineName, $environment, $guzzleParams = [])
    {
        $this->appName = $appName;
        $this->appVersion = $appVersion;
        $this->machineName = $machineName;
        $this->environment = $environment;

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
     * @param  string          $username   The username for your AvaTax user account
     * @param  string          $password   The password for your AvaTax user account
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
     * @param  int             $accountId      The account ID for your AvaTax account
     * @param  string          $licenseKey     The private license key for your AvaTax account
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
     * @param  string          $bearerToken     The private bearer token for your AvaTax account
     * @return AvaTaxClient
     */
	public function withBearerToken($bearerToken)
    {
        $this->auth = [$bearerToken];
        return $this;
    }
	
    /**
     * Make a single REST call to the AvaTax v2 API server
     *
     * @param string $apiUrl           The relative path of the API on the server
     * @param string $verb             The HTTP verb being used in this request
     * @param string $guzzleParams     The Guzzle parameters for this request, including query string and body parameters
     */
    protected function restCall($apiUrl, $verb, $guzzleParams)
    {
        // Set authentication on the parameters
        if(count($this->auth) == 2)
		{
			// Set authentication on the parameters
			if (!isset($guzzleParams['auth'])){
				$guzzleParams['auth'] = $this->auth;
			}
			$guzzleParams['headers'] = [
				'Accept' => 'application/json',
				'X-Avalara-Client' => "{$this->appName}; {$this->appVersion}; PhpRestClient; 17.5.0-67; {$this->machineName}"
			];
		}
        else{
			$guzzleParams['headers'] = [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer '.$this->auth[0],
				'X-Avalara-Client' => "{$this->appName}; {$this->appVersion}; PhpRestClient; 17.5.0-67; {$this->machineName}"
			];
		}

        // Contact the server
        try {
            $response = $this->client->request($verb, $apiUrl, $guzzleParams);
            $body = $response->getBody();
            return json_decode($body);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
?>
