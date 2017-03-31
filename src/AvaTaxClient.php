<?php 
namespace Avalara;
/*
 * AvaTax Software Development Kit for PHP
 *
 * (c) 2004-2017 Avalara, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category   AvaTax client libraries
 * @package    Avalara.AvaTaxClient
 * @author     Ted Spence <ted.spence@avalara.com>
 * @author     Bob Maidens <bob.maidens@avalara.com>
 * @copyright  2004-2017 Avalara, Inc.
 * @license    https://www.apache.org/licenses/LICENSE-2.0
 * @version    2.17.4-58
 * @link       https://github.com/avadev/AvaTax-REST-V2-PHP-SDK
 */

use GuzzleHttp\Client;

/*****************************************************************************
 *                              API Section                                  *
 *****************************************************************************/

/**
 * An AvaTaxClient object that handles connectivity to the AvaTax v2 API server.
 */
class AvaTaxClient 
{
    /**
     * @var Client     The Guzzle client to use to connect to AvaTax.
     */
    private $client;

    /**
     * @var array      The authentication credentials to use to connect to AvaTax.
     */
    private $auth;

    /**
     * Construct a new AvaTaxClient 
     *
     * @param string $appName      Specify the name of your application here.  Should not contain any semicolons.
     * @param string $appVersion   Specify the version number of your application here.  Should not contain any semicolons.
     * @param string $machineName  Specify the machine name of the machine on which this code is executing here.  Should not contain any semicolons.
     * @param string $environment  Indicates which server to use; acceptable values are "sandbox" or "production", or the full URL of your AvaTax instance.
     */
    public function __construct($appName, $appVersion, $machineName, $environment)
    {
        // Determine startup environment
        $env = 'https://rest.avatax.com';
        if ($environment == "sandbox") {
            $env = 'https://sandbox-rest.avatax.com';
        } else if ((substr($environment, 0, 8) == 'https://') || (substr($environment, 0, 7) == 'http://')) {
            $env = $environment;
        }

        // Configure the HTTP client
        $this->client = new Client([
            'base_url' => $env
        ]);
        
        // Set client options
        $this->client->setDefaultOption('headers', array(
            'Accept' => 'application/json',
            'X-Avalara-Client' => "{$appName}; {$appVersion}; PhpRestClient; 2.17.4-58; {$machineName}"));
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
     * Retrieve all accounts
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Get multiple account objects.
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     * You may specify one or more of the following values in the `$include` parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Subscriptions
     * * Users
     *  
     * For more information about filtering in REST, please see the documentation at http://developer.avalara.com/avatax/filtering-in-rest/ .
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryAccounts($include, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/accounts";
        $guzzleParams = [
            'query' => ['$include' => $include, '$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new account
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Create a single new account object. 
     * When creating an account object you may attach subscriptions and users as part of the 'Create' call.
     *
     * 
     * @param AccountModel $model The account you wish to create.
     * @return AccountModel
     */
    public function createAccount($model)
    {
        $path = "/api/v2/accounts";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve overrides for this account
     *
     * List all jurisdiction override objects defined for this account.
     * 
     * A Jurisdiction Override is a configuration setting that allows you to select the taxing
     * jurisdiction for a specific address. If you encounter an address that is on the boundary
     * between two different jurisdictions, you can choose to set up a jurisdiction override
     * to switch this address to use different taxing jurisdictions.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listJurisdictionOverridesByAccount($accountId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/accounts/{$accountId}/jurisdictionoverrides";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create one or more overrides
     *
     * Creates one or more jurisdiction override objects for this account.
     * 
     * A Jurisdiction Override is a configuration setting that allows you to select the taxing
     * jurisdiction for a specific address. If you encounter an address that is on the boundary
     * between two different jurisdictions, you can choose to set up a jurisdiction override
     * to switch this address to use different taxing jurisdictions.
     *
     * 
     * @param JurisdictionOverrideModel[] $model The jurisdiction override objects to create
     * @return JurisdictionOverrideModel[]
     */
    public function createJurisdictionOverrides($accountId, $model)
    {
        $path = "/api/v2/accounts/{$accountId}/jurisdictionoverrides";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single override
     *
     * Get the item object identified by this URL.
     * 
     * A Jurisdiction Override is a configuration setting that allows you to select the taxing
     * jurisdiction for a specific address. If you encounter an address that is on the boundary
     * between two different jurisdictions, you can choose to set up a jurisdiction override
     * to switch this address to use different taxing jurisdictions.
     *
     * 
     * @return JurisdictionOverrideModel
     */
    public function getJurisdictionOverride($accountId, $id)
    {
        $path = "/api/v2/accounts/{$accountId}/jurisdictionoverrides/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single jurisdictionoverride
     *
     * Replace the existing jurisdictionoverride object at this URL with an updated object.
     *
     * 
     * @param JurisdictionOverrideModel $model The jurisdictionoverride object you wish to update.
     * @return JurisdictionOverrideModel
     */
    public function updateJurisdictionOverride($accountId, $id, $model)
    {
        $path = "/api/v2/accounts/{$accountId}/jurisdictionoverrides/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single override
     *
     * Marks the item object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteJurisdictionOverride($accountId, $id)
    {
        $path = "/api/v2/accounts/{$accountId}/jurisdictionoverrides/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve subscriptions for this account
     *
     * List all subscription objects attached to this account.
     * A 'subscription' indicates a licensed subscription to a named Avalara service.
     * To request or remove subscriptions, please contact Avalara sales or your customer account manager.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listSubscriptionsByAccount($accountId, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/accounts/{$accountId}/subscriptions";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new subscription
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Create one or more new subscription objects attached to this account.
     * A 'subscription' indicates a licensed subscription to a named Avalara service.
     * To request or remove subscriptions, please contact Avalara sales or your customer account manager.
     *
     * 
     * @param SubscriptionModel[] $model The subscription you wish to create.
     * @return SubscriptionModel[]
     */
    public function createSubscriptions($accountId, $model)
    {
        $path = "/api/v2/accounts/{$accountId}/subscriptions";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single subscription
     *
     * Get the subscription object identified by this URL.
     * A 'subscription' indicates a licensed subscription to a named Avalara service.
     * To request or remove subscriptions, please contact Avalara sales or your customer account manager.
     *
     * 
     * @return SubscriptionModel
     */
    public function getSubscription($accountId, $id)
    {
        $path = "/api/v2/accounts/{$accountId}/subscriptions/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single subscription
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Replace the existing subscription object at this URL with an updated object.
     * A 'subscription' indicates a licensed subscription to a named Avalara service.
     * To request or remove subscriptions, please contact Avalara sales or your customer account manager.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param SubscriptionModel $model The subscription you wish to update.
     * @return SubscriptionModel
     */
    public function updateSubscription($accountId, $id, $model)
    {
        $path = "/api/v2/accounts/{$accountId}/subscriptions/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single subscription
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Mark the existing account identified by this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteSubscription($accountId, $id)
    {
        $path = "/api/v2/accounts/{$accountId}/subscriptions/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve users for this account
     *
     * List all user objects attached to this account.
     * A user represents one person with access privileges to make API calls and work with a specific account.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listUsersByAccount($accountId, $include, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/accounts/{$accountId}/users";
        $guzzleParams = [
            'query' => ['$include' => $include, '$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create new users
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Create one or more new user objects attached to this account.
     * A user represents one person with access privileges to make API calls and work with a specific account.
     *
     * 
     * @param UserModel[] $model The user or array of users you wish to create.
     * @return UserModel[]
     */
    public function createUsers($accountId, $model)
    {
        $path = "/api/v2/accounts/{$accountId}/users";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single user
     *
     * Get the user object identified by this URL.
     * A user represents one person with access privileges to make API calls and work with a specific account.
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @return UserModel
     */
    public function getUser($id, $accountId, $include)
    {
        $path = "/api/v2/accounts/{$accountId}/users/{$id}";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single user
     *
     * Replace the existing user object at this URL with an updated object.
     * A user represents one person with access privileges to make API calls and work with a specific account.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param UserModel $model The user object you wish to update.
     * @return UserModel
     */
    public function updateUser($id, $accountId, $model)
    {
        $path = "/api/v2/accounts/{$accountId}/users/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single user
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Mark the user object identified by this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteUser($id, $accountId)
    {
        $path = "/api/v2/accounts/{$accountId}/users/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve all entitlements for a single user
     *
     * Return a list of all entitlements to which this user has rights to access.
     * Entitlements are a list of specified API calls the user is permitted to make, a list of identifier numbers for companies the user is 
     * allowed to use, and an access level identifier that indicates what types of access roles the user is allowed to use.
     * This API call is intended to provide a validation endpoint to determine, before making an API call, whether this call is likely to succeed.
     * For example, if user 567 within account 999 is attempting to create a new child company underneath company 12345, you could preview the user's
     * entitlements and predict whether this call would succeed:
     *  
     * * Retrieve entitlements by calling '/api/v2/accounts/999/users/567/entitlements' . If the call fails, you do not have accurate 
     *  credentials for this user.
     * * If the 'accessLevel' field within entitlements is 'None', the call will fail.
     * * If the 'accessLevel' field within entitlements is 'SingleCompany' or 'SingleAccount', the call will fail if the companies
     *  table does not contain the ID number 12345.
     * * If the 'permissions' array within entitlements does not contain 'AccountSvc.CompanySave', the call will fail.
     *  
     * For a full list of defined permissions, please use '/api/v2/definitions/permissions' .
     *
     * 
     * @return UserEntitlementModel
     */
    public function getUserEntitlements($id, $accountId)
    {
        $path = "/api/v2/accounts/{$accountId}/users/{$id}/entitlements";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single account
     *
     * Get the account object identified by this URL.
     * You may use the '$include' parameter to fetch additional nested data:
     * 
     * * Subscriptions
     * * Users
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @return AccountModel
     */
    public function getAccount($id, $include)
    {
        $path = "/api/v2/accounts/{$id}";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single account
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Replace an existing account object with an updated account object.
     *
     * 
     * @param AccountModel $model The account object you wish to update.
     * @return AccountModel
     */
    public function updateAccount($id, $model)
    {
        $path = "/api/v2/accounts/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single account
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Delete an account.
     * Deleting an account will delete all companies and all account level users attached to this account.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteAccount($id)
    {
        $path = "/api/v2/accounts/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Reset this account's license key
     *
     * Resets the existing license key for this account to a new key.
     * To reset your account, you must specify the ID of the account you wish to reset and confirm the action.
     * Resetting a license key cannot be undone. Any previous license keys will immediately cease to work when a new key is created.
     *
     * 
     * @param ResetLicenseKeyModel $model A request confirming that you wish to reset the license key of this account.
     * @return LicenseKeyModel
     */
    public function accountResetLicenseKey($id, $model)
    {
        $path = "/api/v2/accounts/{$id}/resetlicensekey";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * FREE API - Request a free trial of AvaTax
     *
     * Call this API to obtain a free AvaTax sandbox account.
     * 
     * This API is free to use. No authentication credentials are required to call this API.
     * The account will grant a full trial version of AvaTax (e.g. AvaTaxPro) for a limited period of time.
     * After this introductory period, you may continue to use the free TaxRates API.
     * 
     * Limitations on free trial accounts:
     *  
     * * Only one free trial per company.
     * * The free trial account does not expire.
     * * Includes a limited time free trial of AvaTaxPro; after that date, the free TaxRates API will continue to work.
     * * Each free trial account must have its own valid email address.
     *
     * 
     * @param FreeTrialRequestModel $model Required information to provision a free trial account.
     * @return NewAccountModel
     */
    public function requestFreeTrial($model)
    {
        $path = "/api/v2/accounts/freetrials/request";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Request a new Avalara account
     *
     * This API is for use by partner onboarding services customers only.
     * Calling this API creates an account with the specified product subscriptions, but does not configure billing.
     * The customer will receive information from Avalara about how to configure billing for their account.
     * You should call this API when a customer has requested to begin using Avalara services.
     *
     * 
     * @param NewAccountRequestModel $model Information about the account you wish to create and the selected product offerings.
     * @return NewAccountModel
     */
    public function requestNewAccount($model)
    {
        $path = "/api/v2/accounts/request";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve geolocation information for a specified address
     *
     * Resolve an address against Avalara's address-validation system. If the address can be resolved, this API 
     * provides the latitude and longitude of the resolved location. The value 'resolutionQuality' can be used 
     * to identify how closely this address can be located. If the address cannot be clearly located, use the 
     * 'messages' structure to learn more about problems with this address.
     * This is the same API as the POST /api/v2/addresses/resolve endpoint.
     * Both verbs are supported to provide for flexible implementation.
     *
     * 
     * @param string $line1 Line 1
     * @param string $line2 Line 2
     * @param string $line3 Line 3
     * @param string $city City
     * @param string $region State / Province / Region
     * @param string $postalCode Postal Code / Zip Code
     * @param string $country Two character ISO 3166 Country Code (see /api/v2/definitions/countries for a full list)
     * @param string $textCase selectable text case for address validation (See TextCase::* for a list of allowable values)
     * @param float $latitude Geospatial latitude measurement
     * @param float $longitude Geospatial longitude measurement
     * @return AddressResolutionModel
     */
    public function resolveAddress($line1, $line2, $line3, $city, $region, $postalCode, $country, $textCase, $latitude, $longitude)
    {
        $path = "/api/v2/addresses/resolve";
        $guzzleParams = [
            'query' => ['line1' => $line1, 'line2' => $line2, 'line3' => $line3, 'city' => $city, 'region' => $region, 'postalCode' => $postalCode, 'country' => $country, 'textCase' => $textCase, 'latitude' => $latitude, 'longitude' => $longitude],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve geolocation information for a specified address
     *
     * Resolve an address against Avalara's address-validation system. If the address can be resolved, this API 
     * provides the latitude and longitude of the resolved location. The value 'resolutionQuality' can be used 
     * to identify how closely this address can be located. If the address cannot be clearly located, use the 
     * 'messages' structure to learn more about problems with this address.
     * This is the same API as the GET /api/v2/addresses/resolve endpoint.
     * Both verbs are supported to provide for flexible implementation.
     *
     * 
     * @param AddressValidationInfo $model The address to resolve
     * @return AddressResolutionModel
     */
    public function resolveAddressPost($model)
    {
        $path = "/api/v2/addresses/resolve";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all batches
     *
     * Get multiple batch objects across all companies.
     * A batch object is a large collection of API calls stored in a compact file.
     * When you create a batch, it is added to the AvaTax Batch Queue and will be processed in the order it was received.
     * You may fetch a batch to check on its status and retrieve the results of the batch operation.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryBatches($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/batches";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all companies
     *
     * Get multiple company objects.
     * A 'company' represents a single corporation or individual that is registered to handle transactional taxes.
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     * You may specify one or more of the following values in the `$include` parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Contacts
     * * Items
     * * Locations
     * * Nexus
     * * Settings
     * * TaxCodes
     * * TaxRules
     * * UPC
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryCompanies($include, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies";
        $guzzleParams = [
            'query' => ['$include' => $include, '$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create new companies
     *
     * Create one or more new company objects.
     * A 'company' represents a single corporation or individual that is registered to handle transactional taxes.
     * You may attach nested data objects such as contacts, locations, and nexus with this CREATE call, and those objects will be created with the company.
     *
     * 
     * @param CompanyModel[] $model Either a single company object or an array of companies to create
     * @return CompanyModel[]
     */
    public function createCompanies($model)
    {
        $path = "/api/v2/companies";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all transactions
     *
     * List all transactions attached to this company.
     * This endpoint is limited to returning 1,000 transactions at a time maximum.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     * You may specify one or more of the following values in the `$include` parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Lines
     * * Details (implies lines)
     * * Summary (implies details)
     * * Addresses
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listTransactionsByCompany($companyCode, $include, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions";
        $guzzleParams = [
            'query' => ['$include' => $include, '$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single transaction by code
     *
     * Get the current transaction identified by this URL.
     * If this transaction was adjusted, the return value of this API will be the current transaction with this code, and previous revisions of
     * the transaction will be attached to the 'history' data field.
     * You may specify one or more of the following values in the '$include' parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Lines
     * * Details (implies lines)
     * * Summary (implies details)
     * * Addresses
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @return TransactionModel
     */
    public function getTransactionByCode($companyCode, $transactionCode, $include)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Correct a previously created transaction
     *
     * Replaces the current transaction uniquely identified by this URL with a new transaction.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * When you adjust a committed transaction, the original transaction will be updated with the status code 'Adjusted', and
     * both revisions will be available for retrieval based on their code and ID numbers.
     * Only transactions in 'Committed' status are reported by Avalara Managed Returns.
     * Transactions that have been previously reported to a tax authority by Avalara Managed Returns are no longer available for adjustments.
     *
     * 
     * @param AdjustTransactionModel $model The adjustment you wish to make
     * @return TransactionModel
     */
    public function adjustTransaction($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/adjust";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Get audit information about a transaction
     *
     * Retrieve audit information about a transaction stored in AvaTax.
     *  
     * The 'AuditTransaction' endpoint retrieves audit information related to a specific transaction. This audit 
     * information includes the following:
     * 
     * * The `CompanyId` of the company that created the transaction
     * * The server timestamp representing the exact server time when the transaction was created
     * * The server duration - how long it took to process this transaction
     * * Whether exact API call details were logged
     * * A reconstructed API call showing what the original CreateTransaction call looked like
     * 
     * This API can be used to examine information about a previously created transaction.
     * 
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     *
     * 
     * @return AuditTransactionModel
     */
    public function auditTransaction($companyCode, $transactionCode)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/audit";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Change a transaction's code
     *
     * Renames a transaction uniquely identified by this URL by changing its code to a new code.
     * After this API call succeeds, the transaction will have a new URL matching its new code.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     *
     * 
     * @param ChangeTransactionCodeModel $model The code change request you wish to execute
     * @return TransactionModel
     */
    public function changeTransactionCode($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/changecode";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Commit a transaction for reporting
     *
     * Marks a transaction by changing its status to 'Committed'.
     * Transactions that are committed are available to be reported to a tax authority by Avalara Managed Returns.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * Any changes made to a committed transaction will generate a transaction history.
     *
     * 
     * @param CommitTransactionModel $model The commit request you wish to execute
     * @return TransactionModel
     */
    public function commitTransaction($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/commit";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Lock a single transaction
     *
     * Lock a transaction uniquely identified by this URL. 
     * 
     * This API is mainly used for connector developer to simulate what happens when Returns product locks a document.
     * After this API call succeeds, the document will be locked and can't be voided or adjusted.
     * 
     * This API is only available to customers in Sandbox with AvaTaxPro subscription. On production servers, this API is available by invitation only.
     * 
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     *
     * 
     * @param LockTransactionModel $model The lock request you wish to execute
     * @return TransactionModel
     */
    public function lockTransaction($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/lock";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Create a refund for a transaction
     *
     * Create a refund for a transaction.
     * 
     * The `RefundTransaction` API allows you to quickly and easily create a `ReturnInvoice` representing a refund
     * for a previously created `SalesInvoice` transaction. You can choose to create a full or partial refund, and
     * specify individual line items from the original sale for refund.
     * 
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * You may specify one or more of the following values in the '$include' parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Lines
     * * Details (implies lines)
     * * Summary (implies details)
     * * Addresses
     *  
     * If you don't specify '$include' parameter, it will include both details and addresses.
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param RefundTransactionModel $model Information about the refund to create
     * @return TransactionModel
     */
    public function refundTransaction($companyCode, $transactionCode, $include, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/refund";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Perform multiple actions on a transaction
     *
     * Performs the same functions as /verify, /changecode, and /commit. You may specify one or many actions in each call to this endpoint.
     *
     * 
     * @param SettleTransactionModel $model The settle request containing the actions you wish to execute
     * @return TransactionModel
     */
    public function settleTransaction($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/settle";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single transaction by code
     *
     * Get the current transaction identified by this URL.
     * If this transaction was adjusted, the return value of this API will be the current transaction with this code, and previous revisions of
     * the transaction will be attached to the 'history' data field.
     * You may specify one or more of the following values in the '$include' parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Lines
     * * Details (implies lines)
     * * Summary (implies details)
     * * Addresses
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @return TransactionModel
     */
    public function getTransactionByCodeAndType($companyCode, $transactionCode, $documentType, $include)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/types/{$documentType}";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Get audit information about a transaction
     *
     * Retrieve audit information about a transaction stored in AvaTax.
     *  
     * The 'AuditTransaction' endpoint retrieves audit information related to a specific transaction. This audit 
     * information includes the following:
     * 
     * * The `CompanyId` of the company that created the transaction
     * * The server timestamp representing the exact server time when the transaction was created
     * * The server duration - how long it took to process this transaction
     * * Whether exact API call details were logged
     * * A reconstructed API call showing what the original CreateTransaction call looked like
     * 
     * This API can be used to examine information about a previously created transaction.
     * 
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     *
     * 
     * @return AuditTransactionModel
     */
    public function auditTransactionWithType($companyCode, $transactionCode, $documentType)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/types/{$documentType}/audit";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Verify a transaction
     *
     * Verifies that the transaction uniquely identified by this URL matches certain expected values.
     * If the transaction does not match these expected values, this API will return an error code indicating which value did not match.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     *
     * 
     * @param VerifyTransactionModel $model The settle request you wish to execute
     * @return TransactionModel
     */
    public function verifyTransaction($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/verify";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Void a transaction
     *
     * Voids the current transaction uniquely identified by this URL.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * When you void a transaction, that transaction's status is recorded as 'DocVoided'.
     * Transactions that have been previously reported to a tax authority by Avalara Managed Returns are no longer available to be voided.
     *
     * 
     * @param VoidTransactionModel $model The void request you wish to execute
     * @return TransactionModel
     */
    public function voidTransaction($companyCode, $transactionCode, $model)
    {
        $path = "/api/v2/companies/{$companyCode}/transactions/{$transactionCode}/void";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all batches for this company
     *
     * List all batch objects attached to the specified company.
     * A batch object is a large collection of API calls stored in a compact file.
     * When you create a batch, it is added to the AvaTax Batch Queue and will be processed in the order it was received.
     * You may fetch a batch to check on its status and retrieve the results of the batch operation.
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listBatchesByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/batches";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new batch
     *
     * Create one or more new batch objects attached to this company.
     * A batch object is a large collection of API calls stored in a compact file.
     * When you create a batch, it is added to the AvaTax Batch Queue and will be processed in the order it was received.
     * You may fetch a batch to check on its status and retrieve the results of the batch operation.
     * Each batch object may have one or more file objects attached.
     *
     * 
     * @param BatchModel[] $model The batch you wish to create.
     * @return BatchModel[]
     */
    public function createBatches($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/batches";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Download a single batch file
     *
     * Download a single batch file identified by this URL.
     *
     * 
     * @return FileResult
     */
    public function downloadBatch($companyId, $batchId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/batches/{$batchId}/files/{$id}/attachment";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single batch
     *
     * Get the batch object identified by this URL.
     * A batch object is a large collection of API calls stored in a compact file.
     * When you create a batch, it is added to the AvaTax Batch Queue and will be processed in the order it was received.
     * You may fetch a batch to check on its status and retrieve the results of the batch operation.
     *
     * 
     * @return BatchModel
     */
    public function getBatch($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/batches/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Delete a single batch
     *
     * Mark the existing batch object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteBatch($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/batches/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve contacts for this company
     *
     * List all contact objects assigned to this company.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listContactsByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/contacts";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new contact
     *
     * Create one or more new contact objects.
     * A 'contact' is a person associated with a company who is designated to handle certain responsibilities of
     * a tax collecting and filing entity.
     *
     * 
     * @param ContactModel[] $model The contacts you wish to create.
     * @return ContactModel[]
     */
    public function createContacts($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/contacts";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single contact
     *
     * Get the contact object identified by this URL.
     * A 'contact' is a person associated with a company who is designated to handle certain responsibilities of
     * a tax collecting and filing entity.
     *
     * 
     * @return ContactModel
     */
    public function getContact($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/contacts/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single contact
     *
     * Replace the existing contact object at this URL with an updated object.
     * A 'contact' is a person associated with a company who is designated to handle certain responsibilities of
     * a tax collecting and filing entity.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param ContactModel $model The contact you wish to update.
     * @return ContactModel
     */
    public function updateContact($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/contacts/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single contact
     *
     * Mark the existing contact object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteContact($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/contacts/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve all filing calendars for this company
     *
     * This API is available by invitation only.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function companiesByCompanyIdFilingcalendarsGet($companyId, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single filing calendar
     *
     * This API is available by invitation only.
     *
     * 
     * @return FilingCalendarModel
     */
    public function companiesByCompanyIdFilingcalendarsByIdGet($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Returns a list of options for expiring a filing calendar
     *
     * This API is available by invitation only.
     *
     * 
     * @return CycleExpireModel
     */
    public function cycleSafeExpiration($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/{$id}/cancel/options";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new filing request to cancel a filing calendar
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @param FilingRequestModel[] $model The cancellation request for this filing calendar
     * @return FilingRequestModel
     */
    public function filingRequestsNewCancel($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/{$id}/cancel/request";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Indicates when changes are allowed to be made to a filing calendar.
     *
     * This API is available by invitation only.
     *
     * 
     * @param FilingCalendarEditModel[] $model A list of filing calendar edits to be made
     * @return CycleEditOptionModel
     */
    public function cycleSafeEdit($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/{$id}/edit/options";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Create a new filing request to edit a filing calendar
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @param FilingRequestModel[] $model A list of filing calendar edits to be made
     * @return FilingRequestModel
     */
    public function filingRequestsNewEdit($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/{$id}/edit/request";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Returns a list of options for adding the specified form.
     *
     * This API is available by invitation only.
     *
     * 
     * @param string $formCode The unique code of the form
     * @return CycleAddOptionModel[]
     */
    public function cycleSafeAdd($companyId, $formCode)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/add/options";
        $guzzleParams = [
            'query' => ['formCode' => $formCode],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new filing request to create a filing calendar
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @param FilingRequestModel[] $model Information about the proposed new filing calendar
     * @return FilingRequestModel
     */
    public function filingRequestsAdd($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filingcalendars/add/request";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all filing requests for this company
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function companiesByCompanyIdFilingrequestsGet($companyId, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/filingrequests";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single filing request
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @return FilingRequestModel
     */
    public function filingRequests($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filingrequests/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Edit existing Filing Request
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @param FilingRequestModel $model A list of filing calendar edits to be made
     * @return FilingRequestModel
     */
    public function filingRequestsUpdate($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filingrequests/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Approve existing Filing Request
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     * The filing request must be in the "ChangeRequest" status to be approved.
     *
     * 
     * @return FilingRequestModel
     */
    public function filingRequestsApprove($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filingrequests/{$id}/approve";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Cancel existing Filing Request
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     *
     * 
     * @return FilingRequestModel
     */
    public function filingRequestsCancel($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filingrequests/{$id}/cancel";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single attachment for a filing
     *
     * This API is available by invitation only.
     *
     * 
     * @return FileResult
     */
    public function getFilingAttachment($companyId, $filingId)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$filingId}/attachment";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve worksheet checkup report for company and filing period.
     *
     * This API is available by invitation only.
     *
     * 
     * @return FilingsCheckupModel
     */
    public function filingsCheckupReport($worksheetId, $companyId)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$worksheetId}/checkup";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a list of filings for the specified company in the year and month of a given filing period.
     *
     * This API is available by invitation only.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @return FetchResult
     */
    public function getFilings($companyId, $year, $month)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a list of filings for the specified company in the given filing period and country.
     *
     * This API is available by invitation only.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @return FetchResult
     */
    public function getFilingsByCountry($companyId, $year, $month, $country)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a list of filings for the specified company in the filing period, country and region.
     *
     * This API is available by invitation only.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @return FetchResult
     */
    public function getFilingsByCountryRegion($companyId, $year, $month, $country, $region)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/{$region}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a list of filings for the specified company in the given filing period, country, region and form.
     *
     * This API is available by invitation only.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @return FetchResult
     */
    public function getFilingsByReturnName($companyId, $year, $month, $country, $region, $formCode)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/{$region}/{$formCode}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Add an adjustment to a given filing.
     *
     * This API is available by invitation only.
     * An "Adjustment" is usually an increase or decrease to customer funding to Avalara,
     * such as early filer discount amounts that are refunded to the customer, or efile fees from websites. 
     * Sometimes may be a manual change in tax liability similar to an augmentation.
     * This API creates a new adjustment for an existing tax filing.
     * This API can only be used when the filing has not yet been approved.
     *
     * 
     * @param FilingAdjustmentModel[] $model A list of Adjustments to be created for the specified filing.
     * @return FilingAdjustmentModel[]
     */
    public function createReturnAdjustment($companyId, $year, $month, $country, $region, $formCode, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/{$region}/{$formCode}/adjust";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Add an augmentation for a given filing.
     *
     * This API is available by invitation only.
     * An "Augmentation" is a manually added increase or decrease in tax liability, by either customer or Avalara 
     * usually due to customer wanting to report tax Avatax does not support, e.g. bad debts, rental tax.
     * This API creates a new augmentation for an existing tax filing.
     * This API can only be used when the filing has not been approved.
     *
     * 
     * @param FilingAugmentationModel[] $model A list of augmentations to be created for the specified filing.
     * @return FilingAugmentationModel[]
     */
    public function createReturnAugmentation($companyId, $year, $month, $country, $region, $formCode, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/{$region}/{$formCode}/augment";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Approve all filings for the specified company in the given filing period, country and region.
     *
     * This API is available by invitation only.
     * Approving a return means the customer is ready to let Avalara file that return.
     * Customer either approves themselves from admin console, 
     * else system auto-approves the night before the filing cycle
     * Sometimes Compliance has to manually unapprove and reapprove to modify liability or filing for the customer.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @param ApproveFilingsModel $model The approve request you wish to execute.
     * @return FilingModel[]
     */
    public function approveFilingsCountryRegion($companyId, $year, $month, $country, $region, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/{$region}/approve";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Rebuild a set of filings for the specified company in the given filing period, country and region.
     *
     * This API is available by invitation only.
     * Rebuilding a return means re-creating or updating the amounts to be filed for a filing.
     * Rebuilding has to be done whenever a customer adds transactions to a filing. 
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     * This API requires filing to be unapproved.
     *
     * 
     * @param RebuildFilingsModel $model The rebuild request you wish to execute.
     * @return FetchResult
     */
    public function rebuildFilingsByCountryRegion($companyId, $year, $month, $country, $region, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/{$region}/rebuild";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Approve all filings for the specified company in the given filing period and country.
     *
     * This API is available by invitation only.
     * Approving a return means the customer is ready to let Avalara file that return.
     * Customer either approves themselves from admin console, 
     * else system auto-approves the night before the filing cycle.
     * Sometimes Compliance has to manually unapprove and reapprove to modify liability or filing for the customer.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @param ApproveFilingsModel $model The approve request you wish to execute.
     * @return FilingModel[]
     */
    public function approveFilingsCountry($companyId, $year, $month, $country, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/approve";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Rebuild a set of filings for the specified company in the given filing period and country.
     *
     * This API is available by invitation only.
     * Rebuilding a return means re-creating or updating the amounts to be filed (worksheet) for a filing.
     * Rebuilding has to be done whenever a customer adds transactions to a filing.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     * This API requires filing to be unapproved.
     *
     * 
     * @param RebuildFilingsModel $model The rebuild request you wish to execute.
     * @return FetchResult
     */
    public function rebuildFilingsByCountry($companyId, $year, $month, $country, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/{$country}/rebuild";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Approve all filings for the specified company in the given filing period.
     *
     * This API is available by invitation only.
     * Approving a return means the customer is ready to let Avalara file that return.
     * Customer either approves themselves from admin console, 
     * else system auto-approves the night before the filing cycle.
     * Sometimes Compliance has to manually unapprove and reapprove to modify liability or filing for the customer.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @param ApproveFilingsModel $model The approve request you wish to execute.
     * @return FilingModel[]
     */
    public function approveFilings($companyId, $year, $month, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/approve";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a list of filings for the specified company in the year and month of a given filing period.
     *
     * This API is available by invitation only.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @return FileResult
     */
    public function getFilingAttachments($companyId, $year, $month)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/attachments";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single trace file for a company filing period
     *
     * This API is available by invitation only.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     *
     * 
     * @return FileResult
     */
    public function getFilingAttachmentsTraceFile($companyId, $year, $month)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/attachments/tracefile";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve worksheet checkup report for company and filing period.
     *
     * This API is available by invitation only.
     *
     * 
     * @return FilingsCheckupModel
     */
    public function filingsCheckupReports($companyId, $year, $month)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/checkup";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Rebuild a set of filings for the specified company in the given filing period.
     *
     * This API is available by invitation only.
     * Rebuilding a return means re-creating or updating the amounts to be filed (worksheet) for a filing.
     * Rebuilding has to be done whenever a customer adds transactions to a filing.
     * A "filing period" is the year and month of the date of the latest customer transaction allowed to be reported on a filing, 
     * based on filing frequency of filing.
     * This API requires filing to be unapproved.
     *
     * 
     * @param RebuildFilingsModel $model The rebuild request you wish to execute.
     * @return FetchResult
     */
    public function rebuildFilings($companyId, $year, $month, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/{$year}/{$month}/rebuild";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Edit an adjustment for a given filing.
     *
     * This API is available by invitation only.
     * An "Adjustment" is usually an increase or decrease to customer funding to Avalara,
     * such as early filer discount amounts that are refunded to the customer, or efile fees from websites. 
     * Sometimes may be a manual change in tax liability similar to an augmentation.
     * This API modifies an adjustment for an existing tax filing.
     * This API can only be used when the filing has not yet been approved.
     *
     * 
     * @param FilingAdjustmentModel $model The updated Adjustment.
     * @return FilingAdjustmentModel
     */
    public function updateReturnAdjustment($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/adjust/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete an adjustment for a given filing.
     *
     * This API is available by invitation only.
     * An "Adjustment" is usually an increase or decrease to customer funding to Avalara,
     * such as early filer discount amounts that are refunded to the customer, or efile fees from websites. 
     * Sometimes may be a manual change in tax liability similar to an augmentation.
     * This API deletes an adjustment for an existing tax filing.
     * This API can only be used when the filing has been unapproved.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteReturnAdjustment($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filings/adjust/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Edit an augmentation for a given filing.
     *
     * This API is available by invitation only.
     * An "Augmentation" is a manually added increase or decrease in tax liability, by either customer or Avalara 
     * usually due to customer wanting to report tax Avatax does not support, e.g. bad debts, rental tax.
     * This API modifies an augmentation for an existing tax filing.
     * This API can only be used when the filing has not been approved.
     *
     * 
     * @param FilingAugmentationModel $model The updated Augmentation.
     * @return FilingModel
     */
    public function updateReturnAugmentation($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/filings/augment/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete an augmentation for a given filing.
     *
     * This API is available by invitation only.
     * An "Augmentation" is a manually added increase or decrease in tax liability, by either customer or Avalara 
     * usually due to customer wanting to report tax Avatax does not support, e.g. bad debts, rental tax.
     * This API deletes an augmentation for an existing tax filing.
     * This API can only be used when the filing has been unapproved.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteReturnAugmentation($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/filings/augment/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve items for this company
     *
     * List all items defined for the current company.
     * 
     * An 'Item' represents a product or service that your company offers for sale.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listItemsByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/items";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new item
     *
     * Creates one or more new item objects attached to this company.
     *
     * 
     * @param ItemModel[] $model The item you wish to create.
     * @return ItemModel[]
     */
    public function createItems($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/items";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single item
     *
     * Get the item object identified by this URL.
     * An 'Item' represents a product or service that your company offers for sale.
     *
     * 
     * @return ItemModel
     */
    public function getItem($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/items/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single item
     *
     * Replace the existing item object at this URL with an updated object.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param ItemModel $model The item object you wish to update.
     * @return ItemModel
     */
    public function updateItem($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/items/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single item
     *
     * Marks the item object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteItem($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/items/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve locations for this company
     *
     * List all location objects defined for this company.
     * An 'Location' represents a physical address where a company does business.
     * Many taxing authorities require that you define a list of all locations where your company does business.
     * These locations may require additional custom configuration or tax registration with these authorities.
     * For more information on metadata requirements, see the '/api/v2/definitions/locationquestions' API.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listLocationsByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/locations";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new location
     *
     * Create one or more new location objects attached to this company.
     *
     * 
     * @param LocationModel[] $model The location you wish to create.
     * @return LocationModel[]
     */
    public function createLocations($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/locations";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single location
     *
     * Get the location object identified by this URL.
     * An 'Location' represents a physical address where a company does business.
     * Many taxing authorities require that you define a list of all locations where your company does business.
     * These locations may require additional custom configuration or tax registration with these authorities.
     * For more information on metadata requirements, see the '/api/v2/definitions/locationquestions' API.
     *
     * 
     * @return LocationModel
     */
    public function getLocation($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/locations/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single location
     *
     * Replace the existing location object at this URL with an updated object.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param LocationModel $model The location you wish to update.
     * @return LocationModel
     */
    public function updateLocation($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/locations/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single location
     *
     * Mark the location object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteLocation($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/locations/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Point of sale data file generation
     *
     * Builds a point-of-sale data file containing tax rates and rules for this location, containing tax rates for all
     * items defined for this company. This data file can be used to correctly calculate tax in the event a 
     * point-of-sale device is not able to reach AvaTax.
     * This data file can be customized for specific partner devices and usage conditions.
     * The result of this API is the file you requested in the format you requested using the 'responseType' field.
     * This API builds the file on demand, and is limited to a maximum of 7500 items.
     *
     * 
     * @param string $date The date for which point-of-sale data would be calculated (today by default)
     * @param string $format The format of the file (JSON by default) (See PointOfSaleFileType::* for a list of allowable values)
     * @param string $partnerId If specified, requests a custom partner-formatted version of the file. (See PointOfSalePartnerId::* for a list of allowable values)
     * @param boolean $includeJurisCodes When true, the file will include jurisdiction codes in the result.
     * @return FileResult
     */
    public function buildPointOfSaleDataForLocation($companyId, $id, $date, $format, $partnerId, $includeJurisCodes)
    {
        $path = "/api/v2/companies/{$companyId}/locations/{$id}/pointofsaledata";
        $guzzleParams = [
            'query' => ['date' => $date, 'format' => $format, 'partnerId' => $partnerId, 'includeJurisCodes' => $includeJurisCodes],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Validate the location against local requirements
     *
     * Returns validation information for this location.
     * This API call is intended to compare this location against the currently known taxing authority rules and regulations,
     * and provide information about what additional work is required to completely setup this location.
     *
     * 
     * @return LocationValidationModel
     */
    public function validateLocation($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/locations/{$id}/validate";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve nexus for this company
     *
     * List all nexus objects defined for this company.
     * The concept of 'Nexus' indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listNexusByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/nexus";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new nexus
     *
     * Creates one or more new nexus objects attached to this company.
     * The concept of 'Nexus' indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     * Note that not all fields within a nexus can be updated; Avalara publishes a list of all defined nexus at the
     * '/api/v2/definitions/nexus' endpoint.
     * You may only define nexus matching the official list of declared nexus.
     *
     * 
     * @param NexusModel[] $model The nexus you wish to create.
     * @return NexusModel[]
     */
    public function createNexus($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/nexus";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single nexus
     *
     * Get the nexus object identified by this URL.
     * The concept of 'Nexus' indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     *
     * 
     * @return NexusModel
     */
    public function getNexus($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/nexus/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single nexus
     *
     * Replace the existing nexus object at this URL with an updated object.
     * The concept of 'Nexus' indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     * Note that not all fields within a nexus can be updated; Avalara publishes a list of all defined nexus at the
     * '/api/v2/definitions/nexus' endpoint.
     * You may only define nexus matching the official list of declared nexus.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param NexusModel $model The nexus object you wish to update.
     * @return NexusModel
     */
    public function updateNexus($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/nexus/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single nexus
     *
     * Marks the existing nexus object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteNexus($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/nexus/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * List company nexus related to a tax form
     *
     * Retrieves a list of nexus related to a tax form.
     * 
     * The concept of `Nexus` indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * 
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     * 
     * This API is intended to provide useful information when examining a tax form. If you are about to begin filing
     * a tax form, you may want to know whether you have declared nexus in all the jurisdictions related to that tax 
     * form in order to better understand how the form will be filled out.
     *
     * 
     * @return NexusByTaxFormModel
     */
    public function getNexusByFormCode($companyId, $formCode)
    {
        $path = "/api/v2/companies/{$companyId}/nexus/byform/{$formCode}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve notices for a company.
     *
     * This API is available by invitation only.
     * List all tax notice objects assigned to this company.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listNoticesByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/notices";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new notice.
     *
     * This API is available by invitation only.
     * Create one or more new notice objects.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @param NoticeModel[] $model The notice object you wish to create.
     * @return NoticeModel[]
     */
    public function createNotices($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single notice.
     *
     * This API is available by invitation only.
     * Get the tax notice object identified by this URL.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @return NoticeModel
     */
    public function getNotice($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single notice.
     *
     * This API is available by invitation only.
     * Replace the existing notice object at this URL with an updated object.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param NoticeModel $model The notice object you wish to update.
     * @return NoticeModel
     */
    public function updateNotice($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single notice.
     *
     * This API is available by invitation only.
     * Mark the existing notice object at this URL as deleted.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteNotice($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve notice comments for a specific notice.
     *
     * This API is available by invitation only.
     * 'Notice comments' are updates by the notice team on the work to be done and that has been done so far on a notice.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @return FetchResult
     */
    public function getNoticeComments($id, $companyId)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/comments";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new notice comment.
     *
     * This API is available by invitation only.
     * 'Notice comments' are updates by the notice team on the work to be done and that has been done so far on a notice.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @param NoticeCommentModel[] $model The notice comments you wish to create.
     * @return NoticeCommentModel[]
     */
    public function createNoticeComment($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/comments";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve notice finance details for a specific notice.
     *
     * This API is available by invitation only.
     * 'Notice finance details' is the categorical breakdown of the total charge levied by the tax authority on our customer,
     * as broken down in our "notice log" found in Workflow. Main examples of the categories are 'Tax Due', 'Interest', 'Penalty', 'Total Abated'.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @return FetchResult
     */
    public function getNoticeFinanceDetails($id, $companyId)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/financedetails";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new notice finance details.
     *
     * This API is available by invitation only.
     * 'Notice finance details' is the categorical breakdown of the total charge levied by the tax authority on our customer,
     * as broken down in our "notice log" found in Workflow. Main examples of the categories are 'Tax Due', 'Interest', 'Penalty', 'Total Abated'.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @param NoticeFinanceModel[] $model The notice finance details you wish to create.
     * @return NoticeFinanceModel[]
     */
    public function createNoticeFinanceDetails($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/financedetails";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve notice responsibilities for a specific notice.
     *
     * This API is available by invitation only.
     * 'Notice responsibilities' are are those who are responsible for the notice.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @return FetchResult
     */
    public function getNoticeResponsibilities($id, $companyId)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/responsibilities";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new notice responsibility.
     *
     * This API is available by invitation only.
     * 'Notice comments' are updates by the notice team on the work to be done and that has been done so far on a notice.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @param NoticeResponsibilityDetailModel[] $model The notice responsibilities you wish to create.
     * @return NoticeResponsibilityDetailModel[]
     */
    public function createNoticeResponsibilities($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/responsibilities";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve notice root causes for a specific notice.
     *
     * This API is available by invitation only.
     * 'Notice root causes' are are those who are responsible for the notice.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @return FetchResult
     */
    public function getNoticeRootCauses($id, $companyId)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/rootcauses";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new notice root cause.
     *
     * This API is available by invitation only.
     * 'Notice root causes' are are those who are responsible for the notice.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     *
     * 
     * @param NoticeRootCauseDetailModel[] $model The notice root causes you wish to create.
     * @return NoticeRootCauseDetailModel[]
     */
    public function createNoticeRootCauses($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices/{$id}/rootcauses";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single attachment
     *
     * This API is available by invitation only.
     * Get the file attachment identified by this URL.
     *
     * 
     * @return FileResult
     */
    public function downloadNoticeAttachment($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/notices/files/{$id}/attachment";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single attachment
     *
     * This API is available by invitation only.
     * Get the file attachment identified by this URL.
     *
     * 
     * @param ResourceFileUploadRequestModel $model The ResourceFileId of the attachment to download.
     * @return FileResult
     */
    public function uploadAttachment($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/notices/files/attachment";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all settings for this company
     *
     * List all setting objects attached to this company.
     * A 'setting' is a piece of user-defined data that can be attached to a company, and it provides you the ability to store information
     * not defined or managed by Avalara.
     * You may create, update, and delete your own settings objects as required, and there is no mandatory data format for the 'name' and 
     * 'value' data fields.
     * To ensure correct operation of other programs or connectors, please create a new GUID for your application and use that value for
     * the 'set' data field.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listSettingsByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/settings";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new setting
     *
     * Create one or more new setting objects attached to this company.
     * A 'setting' is a piece of user-defined data that can be attached to a company, and it provides you the ability to store information
     * not defined or managed by Avalara.
     * You may create, update, and delete your own settings objects as required, and there is no mandatory data format for the 'name' and 
     * 'value' data fields.
     * To ensure correct operation of other programs or connectors, please create a new GUID for your application and use that value for
     * the 'set' data field.
     *
     * 
     * @param SettingModel[] $model The setting you wish to create.
     * @return SettingModel[]
     */
    public function createSettings($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/settings";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single setting
     *
     * Get a single setting object by its unique ID.
     * A 'setting' is a piece of user-defined data that can be attached to a company, and it provides you the ability to store information
     * not defined or managed by Avalara.
     * You may create, update, and delete your own settings objects as required, and there is no mandatory data format for the 'name' and 
     * 'value' data fields.
     * To ensure correct operation of other programs or connectors, please create a new GUID for your application and use that value for
     * the 'set' data field.
     *
     * 
     * @return SettingModel
     */
    public function getSetting($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/settings/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single setting
     *
     * Replace the existing setting object at this URL with an updated object.
     * A 'setting' is a piece of user-defined data that can be attached to a company, and it provides you the ability to store information
     * not defined or managed by Avalara.
     * You may create, update, and delete your own settings objects as required, and there is no mandatory data format for the 'name' and 
     * 'value' data fields.
     * To ensure correct operation of other programs or connectors, please create a new GUID for your application and use that value for
     * the 'set' data field.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param SettingModel $model The setting you wish to update.
     * @return SettingModel
     */
    public function updateSetting($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/settings/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single setting
     *
     * Mark the setting object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteSetting($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/settings/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve tax codes for this company
     *
     * List all taxcode objects attached to this company.
     * A 'TaxCode' represents a uniquely identified type of product, good, or service.
     * Avalara supports correct tax rates and taxability rules for all TaxCodes in all supported jurisdictions.
     * If you identify your products by tax code in your 'Create Transacion' API calls, Avalara will correctly calculate tax rates and
     * taxability rules for this product in all supported jurisdictions.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listTaxCodesByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/taxcodes";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new tax code
     *
     * Create one or more new taxcode objects attached to this company.
     * A 'TaxCode' represents a uniquely identified type of product, good, or service.
     * Avalara supports correct tax rates and taxability rules for all TaxCodes in all supported jurisdictions.
     * If you identify your products by tax code in your 'Create Transacion' API calls, Avalara will correctly calculate tax rates and
     * taxability rules for this product in all supported jurisdictions.
     *
     * 
     * @param TaxCodeModel[] $model The tax code you wish to create.
     * @return TaxCodeModel[]
     */
    public function createTaxCodes($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/taxcodes";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single tax code
     *
     * Get the taxcode object identified by this URL.
     * A 'TaxCode' represents a uniquely identified type of product, good, or service.
     * Avalara supports correct tax rates and taxability rules for all TaxCodes in all supported jurisdictions.
     * If you identify your products by tax code in your 'Create Transacion' API calls, Avalara will correctly calculate tax rates and
     * taxability rules for this product in all supported jurisdictions.
     *
     * 
     * @return TaxCodeModel
     */
    public function getTaxCode($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/taxcodes/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single tax code
     *
     * Replace the existing taxcode object at this URL with an updated object.
     * A 'TaxCode' represents a uniquely identified type of product, good, or service.
     * Avalara supports correct tax rates and taxability rules for all TaxCodes in all supported jurisdictions.
     * If you identify your products by tax code in your 'Create Transacion' API calls, Avalara will correctly calculate tax rates and
     * taxability rules for this product in all supported jurisdictions.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param TaxCodeModel $model The tax code you wish to update.
     * @return TaxCodeModel
     */
    public function updateTaxCode($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/taxcodes/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single tax code
     *
     * Marks the existing TaxCode object at this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteTaxCode($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/taxcodes/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve tax rules for this company
     *
     * List all taxrule objects attached to this company.
     * A tax rule represents a custom taxability rule for a product or service sold by your company.
     * If you have obtained a custom tax ruling from an auditor that changes the behavior of certain goods or services
     * within certain taxing jurisdictions, or you have obtained special tax concessions for certain dates or locations,
     * you may wish to create a TaxRule object to override the AvaTax engine's default behavior in those circumstances.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listTaxRules($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/taxrules";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new tax rule
     *
     * Create one or more new taxrule objects attached to this company.
     * A tax rule represents a custom taxability rule for a product or service sold by your company.
     * If you have obtained a custom tax ruling from an auditor that changes the behavior of certain goods or services
     * within certain taxing jurisdictions, or you have obtained special tax concessions for certain dates or locations,
     * you may wish to create a TaxRule object to override the AvaTax engine's default behavior in those circumstances.
     *
     * 
     * @param TaxRuleModel[] $model The tax rule you wish to create.
     * @return TaxRuleModel[]
     */
    public function createTaxRules($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/taxrules";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single tax rule
     *
     * Get the taxrule object identified by this URL.
     * A tax rule represents a custom taxability rule for a product or service sold by your company.
     * If you have obtained a custom tax ruling from an auditor that changes the behavior of certain goods or services
     * within certain taxing jurisdictions, or you have obtained special tax concessions for certain dates or locations,
     * you may wish to create a TaxRule object to override the AvaTax engine's default behavior in those circumstances.
     *
     * 
     * @return TaxRuleModel
     */
    public function getTaxRule($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/taxrules/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single tax rule
     *
     * Replace the existing taxrule object at this URL with an updated object.
     * A tax rule represents a custom taxability rule for a product or service sold by your company.
     * If you have obtained a custom tax ruling from an auditor that changes the behavior of certain goods or services
     * within certain taxing jurisdictions, or you have obtained special tax concessions for certain dates or locations,
     * you may wish to create a TaxRule object to override the AvaTax engine's default behavior in those circumstances.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param TaxRuleModel $model The tax rule you wish to update.
     * @return TaxRuleModel
     */
    public function updateTaxRule($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/taxrules/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single tax rule
     *
     * Mark the TaxRule identified by this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteTaxRule($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/taxrules/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve UPCs for this company
     *
     * List all UPC objects attached to this company.
     * A UPC represents a single UPC code in your catalog and matches this product to the tax code identified by this UPC.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function listUPCsByCompany($companyId, $filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/companies/{$companyId}/upcs";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new UPC
     *
     * Create one or more new UPC objects attached to this company.
     * A UPC represents a single UPC code in your catalog and matches this product to the tax code identified by this UPC.
     *
     * 
     * @param UPCModel[] $model The UPC you wish to create.
     * @return UPCModel[]
     */
    public function createUPCs($companyId, $model)
    {
        $path = "/api/v2/companies/{$companyId}/upcs";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve a single UPC
     *
     * Get the UPC object identified by this URL.
     * A UPC represents a single UPC code in your catalog and matches this product to the tax code identified by this UPC.
     *
     * 
     * @return UPCModel
     */
    public function getUPC($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/upcs/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single UPC
     *
     * Replace the existing UPC object at this URL with an updated object.
     * A UPC represents a single UPC code in your catalog and matches this product to the tax code identified by this UPC.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param UPCModel $model The UPC you wish to update.
     * @return UPCModel
     */
    public function updateUPC($companyId, $id, $model)
    {
        $path = "/api/v2/companies/{$companyId}/upcs/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single UPC
     *
     * Marks the UPC object identified by this URL as deleted.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteUPC($companyId, $id)
    {
        $path = "/api/v2/companies/{$companyId}/upcs/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Retrieve a single company
     *
     * Get the company object identified by this URL.
     * A 'company' represents a single corporation or individual that is registered to handle transactional taxes.
     * You may specify one or more of the following values in the '$include' parameter to fetch additional nested data, using commas to separate multiple values:
     * 
     *  * Contacts
     *  * Items
     *  * Locations
     *  * Nexus
     *  * Settings
     *  * TaxCodes
     *  * TaxRules
     *  * UPC
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @return CompanyModel
     */
    public function getCompany($id, $include)
    {
        $path = "/api/v2/companies/{$id}";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Update a single company
     *
     * Replace the existing company object at this URL with an updated object.
     * A 'company' represents a single corporation or individual that is registered to handle transactional taxes.
     * All data from the existing object will be replaced with data in the object you PUT. 
     * To set a field's value to null, you may either set its value to null or omit that field from the object you post.
     *
     * 
     * @param CompanyModel $model The company object you wish to update.
     * @return CompanyModel
     */
    public function updateCompany($id, $model)
    {
        $path = "/api/v2/companies/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Delete a single company
     *
     * Deleting a company will delete all child companies, and all users attached to this company.
     *
     * 
     * @return ErrorDetail[]
     */
    public function deleteCompany($id)
    {
        $path = "/api/v2/companies/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'DELETE', $guzzleParams);
    }

    /**
     * Check managed returns funding configuration for a company
     *
     * This API is available by invitation only.
     * Requires a subscription to Avalara Managed Returns or SST Certified Service Provider.
     * Returns a list of funding setup requests and their current status.
     * Each object in the result is a request that was made to setup or adjust funding configuration for this company.
     *
     * 
     * @return FundingStatusModel[]
     */
    public function listFundingRequestsByCompany($id)
    {
        $path = "/api/v2/companies/{$id}/funding";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Request managed returns funding setup for a company
     *
     * This API is available by invitation only.
     * Companies that use the Avalara Managed Returns or the SST Certified Service Provider services are 
     * required to setup their funding configuration before Avalara can begin filing tax returns on their 
     * behalf.
     * Funding configuration for each company is set up by submitting a funding setup request, which can
     * be sent either via email or via an embedded HTML widget.
     * When the funding configuration is submitted to Avalara, it will be reviewed by treasury team members
     * before approval.
     * This API records that an ambedded HTML funding setup widget was activated.
     * This API requires a subscription to Avalara Managed Returns or SST Certified Service Provider.
     *
     * 
     * @param FundingInitiateModel $model The funding initialization request
     * @return FundingStatusModel
     */
    public function createFundingRequest($id, $model)
    {
        $path = "/api/v2/companies/{$id}/funding/setup";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Quick setup for a company with a single physical address
     *
     * Shortcut to quickly setup a single-physical-location company with critical information and activate it.
     * This API provides quick and simple company setup functionality and does the following things:
     *  
     * * Create a company object with its own tax profile
     * * Add a key contact person for the company
     * * Set up one physical location for the main office
     * * Declare nexus in all taxing jurisdictions for that main office address
     * * Activate the company
     *  
     * This API only provides a limited subset of functionality compared to the 'Create Company' API call. 
     * If you need additional features or options not present in this 'Quick Setup' API call, please use the full 'Create Company' call instead.
     *
     * 
     * @param CompanyInitializationModel $model Information about the company you wish to create.
     * @return CompanyModel
     */
    public function companyInitialize($model)
    {
        $path = "/api/v2/companies/initialize";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all contacts
     *
     * Get multiple contact objects across all companies.
     * A 'contact' is a person associated with a company who is designated to handle certain responsibilities of
     * a tax collecting and filing entity.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryContacts($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/contacts";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all ISO 3166 countries
     *
     * Returns a list of all ISO 3166 country codes, and their US English friendly names.
     * This API is intended to be useful when presenting a dropdown box in your website to allow customers to select a country for 
     * a shipping address.
     *
     * 
     * @return FetchResult
     */
    public function listCountries()
    {
        $path = "/api/v2/definitions/countries";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of rate types for each country
     *
     * Returns the full list of Avalara-supported rate type file types
     * This API is intended to be useful to identify all the different rate types.
     *
     * 
     * @return FetchResult
     */
    public function listRateTypesByCountry($country)
    {
        $path = "/api/v2/definitions/countries/{$country}/ratetypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all ISO 3166 regions for a country
     *
     * Returns a list of all ISO 3166 region codes for a specific country code, and their US English friendly names.
     * This API is intended to be useful when presenting a dropdown box in your website to allow customers to select a region 
     * within the country for a shipping addresses.
     *
     * 
     * @return FetchResult
     */
    public function listRegionsByCountry($country)
    {
        $path = "/api/v2/definitions/countries/{$country}/regions";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported entity use codes
     *
     * Returns the full list of Avalara-supported entity use codes.
     * Entity/Use Codes are definitions of the entity who is purchasing something, or the purpose for which the transaction
     * is occurring. This information is generally used to determine taxability of the product.
     * In order to facilitate correct reporting of your taxes, you are encouraged to select the proper entity use codes for
     * all transactions that are exempt.
     *
     * 
     * @return FetchResult
     */
    public function listEntityUseCodes()
    {
        $path = "/api/v2/definitions/entityusecodes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all forms where logins can be verified automatically
     *
     * List all forms where logins can be verified automatically.
     * This API is intended to be useful to identify whether the user should be allowed
     * to automatically verify their login and password.
     *
     * 
     * @return FetchResult
     */
    public function listLoginVerifiers()
    {
        $path = "/api/v2/definitions/filingcalendars/loginverifiers";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Test whether a form supports online login verification
     *
     * This API is intended to be useful to identify whether the user should be allowed
     * to automatically verify their login and password.
     *
     * 
     * @return FetchResult
     */
    public function getLoginVerifierByForm($form)
    {
        $path = "/api/v2/definitions/filingcalendars/loginverifiers/{$form}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported filing frequencies.
     *
     * Returns the full list of Avalara-supported filing frequencies.
     * This API is intended to be useful to identify all the different filing frequencies that can be used in notices.
     *
     * 
     * @return FetchResult
     */
    public function listFilingFrequencies()
    {
        $path = "/api/v2/definitions/filingfrequencies";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List jurisdictions near a specific address
     *
     * Returns a list of all Avalara-supported taxing jurisdictions that apply to this address.
     * 
     * This API allows you to identify which jurisdictions are nearby a specific address according to the best available geocoding information.
     * It is intended to allow you to create a "Jurisdiction Override", which allows an address to be configured as belonging to a nearby 
     * jurisdiction in AvaTax.
     *  
     * The results of this API call can be passed to the `CreateJurisdictionOverride` API call.
     *
     * 
     * @param string $line1 The first address line portion of this address.
     * @param string $line2 The second address line portion of this address.
     * @param string $line3 The third address line portion of this address.
     * @param string $city The city portion of this address.
     * @param string $region The region, state, or province code portion of this address.
     * @param string $postalCode The postal code or zip code portion of this address.
     * @param string $country The two-character ISO-3166 code of the country portion of this address.
     * @return FetchResult
     */
    public function listJurisdictionsByAddress($line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $path = "/api/v2/definitions/jurisdictionsnearaddress";
        $guzzleParams = [
            'query' => ['line1' => $line1, 'line2' => $line2, 'line3' => $line3, 'city' => $city, 'region' => $region, 'postalCode' => $postalCode, 'country' => $country],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the list of questions that are required for a tax location
     *
     * Returns the list of additional questions you must answer when declaring a location in certain taxing jurisdictions.
     * Some tax jurisdictions require that you register or provide additional information to configure each physical place where
     * your company does business.
     * This information is not usually required in order to calculate tax correctly, but is almost always required to file your tax correctly.
     * You can call this API call for any address and obtain information about what questions must be answered in order to properly
     * file tax in that location.
     *
     * 
     * @param string $line1 The first line of this location's address.
     * @param string $line2 The second line of this location's address.
     * @param string $line3 The third line of this location's address.
     * @param string $city The city part of this location's address.
     * @param string $region The region, state, or province part of this location's address.
     * @param string $postalCode The postal code of this location's address.
     * @param string $country The country part of this location's address.
     * @param float $latitude Optionally identify the location via latitude/longitude instead of via address.
     * @param float $longitude Optionally identify the location via latitude/longitude instead of via address.
     * @return FetchResult
     */
    public function listLocationQuestionsByAddress($line1, $line2, $line3, $city, $region, $postalCode, $country, $latitude, $longitude)
    {
        $path = "/api/v2/definitions/locationquestions";
        $guzzleParams = [
            'query' => ['line1' => $line1, 'line2' => $line2, 'line3' => $line3, 'city' => $city, 'region' => $region, 'postalCode' => $postalCode, 'country' => $country, 'latitude' => $latitude, 'longitude' => $longitude],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported nexus for all countries and regions.
     *
     * Returns the full list of all Avalara-supported nexus for all countries and regions. 
     * This API is intended to be useful if your user interface needs to display a selectable list of nexus.
     *
     * 
     * @return FetchResult
     */
    public function definitionsNexusGet()
    {
        $path = "/api/v2/definitions/nexus";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported nexus for a country.
     *
     * Returns all Avalara-supported nexus for the specified country.
     * This API is intended to be useful if your user interface needs to display a selectable list of nexus filtered by country.
     *
     * 
     * @return FetchResult
     */
    public function definitionsNexusByCountryGet($country)
    {
        $path = "/api/v2/definitions/nexus/{$country}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported nexus for a country and region.
     *
     * Returns all Avalara-supported nexus for the specified country and region.
     * This API is intended to be useful if your user interface needs to display a selectable list of nexus filtered by country and region.
     *
     * 
     * @return FetchResult
     */
    public function definitionsNexusByCountryByRegionGet($country, $region)
    {
        $path = "/api/v2/definitions/nexus/{$country}/{$region}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all nexus that apply to a specific address.
     *
     * Returns a list of all Avalara-supported taxing jurisdictions that apply to this address.
     * This API allows you to identify which tax authorities apply to a physical location, salesperson address, or point of sale.
     * In general, it is usually expected that a company will declare nexus in all the jurisdictions that apply to each physical address
     * where the company does business.
     * The results of this API call can be passed to the 'Create Nexus' API call to declare nexus for this address.
     *
     * 
     * @param string $line1 The first address line portion of this address.
     * @param string $line2 The first address line portion of this address.
     * @param string $line3 The first address line portion of this address.
     * @param string $city The city portion of this address.
     * @param string $region The region, state, or province code portion of this address.
     * @param string $postalCode The postal code or zip code portion of this address.
     * @param string $country The two-character ISO-3166 code of the country portion of this address.
     * @return FetchResult
     */
    public function listNexusByAddress($line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $path = "/api/v2/definitions/nexus/byaddress";
        $guzzleParams = [
            'query' => ['line1' => $line1, 'line2' => $line2, 'line3' => $line3, 'city' => $city, 'region' => $region, 'postalCode' => $postalCode, 'country' => $country],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List nexus related to a tax form
     *
     * Retrieves a list of nexus related to a tax form.
     * 
     * The concept of `Nexus` indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * 
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     * 
     * This API is intended to provide useful information when examining a tax form. If you are about to begin filing
     * a tax form, you may want to know whether you have declared nexus in all the jurisdictions related to that tax 
     * form in order to better understand how the form will be filled out.
     *
     * 
     * @return NexusByTaxFormModel
     */
    public function listNexusByFormCode($formCode)
    {
        $path = "/api/v2/definitions/nexus/byform/{$formCode}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice customer funding options.
     *
     * Returns the full list of Avalara-supported tax notice customer funding options.
     * This API is intended to be useful to identify all the different notice customer funding options that can be used in notices.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeCustomerFundingOptions()
    {
        $path = "/api/v2/definitions/noticecustomerfundingoptions";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice customer types.
     *
     * Returns the full list of Avalara-supported tax notice customer types.
     * This API is intended to be useful to identify all the different notice customer types.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeCustomerTypes()
    {
        $path = "/api/v2/definitions/noticecustomertypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice filing types.
     *
     * Returns the full list of Avalara-supported tax notice filing types.
     * This API is intended to be useful to identify all the different notice filing types that can be used in notices.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeFilingtypes()
    {
        $path = "/api/v2/definitions/noticefilingtypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice priorities.
     *
     * Returns the full list of Avalara-supported tax notice priorities.
     * This API is intended to be useful to identify all the different notice priorities that can be used in notices.
     *
     * 
     * @return FetchResult
     */
    public function listNoticePriorities()
    {
        $path = "/api/v2/definitions/noticepriorities";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice reasons.
     *
     * Returns the full list of Avalara-supported tax notice reasons.
     * This API is intended to be useful to identify all the different tax notice reasons.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeReasons()
    {
        $path = "/api/v2/definitions/noticereasons";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice responsibility ids
     *
     * Returns the full list of Avalara-supported tax notice responsibility ids
     * This API is intended to be useful to identify all the different tax notice responsibilities.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeResponsibilities()
    {
        $path = "/api/v2/definitions/noticeresponsibilities";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice root causes
     *
     * Returns the full list of Avalara-supported tax notice root causes
     * This API is intended to be useful to identify all the different tax notice root causes.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeRootCauses()
    {
        $path = "/api/v2/definitions/noticerootcauses";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice statuses.
     *
     * Returns the full list of Avalara-supported tax notice statuses.
     * This API is intended to be useful to identify all the different tax notice statuses.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeStatuses()
    {
        $path = "/api/v2/definitions/noticestatuses";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax notice types.
     *
     * Returns the full list of Avalara-supported tax notice types.
     * This API is intended to be useful to identify all the different notice types that can be used in notices.
     *
     * 
     * @return FetchResult
     */
    public function listNoticeTypes()
    {
        $path = "/api/v2/definitions/noticetypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported extra parameters for creating transactions.
     *
     * Returns the full list of Avalara-supported extra parameters for the 'Create Transaction' API call.
     * This list of parameters is available for use when configuring your transaction.
     * Some parameters are only available for use if you have subscribed to certain features of AvaTax.
     *
     * 
     * @return FetchResult
     */
    public function listParameters()
    {
        $path = "/api/v2/definitions/parameters";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported permissions
     *
     * Returns the full list of Avalara-supported permission types.
     * This API is intended to be useful to identify the capabilities of a particular user logon.
     *
     * 
     * @return FetchResult
     */
    public function listPermissions()
    {
        $path = "/api/v2/definitions/permissions";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all ISO 3166 regions
     *
     * Returns a list of all ISO 3166 region codes and their US English friendly names.
     * This API is intended to be useful when presenting a dropdown box in your website to allow customers to select a region 
     * within the country for a shipping addresses.
     *
     * 
     * @return FetchResult
     */
    public function listRegions()
    {
        $path = "/api/v2/definitions/regions";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported resource file types
     *
     * Returns the full list of Avalara-supported resource file types
     * This API is intended to be useful to identify all the different resource file types.
     *
     * 
     * @return FetchResult
     */
    public function listResourceFileTypes()
    {
        $path = "/api/v2/definitions/resourcefiletypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported permissions
     *
     * Returns the full list of Avalara-supported permission types.
     * This API is intended to be useful when designing a user interface for selecting the security role of a user account.
     * Some security roles are restricted for Avalara internal use.
     *
     * 
     * @return FetchResult
     */
    public function listSecurityRoles()
    {
        $path = "/api/v2/definitions/securityroles";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported subscription types
     *
     * Returns the full list of Avalara-supported subscription types.
     * This API is intended to be useful for identifying which features you have added to your account.
     * You may always contact Avalara's sales department for information on available products or services.
     * You cannot change your subscriptions directly through the API.
     *
     * 
     * @return FetchResult
     */
    public function listSubscriptionTypes()
    {
        $path = "/api/v2/definitions/subscriptiontypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax authorities.
     *
     * Returns the full list of Avalara-supported tax authorities.
     * This API is intended to be useful to identify all the different authorities that receive tax.
     *
     * 
     * @return FetchResult
     */
    public function listTaxAuthorities()
    {
        $path = "/api/v2/definitions/taxauthorities";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported forms for each tax authority.
     *
     * Returns the full list of Avalara-supported forms for each tax authority.
     * This list represents tax forms that Avalara recognizes.
     * Customers who subscribe to Avalara Managed Returns Service can request these forms to be filed automatically 
     * based on the customer's AvaTax data.
     *
     * 
     * @return FetchResult
     */
    public function listTaxAuthorityForms()
    {
        $path = "/api/v2/definitions/taxauthorityforms";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax authority types.
     *
     * Returns the full list of Avalara-supported tax authority types.
     * This API is intended to be useful to identify all the different authority types.
     *
     * 
     * @return FetchResult
     */
    public function listTaxAuthorityTypes()
    {
        $path = "/api/v2/definitions/taxauthoritytypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax codes.
     *
     * Retrieves the list of Avalara-supported system tax codes.
     * A 'TaxCode' represents a uniquely identified type of product, good, or service.
     * Avalara supports correct tax rates and taxability rules for all TaxCodes in all supported jurisdictions.
     * If you identify your products by tax code in your 'Create Transacion' API calls, Avalara will correctly calculate tax rates and
     * taxability rules for this product in all supported jurisdictions.
     *
     * 
     * @return FetchResult
     */
    public function listTaxCodes()
    {
        $path = "/api/v2/definitions/taxcodes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve the full list of Avalara-supported tax code types.
     *
     * Returns the full list of recognized tax code types.
     * A 'Tax Code Type' represents a broad category of tax codes, and is less detailed than a single TaxCode.
     * This API is intended to be useful for broadly searching for tax codes by tax code type.
     *
     * 
     * @return TaxCodeTypesModel
     */
    public function listTaxCodeTypes()
    {
        $path = "/api/v2/definitions/taxcodetypes";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all filing calendars
     *
     * This API is available by invitation only.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryFilingCalendars($filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/filingcalendars";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Gets the request status and Login Result
     *
     * This API is available by invitation only.
     *
     * 
     * @return LoginVerificationOutputModel
     */
    public function loginVerificationGet($jobId)
    {
        $path = "/api/v2/filingcalendars/credentials/{$jobId}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * New request for getting for validating customer's login credentials
     *
     * This API is available by invitation only.
     *
     * 
     * @param LoginVerificationInputModel $model The model of the login information we are verifying
     * @return LoginVerificationOutputModel
     */
    public function loginVerificationPost($model)
    {
        $path = "/api/v2/filingcalendars/credentials/verify";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all filing requests
     *
     * This API is available by invitation only.
     * A "filing request" represents a request to change an existing filing calendar. Filing requests
     * are reviewed and validated by Avalara Compliance before being implemented.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryFilingRequests($filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/filingrequests";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve status about a funding setup request
     *
     * This API is available by invitation only.
     * Companies that use the Avalara Managed Returns or the SST Certified Service Provider services are 
     * required to setup their funding configuration before Avalara can begin filing tax returns on their 
     * behalf.
     * Funding configuration for each company is set up by submitting a funding setup request, which can
     * be sent either via email or via an embedded HTML widget.
     * When the funding configuration is submitted to Avalara, it will be reviewed by treasury team members
     * before approval.
     * This API checks the status on an existing funding request.
     * This API requires a subscription to Avalara Managed Returns or SST Certified Service Provider.
     *
     * 
     * @return FundingStatusModel
     */
    public function fundingRequestStatus($id)
    {
        $path = "/api/v2/fundingrequests/{$id}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Request the javascript for a funding setup widget
     *
     * This API is available by invitation only.
     * Companies that use the Avalara Managed Returns or the SST Certified Service Provider services are 
     * required to setup their funding configuration before Avalara can begin filing tax returns on their 
     * behalf.
     * Funding configuration for each company is set up by submitting a funding setup request, which can
     * be sent either via email or via an embedded HTML widget.
     * When the funding configuration is submitted to Avalara, it will be reviewed by treasury team members
     * before approval.
     * This API returns back the actual javascript code to insert into your application to render the 
     * JavaScript funding setup widget inline.
     * Use the 'methodReturn.javaScript' return value to insert this widget into your HTML page.
     * This API requires a subscription to Avalara Managed Returns or SST Certified Service Provider.
     *
     * 
     * @return FundingStatusModel
     */
    public function activateFundingRequest($id)
    {
        $path = "/api/v2/fundingrequests/{$id}/widget";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all items
     *
     * Get multiple item objects across all companies.
     * An 'Item' represents a product or service that your company offers for sale.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryItems($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/items";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all overrides
     *
     * Get multiple jurisdiction override objects across all companies.
     * 
     * A Jurisdiction Override is a configuration setting that allows you to select the taxing
     * jurisdiction for a specific address. If you encounter an address that is on the boundary
     * between two different jurisdictions, you can choose to set up a jurisdiction override
     * to switch this address to use different taxing jurisdictions.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryJurisdictionOverrides($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/jurisdictionoverrides";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all locations
     *
     * Get multiple location objects across all companies.
     * An 'Location' represents a physical address where a company does business.
     * Many taxing authorities require that you define a list of all locations where your company does business.
     * These locations may require additional custom configuration or tax registration with these authorities.
     * For more information on metadata requirements, see the '/api/v2/definitions/locationquestions' API.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryLocations($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/locations";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all nexus
     *
     * Get multiple nexus objects across all companies.
     * The concept of 'Nexus' indicates a place where your company has sufficient physical presence and is obligated
     * to collect and remit transaction-based taxes.
     * When defining companies in AvaTax, you must declare nexus for your company in order to correctly calculate tax
     * in all jurisdictions affected by your transactions.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryNexus($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/nexus";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all notices.
     *
     * This API is available by invitation only.
     * Get multiple notice objects across all companies.
     * A 'notice' represents a letter sent to a business by a tax authority regarding tax filing issues. Avalara
     * Returns customers often receive support and assistance from the Compliance Notices team in handling notices received by taxing authorities.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryNotices($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/notices";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Change Password
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Allows a user to change their password via the API.
     * This API only allows the currently authenticated user to change their password; it cannot be used to apply to a
     * different user than the one authenticating the current API call.
     *
     * 
     * @param PasswordChangeModel $model An object containing your current password and the new password.
     * @return string
     */
    public function changePassword($model)
    {
        $path = "/api/v2/passwords";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'PUT', $guzzleParams);
    }

    /**
     * Reset a user's password programmatically
     *
     * # For Registrar Use Only
     * This API is for use by Avalara Registrar administrative users only.
     * 
     * Allows a system admin to reset the password for a specific user via the API.
     * This API is only available for Avalara Registrar Admins, and can be used to reset the password of any
     * user based on internal Avalara business processes.
     *
     * 
     * @param SetPasswordModel $model The new password for this user
     * @return string
     */
    public function resetPassword($userId, $model)
    {
        $path = "/api/v2/passwords/{$userId}/reset";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Point of sale data file generation
     *
     * Builds a point-of-sale data file containing tax rates and rules for items and locations that can be used
     * to correctly calculate tax in the event a point-of-sale device is not able to reach AvaTax.
     * This data file can be customized for specific partner devices and usage conditions.
     * The result of this API is the file you requested in the format you requested using the 'responseType' field.
     * This API builds the file on demand, and is limited to files with no more than 7500 scenarios.
     *
     * 
     * @param PointOfSaleDataRequestModel $model Parameters about the desired file format and report format, specifying which company, locations and TaxCodes to include.
     * @return FileResult
     */
    public function buildPointOfSaleDataFile($model)
    {
        $path = "/api/v2/pointofsaledata/build";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all settings
     *
     * Get multiple setting objects across all companies.
     * A 'setting' is a piece of user-defined data that can be attached to a company, and it provides you the ability to store information
     * not defined or managed by Avalara.
     * You may create, update, and delete your own settings objects as required, and there is no mandatory data format for the 'name' and 
     * 'value' data fields.
     * To ensure correct operation of other programs or connectors, please create a new GUID for your application and use that value for
     * the 'set' data field.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function querySettings($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/settings";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all subscriptions
     *
     * Get multiple subscription objects across all accounts.
     * A 'subscription' indicates a licensed subscription to a named Avalara service.
     * To request or remove subscriptions, please contact Avalara sales or your customer account manager.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function querySubscriptions($filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/subscriptions";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all tax codes
     *
     * Get multiple taxcode objects across all companies.
     * A 'TaxCode' represents a uniquely identified type of product, good, or service.
     * Avalara supports correct tax rates and taxability rules for all TaxCodes in all supported jurisdictions.
     * If you identify your products by tax code in your 'Create Transacion' API calls, Avalara will correctly calculate tax rates and
     * taxability rules for this product in all supported jurisdictions.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryTaxCodes($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/taxcodes";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * FREE API - Sales tax rates for a specified address
     *
     * # Free-To-Use
     * 
     * The TaxRates API is a free-to-use, no cost option for estimating sales tax rates.
     * Any customer can request a free AvaTax account and make use of the TaxRates API.
     * However, this API is currently limited for US only
     * 
     * Note that the TaxRates API assumes the sale of general tangible personal property when estimating the sales tax
     * rate for a specified address. Avalara provides the `CreateTransaction` API, which provides extensive tax calculation 
     * support for scenarios including, but not limited to:
     * 
     * * Nexus declarations
     * * Taxability based on product/service type
     * * Sourcing rules affecting origin/destination states
     * * Customers who are exempt from certain taxes
     * * States that have dollar value thresholds for tax amounts
     * * Refunds for products purchased on a different date
     * * Detailed jurisdiction names and state assigned codes
     * * And more!
     * 
     * Please see [Estimating Tax with REST v2](http://developer.avalara.com/blog/2016/11/04/estimating-tax-with-rest-v2/)
     * for information on how to upgrade to the full AvaTax CreateTransaction API.
     *
     * 
     * @param string $line1 The street address of the location.
     * @param string $line2 The street address of the location.
     * @param string $line3 The street address of the location.
     * @param string $city The city name of the location.
     * @param string $region The state or region of the location
     * @param string $postalCode The postal code of the location.
     * @param string $country The two letter ISO-3166 country code.
     * @return TaxRateModel
     */
    public function taxRatesByAddress($line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $path = "/api/v2/taxrates/byaddress";
        $guzzleParams = [
            'query' => ['line1' => $line1, 'line2' => $line2, 'line3' => $line3, 'city' => $city, 'region' => $region, 'postalCode' => $postalCode, 'country' => $country],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * FREE API - Sales tax rates for a specified country and postal code
     *
     * # Free-To-Use
     * 
     * The TaxRates API is a free-to-use, no cost option for estimating sales tax rates.
     * Any customer can request a free AvaTax account and make use of the TaxRates API.
     * However, this API is currently limited for US only
     * 
     * Note that the TaxRates API assumes the sale of general tangible personal property when estimating the sales tax
     * rate for a specified address. Avalara provides the `CreateTransaction` API, which provides extensive tax calculation 
     * support for scenarios including, but not limited to:
     * 
     * * Nexus declarations
     * * Taxability based on product/service type
     * * Sourcing rules affecting origin/destination states
     * * Customers who are exempt from certain taxes
     * * States that have dollar value thresholds for tax amounts
     * * Refunds for products purchased on a different date
     * * Detailed jurisdiction names and state assigned codes
     * * And more!
     * 
     * Please see [Estimating Tax with REST v2](http://developer.avalara.com/blog/2016/11/04/estimating-tax-with-rest-v2/)
     * for information on how to upgrade to the full AvaTax CreateTransaction API.
     *
     * 
     * @param string $country The two letter ISO-3166 country code.
     * @param string $postalCode The postal code of the location.
     * @return TaxRateModel
     */
    public function taxRatesByPostalCode($country, $postalCode)
    {
        $path = "/api/v2/taxrates/bypostalcode";
        $guzzleParams = [
            'query' => ['country' => $country, 'postalCode' => $postalCode],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all tax rules
     *
     * Get multiple taxrule objects across all companies.
     * A tax rule represents a custom taxability rule for a product or service sold by your company.
     * If you have obtained a custom tax ruling from an auditor that changes the behavior of certain goods or services
     * within certain taxing jurisdictions, or you have obtained special tax concessions for certain dates or locations,
     * you may wish to create a TaxRule object to override the AvaTax engine's default behavior in those circumstances.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryTaxRules($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/taxrules";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve a single transaction by ID
     *
     * Get the unique transaction identified by this URL.
     * This endpoint retrieves the exact transaction identified by this ID number even if that transaction was later adjusted
     * by using the 'Adjust Transaction' endpoint.
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * You may specify one or more of the following values in the '$include' parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Lines
     * * Details (implies lines)
     * * Summary (implies details)
     * * Addresses
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @return TransactionModel
     */
    public function getTransactionById($id, $include)
    {
        $path = "/api/v2/transactions/{$id}";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Create a new transaction
     *
     * Records a new transaction in AvaTax.
     * 
     * The `CreateTransaction` endpoint uses the configuration values specified by your company to identify the correct tax rules
     * and rates to apply to all line items in this transaction, and reports the total tax calculated by AvaTax based on your
     * company's configuration and the data provided in this API call.
     * 
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     * You may specify one or more of the following values in the '$include' parameter to fetch additional nested data, using commas to separate multiple values:
     *  
     * * Lines
     * * Details (implies lines)
     * * Summary (implies details)
     * * Addresses
     *  
     * If you don't specify '$include' parameter, it will include both details and addresses.
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param CreateTransactionModel $model The transaction you wish to create
     * @return TransactionModel
     */
    public function createTransaction($include, $model)
    {
        $path = "/api/v2/transactions/create";
        $guzzleParams = [
            'query' => ['$include' => $include],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Lock a set of documents
     *
     * This API is available by invitation only.
     * 
     * Lock a set of transactions uniquely identified by DocumentIds provided. This API allows locking multiple documents at once.
     * After this API call succeeds, documents will be locked and can't be voided.
     * 
     * A transaction represents a unique potentially taxable action that your company has recorded, and transactions include actions like
     * sales, purchases, inventory transfer, and returns (also called refunds).
     *
     * 
     * @param BulkLockTransactionModel $model bulk lock request
     * @return BulkLockTransactionResult
     */
    public function bulkLockTransaction($model)
    {
        $path = "/api/v2/transactions/lock";
        $guzzleParams = [
            'query' => [],
            'body' => json_encode($model)
        ];
        return $this->restCall($path, 'POST', $guzzleParams);
    }

    /**
     * Retrieve all UPCs
     *
     * Get multiple UPC objects across all companies.
     * A UPC represents a single UPC code in your catalog and matches this product to the tax code identified by this UPC.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryUPCs($filter, $include, $top, $skip, $orderBy)
    {
        $path = "/api/v2/upcs";
        $guzzleParams = [
            'query' => ['$filter' => $filter, '$include' => $include, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Retrieve all users
     *
     * Get multiple user objects across all accounts.
     * A user represents one person with access privileges to make API calls and work with a specific account.
     * 
     * Search for specific objects using the criteria in the `$filter` parameter; full documentation is available on [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * Paginate your results using the `$top`, `$skip`, and `$orderby` parameters.
     *
     * 
     * @param string $include A comma separated list of child objects to return underneath the primary object.
     * @param string $filter A filter statement to identify specific records to retrieve. For more information on filtering, see [Filtering in REST](http://developer.avalara.com/avatax/filtering-in-rest/) .
     * @param int $top If nonzero, return no more than this number of results. Used with $skip to provide pagination for large datasets.
     * @param int $skip If nonzero, skip this number of results before returning data. Used with $top to provide pagination for large datasets.
     * @param string $orderBy A comma separated list of sort statements in the format `(fieldname) [ASC|DESC]`, for example `id ASC`.
     * @return FetchResult
     */
    public function queryUsers($include, $filter, $top, $skip, $orderBy)
    {
        $path = "/api/v2/users";
        $guzzleParams = [
            'query' => ['$include' => $include, '$filter' => $filter, '$top' => $top, '$skip' => $skip, '$orderBy' => $orderBy],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Tests connectivity and version of the service
     *
     * This API helps diagnose connectivity problems between your application and AvaTax; you may call this API even 
     * if you do not have verified connection credentials.
     * The results of this API call will help you determine whether your computer can contact AvaTax via the network,
     * whether your authentication credentials are recognized, and the roundtrip time it takes to communicate with
     * AvaTax.
     *
     * 
     * @return PingResultModel
     */
    public function ping()
    {
        $path = "/api/v2/utilities/ping";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * List all services to which the current user is subscribed
     *
     * Returns the list of all subscriptions enabled for the current account.
     * This API is intended to help you determine whether you have the necessary subscription to use certain API calls
     * within AvaTax.
     *
     * 
     * @return FetchResult
     */
    public function listMySubscriptions()
    {
        $path = "/api/v2/utilities/subscriptions";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Checks if the current user is subscribed to a specific service
     *
     * Returns a subscription object for the current account, or 404 Not Found if this subscription is not enabled for this account.
     * This API call is intended to allow you to identify whether you have the necessary account configuration to access certain
     * features of AvaTax, and would be useful in debugging access privilege problems.
     *
     * 
     * @return SubscriptionModel
     */
    public function getMySubscription($serviceTypeId)
    {
        $path = "/api/v2/utilities/subscriptions/{$serviceTypeId}";
        $guzzleParams = [
            'query' => [],
            'body' => null
        ];
        return $this->restCall($path, 'GET', $guzzleParams);
    }

    /**
     * Make a single REST call to the AvaTax v2 API server
     *
     * @param string $apiUrl           The relative path of the API on the server
     * @param string $verb             The HTTP verb being used in this request
     * @param string $guzzleParams     The Guzzle parameters for this request, including query string and body parameters
     */
    private function restCall($apiUrl, $verb, $guzzleParams)
    {
        // Set authentication on the parameters
        if (!isset($guzzleParams['auth'])){
            $guzzleParams['auth'] = $this->auth;
        }
    
        // Contact the server
        try {
            $request = $this->client->createRequest($verb, $apiUrl, $guzzleParams);
            $response = $this->client->send($request);
            $body = $response->getBody();
            return json_decode($body);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

/*****************************************************************************
 *                              Object Models                                *
 *****************************************************************************/


/**
 * An AvaTax account.
 */
class AccountModel
{

    /**
     * @var int The unique ID number assigned to this account.
     */
    public $id;

    /**
     * @var string The name of this account.
     */
    public $name;

    /**
     * @var string The earliest date on which this account may be used.
     */
    public $effectiveDate;

    /**
     * @var string If this account has been closed, this is the last date the account was open.
     */
    public $endDate;

    /**
     * @var string The current status of this account. (See AccountStatusId::* for a list of allowable values)
     */
    public $accountStatusId;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var SubscriptionModel[] Optional: A list of subscriptions granted to this account. To fetch this list, add the query string "?$include=Subscriptions" to your URL.
     */
    public $subscriptions;

    /**
     * @var UserModel[] Optional: A list of all the users belonging to this account. To fetch this list, add the query string "?$include=Users" to your URL.
     */
    public $users;

}

/**
 * Represents a service that this account has subscribed to.
 */
class SubscriptionModel
{

    /**
     * @var int The unique ID number of this subscription.
     */
    public $id;

    /**
     * @var int The unique ID number of the account this subscription belongs to.
     */
    public $accountId;

    /**
     * @var int The unique ID number of the service that the account is subscribed to.
     */
    public $subscriptionTypeId;

    /**
     * @var string A friendly description of the service that the account is subscribed to.
     */
    public $subscriptionDescription;

    /**
     * @var string The date when the subscription began.
     */
    public $effectiveDate;

    /**
     * @var string If the subscription has ended or will end, this date indicates when the subscription ends.
     */
    public $endDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * An account user who is permitted to use AvaTax.
 */
class UserModel
{

    /**
     * @var int The unique ID number of this user.
     */
    public $id;

    /**
     * @var int The unique ID number of the account to which this user belongs.
     */
    public $accountId;

    /**
     * @var int If this user is locked to one company (and its children), this is the unique ID number of the company to which this user belongs.
     */
    public $companyId;

    /**
     * @var string The username which is used to log on to the AvaTax website, or to authenticate against API calls.
     */
    public $userName;

    /**
     * @var string The first or given name of the user.
     */
    public $firstName;

    /**
     * @var string The last or family name of the user.
     */
    public $lastName;

    /**
     * @var string The email address to be used to contact this user. If the user has forgotten a password, an email can be sent to this email address with information on how to reset this password.
     */
    public $email;

    /**
     * @var string The postal code in which this user resides.
     */
    public $postalCode;

    /**
     * @var string The security level for this user. (See SecurityRoleId::* for a list of allowable values)
     */
    public $securityRoleId;

    /**
     * @var string The status of the user's password. (See PasswordStatusId::* for a list of allowable values)
     */
    public $passwordStatus;

    /**
     * @var boolean True if this user is currently active.
     */
    public $isActive;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Message object
 */
class ErrorDetail
{

    /**
     * @var string Name of the error or message. (See ErrorCodeId::* for a list of allowable values)
     */
    public $code;

    /**
     * @var int Unique ID number referring to this error or message.
     */
    public $number;

    /**
     * @var string Concise summary of the message, suitable for display in the caption of an alert box.
     */
    public $message;

    /**
     * @var string A more detailed description of the problem referenced by this error message, suitable for display in the contents area of an alert box.
     */
    public $description;

    /**
     * @var string Indicates the SOAP Fault code, if this was related to an error that corresponded to AvaTax SOAP v1 behavior.
     */
    public $faultCode;

    /**
     * @var string URL to help for this message
     */
    public $helpLink;

    /**
     * @var string Item the message refers to, if applicable. This is used to indicate a missing or incorrect value.
     */
    public $refersTo;

    /**
     * @var string Severity of the message (See SeverityLevel::* for a list of allowable values)
     */
    public $severity;

}

/**
 * Represents a request for a new account with Avalara for a new subscriber.
 * Contains information about the account requested and the rate plan selected.
 */
class NewAccountRequestModel
{

    /**
     * @var string[] The list of products to which this account would like to subscribe.
     */
    public $products;

    /**
     * @var string The name of the connector that will be the primary method of access used to call the account created. For a list of available connectors, please contact your Avalara representative.
     */
    public $connectorName;

    /**
     * @var string An approved partner account can be referenced when provisioning an account, allowing a link between  the partner and the provisioned account.
     */
    public $parentAccountNumber;

    /**
     * @var string Identifies a referring partner for the assessment of referral-based commissions.
     */
    public $referrerId;

    /**
     * @var string Zuora-generated Payment ID to which the new account should be associated. For free trial accounts, an empty string is acceptable.
     */
    public $paymentMethodId;

    /**
     * @var string The date on which the account should take effect. If null, defaults to today.
     */
    public $effectiveDate;

    /**
     * @var string The date on which the account should expire. If null, defaults to a 90-day trial account.
     */
    public $endDate;

    /**
     * @var string Account Name
     */
    public $accountName;

    /**
     * @var string First Name of the primary contact person for this account
     */
    public $firstName;

    /**
     * @var string Last Name of the primary contact person for this account
     */
    public $lastName;

    /**
     * @var string Title of the primary contact person for this account
     */
    public $title;

    /**
     * @var string Phone number of the primary contact person for this account
     */
    public $phoneNumber;

    /**
     * @var string Email of the primary contact person for this account
     */
    public $email;

    /**
     * @var string If no password is supplied, an a tempoarary password is generated by the system and emailed to the user. The user will  be challenged to change this password upon logging in to the Admin Console. If supplied, will be the set password for  the default created user, and the user will not be challenged to change their password upon login to the Admin Console.
     */
    public $userPassword;

}

/**
 * Represents information about a newly created account
 */
class NewAccountModel
{

    /**
     * @var int This is the ID number of the account that was created
     */
    public $accountId;

    /**
     * @var string This is the email address to which credentials were mailed
     */
    public $accountDetailsEmailedTo;

    /**
     * @var string The date and time when this account was created
     */
    public $createdDate;

    /**
     * @var string The date and time when account information was emailed to the user
     */
    public $emailedDate;

    /**
     * @var string If this account includes any limitations, specify them here
     */
    public $limitations;

}

/**
 * Represents a request for a free trial account for AvaTax.
 * Free trial accounts are only available on the Sandbox environment.
 */
class FreeTrialRequestModel
{

    /**
     * @var string The first or given name of the user requesting a free trial.
     */
    public $firstName;

    /**
     * @var string The last or family name of the user requesting a free trial.
     */
    public $lastName;

    /**
     * @var string The email address of the user requesting a free trial.
     */
    public $email;

    /**
     * @var string The company or organizational name for this free trial. If this account is for personal use, it is acceptable  to use your full name here.
     */
    public $company;

    /**
     * @var string The phone number of the person requesting the free trial.
     */
    public $phone;

}

/**
 * Represents a license key reset request.
 */
class ResetLicenseKeyModel
{

    /**
     * @var int The primary key of the account ID to reset
     */
    public $accountId;

    /**
     * @var boolean Set this value to true to reset the license key for this account. This license key reset function will only work when called using the credentials of the account administrator of this account.
     */
    public $confirmResetLicenseKey;

}

/**
 * Represents a license key for this account.
 */
class LicenseKeyModel
{

    /**
     * @var int The primary key of the account
     */
    public $accountId;

    /**
     * @var string This is your private license key. You must record this license key for safekeeping. If you lose this key, you must contact the ResetLicenseKey API in order to request a new one. Each account can only have one license key at a time.
     */
    public $privateLicenseKey;

    /**
     * @var string If your software allows you to specify the HTTP Authorization header directly, this is the header string you  should use when contacting Avalara to make API calls with this license key.
     */
    public $httpRequestHeader;

}

/**
 * TextCase info for input address
 */
class AddressValidationInfo
{

    /**
     * @var string Specify the text case for the validated address result. If not specified, will return uppercase. (See TextCase::* for a list of allowable values)
     */
    public $textCase;

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * Address Resolution Model
 */
class AddressResolutionModel
{

    /**
     * @var AddressInfo The original address
     */
    public $address;

    /**
     * @var ValidatedAddressInfo[] The validated address or addresses
     */
    public $validatedAddresses;

    /**
     * @var CoordinateInfo The geospatial coordinates of this address
     */
    public $coordinates;

    /**
     * @var string The resolution quality of the geospatial coordinates (See ResolutionQuality::* for a list of allowable values)
     */
    public $resolutionQuality;

    /**
     * @var TaxAuthorityInfo[] List of informational and warning messages regarding this address
     */
    public $taxAuthorities;

    /**
     * @var AvaTaxMessage[] List of informational and warning messages regarding this address
     */
    public $messages;

}

/**
 * Represents an address to resolve.
 */
class AddressInfo
{

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * Represents a validated address
 */
class ValidatedAddressInfo
{

    /**
     * @var string Address type code. One of:  * F - Firm or company address * G - General Delivery address * H - High-rise or business complex * P - PO Box address * R - Rural route address * S - Street or residential address
     */
    public $addressType;

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * Coordinate Info
 */
class CoordinateInfo
{

    /**
     * @var float Latitude
     */
    public $latitude;

    /**
     * @var float Longitude
     */
    public $longitude;

}

/**
 * Tax Authority Info
 */
class TaxAuthorityInfo
{

    /**
     * @var string Avalara Id
     */
    public $avalaraId;

    /**
     * @var string Jurisdiction Name
     */
    public $jurisdictionName;

    /**
     * @var string Jurisdiction Type (See JurisdictionType::* for a list of allowable values)
     */
    public $jurisdictionType;

    /**
     * @var string Signature Code
     */
    public $signatureCode;

}

/**
 * Informational or warning messages returned by AvaTax with a transaction
 */
class AvaTaxMessage
{

    /**
     * @var string A brief summary of what this message tells us
     */
    public $summary;

    /**
     * @var string Detailed information that explains what the summary provided
     */
    public $details;

    /**
     * @var string Information about what object in your request this message refers to
     */
    public $refersTo;

    /**
     * @var string A category that indicates how severely this message affects the results
     */
    public $severity;

    /**
     * @var string The name of the code or service that generated this message
     */
    public $source;

}

/**
 * Represents a batch of uploaded documents.
 */
class BatchModel
{

    /**
     * @var int The unique ID number of this batch.
     */
    public $id;

    /**
     * @var string The user-friendly readable name for this batch.
     */
    public $name;

    /**
     * @var int The Account ID number of the account that owns this batch.
     */
    public $accountId;

    /**
     * @var int The Company ID number of the company that owns this batch.
     */
    public $companyId;

    /**
     * @var string The type of this batch. (See BatchType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string This batch's current processing status (See BatchStatus::* for a list of allowable values)
     */
    public $status;

    /**
     * @var string Any optional flags provided for this batch
     */
    public $options;

    /**
     * @var string The agent used to create this batch
     */
    public $batchAgent;

    /**
     * @var string The date/time when this batch started processing
     */
    public $startedDate;

    /**
     * @var int The number of records in this batch; determined by the server
     */
    public $recordCount;

    /**
     * @var int The current record being processed
     */
    public $currentRecord;

    /**
     * @var string The date/time when this batch was completely processed
     */
    public $completedDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var BatchFileModel[] The list of files contained in this batch.
     */
    public $files;

}

/**
 * Represents one file in a batch upload.
 */
class BatchFileModel
{

    /**
     * @var int The unique ID number assigned to this batch file.
     */
    public $id;

    /**
     * @var int The unique ID number of the batch that this file belongs to.
     */
    public $batchId;

    /**
     * @var string Logical Name of file (e.g. "Input" or "Error").
     */
    public $name;

    /**
     * @var string Content of the batch file. (This value is encoded as a Base64 string)
     */
    public $content;

    /**
     * @var int Size of content, in bytes.
     */
    public $contentLength;

    /**
     * @var string Content mime type (e.g. text/csv). This is used for HTTP downloading.
     */
    public $contentType;

    /**
     * @var string File extension (e.g. CSV).
     */
    public $fileExtension;

    /**
     * @var int Number of errors that occurred when processing this file.
     */
    public $errorCount;

}

/**
 * A company or business entity.
 */
class CompanyModel
{

    /**
     * @var int The unique ID number of this company.
     */
    public $id;

    /**
     * @var int The unique ID number of the account this company belongs to.
     */
    public $accountId;

    /**
     * @var int If this company is fully owned by another company, this is the unique identity of the parent company.
     */
    public $parentCompanyId;

    /**
     * @var string If this company files Streamlined Sales Tax, this is the PID of this company as defined by the Streamlined Sales Tax governing board.
     */
    public $sstPid;

    /**
     * @var string A unique code that references this company within your account.
     */
    public $companyCode;

    /**
     * @var string The name of this company, as shown to customers.
     */
    public $name;

    /**
     * @var boolean This flag is true if this company is the default company for this account. Only one company may be set as the default.
     */
    public $isDefault;

    /**
     * @var int If set, this is the unique ID number of the default location for this company.
     */
    public $defaultLocationId;

    /**
     * @var boolean This flag indicates whether tax activity can occur for this company. Set this flag to true to permit the company to process transactions.
     */
    public $isActive;

    /**
     * @var string For United States companies, this field contains your Taxpayer Identification Number.  This is a nine digit number that is usually called an EIN for an Employer Identification Number if this company is a corporation,  or SSN for a Social Security Number if this company is a person. This value is required if you subscribe to Avalara Managed Returns or the SST Certified Service Provider services,  but it is optional if you do not subscribe to either of those services.
     */
    public $taxpayerIdNumber;

    /**
     * @var boolean Set this flag to true to give this company its own unique tax profile. If this flag is true, this company will have its own Nexus, TaxRule, TaxCode, and Item definitions. If this flag is false, this company will inherit all profile values from its parent.
     */
    public $hasProfile;

    /**
     * @var boolean Set this flag to true if this company must file its own tax returns. For users who have Returns enabled, this flag turns on monthly Worksheet generation for the company.
     */
    public $isReportingEntity;

    /**
     * @var string If this company participates in Streamlined Sales Tax, this is the date when the company joined the SST program.
     */
    public $sstEffectiveDate;

    /**
     * @var string The two character ISO-3166 country code of the default country for this company.
     */
    public $defaultCountry;

    /**
     * @var string This is the three character ISO-4217 currency code of the default currency used by this company.
     */
    public $baseCurrencyCode;

    /**
     * @var string Indicates whether this company prefers to round amounts at the document level or line level. (See RoundingLevelId::* for a list of allowable values)
     */
    public $roundingLevelId;

    /**
     * @var boolean Set this value to true to receive warnings in API calls via SOAP.
     */
    public $warningsEnabled;

    /**
     * @var boolean Set this flag to true to indicate that this company is a test company. If you have Returns enabled, Test companies will not file tax returns and can be used for validation purposes.
     */
    public $isTest;

    /**
     * @var string Used to apply tax detail dependency at a jurisdiction level. (See TaxDependencyLevelId::* for a list of allowable values)
     */
    public $taxDependencyLevelId;

    /**
     * @var boolean Set this value to true to indicate that you are still working to finish configuring this company. While this value is true, no tax reporting will occur and the company will not be usable for transactions.
     */
    public $inProgress;

    /**
     * @var string Business Identification No
     */
    public $businessIdentificationNo;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var ContactModel[] Optional: A list of contacts defined for this company. To fetch this list, add the query string "?$include=Contacts" to your URL.
     */
    public $contacts;

    /**
     * @var ItemModel[] Optional: A list of items defined for this company. To fetch this list, add the query string "?$include=Items" to your URL.
     */
    public $items;

    /**
     * @var LocationModel[] Optional: A list of locations defined for this company. To fetch this list, add the query string "?$include=Locations" to your URL.
     */
    public $locations;

    /**
     * @var NexusModel[] Optional: A list of nexus defined for this company. To fetch this list, add the query string "?$include=Nexus" to your URL.
     */
    public $nexus;

    /**
     * @var SettingModel[] Optional: A list of settings defined for this company. To fetch this list, add the query string "?$include=Settings" to your URL.
     */
    public $settings;

    /**
     * @var TaxCodeModel[] Optional: A list of tax codes defined for this company. To fetch this list, add the query string "?$include=TaxCodes" to your URL.
     */
    public $taxCodes;

    /**
     * @var TaxRuleModel[] Optional: A list of tax rules defined for this company. To fetch this list, add the query string "?$include=TaxRules" to your URL.
     */
    public $taxRules;

    /**
     * @var UPCModel[] Optional: A list of UPCs defined for this company. To fetch this list, add the query string "?$include=UPCs" to your URL.
     */
    public $upcs;

}

/**
 * A contact person for a company.
 */
class ContactModel
{

    /**
     * @var int The unique ID number of this contact.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this contact belongs.
     */
    public $companyId;

    /**
     * @var string A unique code for this contact.
     */
    public $contactCode;

    /**
     * @var string The first or given name of this contact.
     */
    public $firstName;

    /**
     * @var string The middle name of this contact.
     */
    public $middleName;

    /**
     * @var string The last or family name of this contact.
     */
    public $lastName;

    /**
     * @var string Professional title of this contact.
     */
    public $title;

    /**
     * @var string The first line of the postal mailing address of this contact.
     */
    public $line1;

    /**
     * @var string The second line of the postal mailing address of this contact.
     */
    public $line2;

    /**
     * @var string The third line of the postal mailing address of this contact.
     */
    public $line3;

    /**
     * @var string The city of the postal mailing address of this contact.
     */
    public $city;

    /**
     * @var string The state, region, or province of the postal mailing address of this contact.
     */
    public $region;

    /**
     * @var string The postal code or zip code of the postal mailing address of this contact.
     */
    public $postalCode;

    /**
     * @var string The ISO 3166 two-character country code of the postal mailing address of this contact.
     */
    public $country;

    /**
     * @var string The email address of this contact.
     */
    public $email;

    /**
     * @var string The main phone number for this contact.
     */
    public $phone;

    /**
     * @var string The mobile phone number for this contact.
     */
    public $mobile;

    /**
     * @var string The facsimile phone number for this contact.
     */
    public $fax;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents an item in your company's product catalog.
 */
class ItemModel
{

    /**
     * @var int The unique ID number of this item.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that owns this item.
     */
    public $companyId;

    /**
     * @var string A unique code representing this item.
     */
    public $itemCode;

    /**
     * @var int The unique ID number of the tax code that is applied when selling this item. When creating or updating an item, you can either specify the Tax Code ID number or the Tax Code string; you do not need to specify both values.
     */
    public $taxCodeId;

    /**
     * @var string The unique code string of the Tax Code that is applied when selling this item. When creating or updating an item, you can either specify the Tax Code ID number or the Tax Code string; you do not need to specify both values.
     */
    public $taxCode;

    /**
     * @var string A friendly description of this item in your product catalog.
     */
    public $description;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * A location where this company does business.
 * Some jurisdictions may require you to list all locations where your company does business.
 */
class LocationModel
{

    /**
     * @var int The unique ID number of this location.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that operates at this location.
     */
    public $companyId;

    /**
     * @var string A code that identifies this location. Must be unique within your company.
     */
    public $locationCode;

    /**
     * @var string A friendly name for this location.
     */
    public $description;

    /**
     * @var string Indicates whether this location is a physical place of business or a temporary salesperson location. (See AddressTypeId::* for a list of allowable values)
     */
    public $addressTypeId;

    /**
     * @var string Indicates the type of place of business represented by this location. (See AddressCategoryId::* for a list of allowable values)
     */
    public $addressCategoryId;

    /**
     * @var string The first line of the physical address of this location.
     */
    public $line1;

    /**
     * @var string The second line of the physical address of this location.
     */
    public $line2;

    /**
     * @var string The third line of the physical address of this location.
     */
    public $line3;

    /**
     * @var string The city of the physical address of this location.
     */
    public $city;

    /**
     * @var string The county name of the physical address of this location. Not required.
     */
    public $county;

    /**
     * @var string The state, region, or province of the physical address of this location.
     */
    public $region;

    /**
     * @var string The postal code or zip code of the physical address of this location.
     */
    public $postalCode;

    /**
     * @var string The two character ISO-3166 country code of the physical address of this location.
     */
    public $country;

    /**
     * @var boolean Set this flag to true to indicate that this is the default location for this company.
     */
    public $isDefault;

    /**
     * @var boolean Set this flag to true to indicate that this location has been registered with a tax authority.
     */
    public $isRegistered;

    /**
     * @var string If this location has a different business name from its legal entity name, specify the "Doing Business As" name for this location.
     */
    public $dbaName;

    /**
     * @var string A friendly name for this location.
     */
    public $outletName;

    /**
     * @var string The date when this location was opened for business, or null if not known.
     */
    public $effectiveDate;

    /**
     * @var string If this place of business has closed, the date when this location closed business.
     */
    public $endDate;

    /**
     * @var string The most recent date when a transaction was processed for this location. Set by AvaTax.
     */
    public $lastTransactionDate;

    /**
     * @var string The date when this location was registered with a tax authority. Not required.
     */
    public $registeredDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var LocationSettingModel[] Extra information required by certain jurisdictions for filing. For a list of settings recognized by Avalara, query the endpoint "/api/v2/definitions/locationquestions".  To determine the list of settings required for this location, query the endpoint "/api/v2/companies/(id)/locations/(id)/validate".
     */
    public $settings;

}

/**
 * Represents a declaration of nexus within a particular taxing jurisdiction.
 */
class NexusModel
{

    /**
     * @var int The unique ID number of this declaration of nexus.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that declared nexus.
     */
    public $companyId;

    /**
     * @var string The two character ISO-3166 country code of the country in which this company declared nexus.
     */
    public $country;

    /**
     * @var string The two or three character ISO region code of the region, state, or province in which this company declared nexus.
     */
    public $region;

    /**
     * @var string The jurisdiction type of the jurisdiction in which this company declared nexus. (See JurisTypeId::* for a list of allowable values)
     */
    public $jurisTypeId;

    /**
     * @var string The code identifying the jurisdiction in which this company declared nexus.
     */
    public $jurisCode;

    /**
     * @var string The common name of the jurisdiction in which this company declared nexus.
     */
    public $jurisName;

    /**
     * @var string The date when this nexus began. If not known, set to null.
     */
    public $effectiveDate;

    /**
     * @var string If this nexus will end or has ended on a specific date, set this to the date when this nexus ends.
     */
    public $endDate;

    /**
     * @var string The short name of the jurisdiction.
     */
    public $shortName;

    /**
     * @var string The signature code of the boundary region as defined by Avalara.
     */
    public $signatureCode;

    /**
     * @var string The state assigned number of this jurisdiction.
     */
    public $stateAssignedNo;

    /**
     * @var string The type of nexus that this company is declaring. (See NexusTypeId::* for a list of allowable values)
     */
    public $nexusTypeId;

    /**
     * @var string Indicates whether this nexus is defined as origin or destination nexus. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var boolean True if you are also declaring local nexus within this jurisdiction. Many U.S. states have options for declaring nexus in local jurisdictions as well as within the state.
     */
    public $hasLocalNexus;

    /**
     * @var string If you are declaring local nexus within this jurisdiction, this indicates whether you are declaring only  a specified list of local jurisdictions, all state-administered local jurisdictions, or all local jurisdictions. (See LocalNexusTypeId::* for a list of allowable values)
     */
    public $localNexusTypeId;

    /**
     * @var boolean Set this value to true if your company has a permanent establishment within this jurisdiction.
     */
    public $hasPermanentEstablishment;

    /**
     * @var string Optional - the tax identification number under which you declared nexus.
     */
    public $taxId;

    /**
     * @var boolean For the United States, this flag indicates whether this particular nexus falls within a U.S. State that participates  in the Streamlined Sales Tax program. For countries other than the US, this flag is null.
     */
    public $streamlinedSalesTax;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * This object is used to keep track of custom information about a company.
 * A setting can refer to any type of data you need to remember about this company object.
 * When creating this object, you may define your own "set", "name", and "value" parameters.
 * To define your own values, please choose a "set" name that begins with "X-" to indicate an extension.
 */
class SettingModel
{

    /**
     * @var int The unique ID number of this setting.
     */
    public $id;

    /**
     * @var int The unique ID number of the company this setting refers to.
     */
    public $companyId;

    /**
     * @var string A user-defined "set" containing this name-value pair.
     */
    public $set;

    /**
     * @var string A user-defined "name" for this name-value pair.
     */
    public $name;

    /**
     * @var string The value of this name-value pair.
     */
    public $value;

}

/**
 * Represents a tax code that can be applied to items on a transaction.
 * A tax code can have specific rules for specific jurisdictions that change the tax calculation behavior.
 */
class TaxCodeModel
{

    /**
     * @var int The unique ID number of this tax code.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that owns this tax code.
     */
    public $companyId;

    /**
     * @var string A code string that identifies this tax code.
     */
    public $taxCode;

    /**
     * @var string The type of this tax code.
     */
    public $taxCodeTypeId;

    /**
     * @var string A friendly description of this tax code.
     */
    public $description;

    /**
     * @var string If this tax code is a subset of a different tax code, this identifies the parent code.
     */
    public $parentTaxCode;

    /**
     * @var boolean True if this tax code refers to a physical object.
     */
    public $isPhysical;

    /**
     * @var int The Avalara Goods and Service Code represented by this tax code.
     */
    public $goodsServiceCode;

    /**
     * @var string The Avalara Entity Use Code represented by this tax code.
     */
    public $entityUseCode;

    /**
     * @var boolean True if this tax code is active and can be used in transactions.
     */
    public $isActive;

    /**
     * @var boolean True if this tax code has been certified by the Streamlined Sales Tax governing board. By default, you should leave this value empty.
     */
    public $isSSTCertified;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents a tax rule that changes the behavior of Avalara's tax engine for certain products in certain jurisdictions.
 */
class TaxRuleModel
{

    /**
     * @var int The unique ID number of this tax rule.
     */
    public $id;

    /**
     * @var int The unique ID number of the company that owns this tax rule.
     */
    public $companyId;

    /**
     * @var int The unique ID number of the tax code for this rule. When creating or updating a tax rule, you may specify either the taxCodeId value or the taxCode value.
     */
    public $taxCodeId;

    /**
     * @var string The code string of the tax code for this rule. When creating or updating a tax rule, you may specify either the taxCodeId value or the taxCode value.
     */
    public $taxCode;

    /**
     * @var string For U.S. tax rules, this is the state's Federal Information Processing Standard (FIPS) code.
     */
    public $stateFIPS;

    /**
     * @var string The name of the jurisdiction to which this tax rule applies.
     */
    public $jurisName;

    /**
     * @var string The code of the jurisdiction to which this tax rule applies.
     */
    public $jurisCode;

    /**
     * @var string The type of the jurisdiction to which this tax rule applies. (See JurisTypeId::* for a list of allowable values)
     */
    public $jurisTypeId;

    /**
     * @var string The type of customer usage to which this rule applies.
     */
    public $customerUsageType;

    /**
     * @var string Indicates which tax types to which this rule applies. (See MatchingTaxType::* for a list of allowable values)
     */
    public $taxTypeId;

    /**
     * @var string Indicates the rate type to which this rule applies.
     */
    public $rateTypeId;

    /**
     * @var string This type value determines the behavior of the tax rule. You can specify that this rule controls the product's taxability or exempt / nontaxable status, the product's rate  (for example, if you have been granted an official ruling for your product's rate that differs from the official rate),  or other types of behavior. (See TaxRuleTypeId::* for a list of allowable values)
     */
    public $taxRuleTypeId;

    /**
     * @var boolean Set this value to true if this tax rule applies in all jurisdictions.
     */
    public $isAllJuris;

    /**
     * @var float The corrected rate for this tax rule.
     */
    public $value;

    /**
     * @var float The maximum cap for the price of this item according to this rule.
     */
    public $cap;

    /**
     * @var float The per-unit threshold that must be met before this rule applies.
     */
    public $threshold;

    /**
     * @var string Custom option flags for this rule.
     */
    public $options;

    /**
     * @var string The first date at which this rule applies. If null, this rule will apply to all dates prior to the end date.
     */
    public $effectiveDate;

    /**
     * @var string The last date for which this rule applies. If null, this rule will apply to all dates after the effective date.
     */
    public $endDate;

    /**
     * @var string A friendly name for this tax rule.
     */
    public $description;

    /**
     * @var string For U.S. tax rules, this is the county's Federal Information Processing Standard (FIPS) code.
     */
    public $countyFIPS;

    /**
     * @var boolean If true, indicates this rule is for Sales Tax Pro.
     */
    public $isSTPro;

    /**
     * @var string The two character ISO 3166 country code for the locations where this rule applies.
     */
    public $country;

    /**
     * @var string The state, region, or province name for the locations where this rule applies.
     */
    public $region;

    /**
     * @var string The sourcing types to which this rule applies. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * One Universal Product Code object as defined for your company.
 */
class UPCModel
{

    /**
     * @var int The unique ID number for this UPC.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this UPC belongs.
     */
    public $companyId;

    /**
     * @var string The 12-14 character Universal Product Code, European Article Number, or Global Trade Identification Number.
     */
    public $upc;

    /**
     * @var string Legacy Tax Code applied to any product sold with this UPC.
     */
    public $legacyTaxCode;

    /**
     * @var string Description of the product to which this UPC applies.
     */
    public $description;

    /**
     * @var string If this UPC became effective on a certain date, this contains the first date on which the UPC was effective.
     */
    public $effectiveDate;

    /**
     * @var string If this UPC expired or will expire on a certain date, this contains the last date on which the UPC was effective.
     */
    public $endDate;

    /**
     * @var int A usage identifier for this UPC code.
     */
    public $usage;

    /**
     * @var int A flag indicating whether this UPC code is attached to the AvaTax system or to a company.
     */
    public $isSystem;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents the answer to one local jurisdiction question for a location.
 */
class LocationSettingModel
{

    /**
     * @var int The unique ID number of the location question answered.
     */
    public $questionId;

    /**
     * @var string The answer the user provided.
     */
    public $value;

}

/**
 * Company Initialization Model
 */
class CompanyInitializationModel
{

    /**
     * @var string Company Name
     */
    public $name;

    /**
     * @var string Company Code - used to distinguish between companies within your accounting system
     */
    public $companyCode;

    /**
     * @var string Vat Registration Id - leave blank if not known.
     */
    public $vatRegistrationId;

    /**
     * @var string United States Taxpayer ID number, usually your Employer Identification Number if you are a business or your  Social Security Number if you are an individual. This value is required if you subscribe to Avalara Managed Returns or the SST Certified Service Provider services,  but it is optional if you do not subscribe to either of those services.
     */
    public $taxpayerIdNumber;

    /**
     * @var string Address Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string Two character ISO 3166 Region code for this company's primary business location.
     */
    public $region;

    /**
     * @var string Postal Code
     */
    public $postalCode;

    /**
     * @var string Two character ISO 3166 Country code for this company's primary business location.
     */
    public $country;

    /**
     * @var string First Name
     */
    public $firstName;

    /**
     * @var string Last Name
     */
    public $lastName;

    /**
     * @var string Title
     */
    public $title;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string Phone Number
     */
    public $phoneNumber;

    /**
     * @var string Mobile Number
     */
    public $mobileNumber;

    /**
     * @var string Fax Number
     */
    public $faxNumber;

}

/**
 * Status of an Avalara Managed Returns funding configuration for a company
 */
class FundingStatusModel
{

    /**
     * @var int The unique ID number of this funding request
     */
    public $requestId;

    /**
     * @var int SubledgerProfileID
     */
    public $subledgerProfileID;

    /**
     * @var string CompanyID
     */
    public $companyID;

    /**
     * @var string Domain
     */
    public $domain;

    /**
     * @var string Recipient
     */
    public $recipient;

    /**
     * @var string Sender
     */
    public $sender;

    /**
     * @var string DocumentKey
     */
    public $documentKey;

    /**
     * @var string DocumentType
     */
    public $documentType;

    /**
     * @var string DocumentName
     */
    public $documentName;

    /**
     * @var FundingESignMethodReturn MethodReturn
     */
    public $methodReturn;

    /**
     * @var string Status
     */
    public $status;

    /**
     * @var string ErrorMessage
     */
    public $errorMessage;

    /**
     * @var string LastPolled
     */
    public $lastPolled;

    /**
     * @var string LastSigned
     */
    public $lastSigned;

    /**
     * @var string LastActivated
     */
    public $lastActivated;

    /**
     * @var int TemplateRequestId
     */
    public $templateRequestId;

}

/**
 * Represents the current status of a funding ESign method
 */
class FundingESignMethodReturn
{

    /**
     * @var string Method
     */
    public $method;

    /**
     * @var boolean JavaScriptReady
     */
    public $javaScriptReady;

    /**
     * @var string The actual javascript to use to render this object
     */
    public $javaScript;

}

/**
 * 
 */
class FundingInitiateModel
{

    /**
     * @var boolean Set this value to true to request an email to the recipient
     */
    public $requestEmail;

    /**
     * @var string If you have requested an email for funding setup, this is the recipient who will receive an  email inviting them to setup funding configuration for Avalara Managed Returns. The recipient can then click on a link in the email and setup funding configuration for this company.
     */
    public $fundingEmailRecipient;

    /**
     * @var boolean Set this value to true to request an HTML-based funding widget that can be embedded within an  existing user interface. A user can then interact with the HTML-based funding widget to set up funding information for the company.
     */
    public $requestWidget;

}

/**
 * Identifies all nexus that match a particular tax form
 */
class NexusByTaxFormModel
{

    /**
     * @var string The code of the tax form that was requested
     */
    public $formCode;

    /**
     * @var int The company ID of the company that was used to load the companyNexus array. If this value is null, no company data was loaded.
     */
    public $companyId;

    /**
     * @var NexusModel[] A list of all Avalara-defined nexus that are relevant to this tax form
     */
    public $nexusDefinitions;

    /**
     * @var NexusModel[] A list of all currently-defined company nexus that are related to this tax form
     */
    public $companyNexus;

}

/**
 * Information about Avalara-defined tax code types.
 * This list is used when creating tax codes and tax rules.
 */
class TaxCodeTypesModel
{

    /**
     * @var object The list of Avalara-defined tax code types.
     */
    public $types;

}

/**
 * Represents a service or a subscription type.
 */
class SubscriptionTypeModel
{

    /**
     * @var int The unique ID number of this subscription type.
     */
    public $id;

    /**
     * @var string The friendly name of the service this subscription type represents.
     */
    public $description;

}

/**
 * Represents a single security role.
 */
class SecurityRoleModel
{

    /**
     * @var int The unique ID number of this security role.
     */
    public $id;

    /**
     * @var string A description of this security role
     */
    public $description;

}

/**
 * Tax Authority Model
 */
class TaxAuthorityModel
{

    /**
     * @var int The unique ID number of this tax authority.
     */
    public $id;

    /**
     * @var string The friendly name of this tax authority.
     */
    public $name;

    /**
     * @var int The type of this tax authority.
     */
    public $taxAuthorityTypeId;

    /**
     * @var int The unique ID number of the jurisdiction for this tax authority.
     */
    public $jurisdictionId;

}

/**
 * Represents a form that can be filed with a tax authority.
 */
class TaxAuthorityFormModel
{

    /**
     * @var int The unique ID number of the tax authority.
     */
    public $taxAuthorityId;

    /**
     * @var string The form name of the form for this tax authority.
     */
    public $formName;

}

/**
 * An extra property that can change the behavior of tax transactions.
 */
class ParameterModel
{

    /**
     * @var int The unique ID number of this property.
     */
    public $id;

    /**
     * @var string The service category of this property. Some properties may require that you subscribe to certain features of avatax before they can be used.
     */
    public $category;

    /**
     * @var string The name of the property. To use this property, add a field on the "properties" object of a /api/v2/companies/(code)/transactions/create call.
     */
    public $name;

    /**
     * @var string The data type of the property. (See ParameterBagDataType::* for a list of allowable values)
     */
    public $dataType;

    /**
     * @var string A full description of this property.
     */
    public $description;

}

/**
 * Information about questions that the local jurisdictions require for each location
 */
class LocationQuestionModel
{

    /**
     * @var int The unique ID number of this location setting type
     */
    public $id;

    /**
     * @var string This is the prompt for this question
     */
    public $question;

    /**
     * @var string If additional information is available about the location setting, this contains descriptive text to help you identify the correct value to provide in this setting.
     */
    public $description;

    /**
     * @var string If available, this regular expression will verify that the input from the user is in the expected format.
     */
    public $regularExpression;

    /**
     * @var string If available, this is an example value that you can demonstrate to the user to show what is expected.
     */
    public $exampleValue;

    /**
     * @var string Indicates which jurisdiction requires this question
     */
    public $jurisdictionName;

    /**
     * @var string Indicates which type of jurisdiction requires this question (See JurisdictionType::* for a list of allowable values)
     */
    public $jurisdictionType;

    /**
     * @var string Indicates the country that this jurisdiction belongs to
     */
    public $jurisdictionCountry;

    /**
     * @var string Indicates the state, region, or province that this jurisdiction belongs to
     */
    public $jurisdictionRegion;

}

/**
 * Represents an ISO 3166 recognized country
 */
class IsoCountryModel
{

    /**
     * @var string The two character ISO 3166 country code
     */
    public $code;

    /**
     * @var string The full name of this country as it is known in US English
     */
    public $name;

    /**
     * @var boolean True if this country is a member of the European Union
     */
    public $isEuropeanUnion;

}

/**
 * Represents a region, province, or state within a country
 */
class IsoRegionModel
{

    /**
     * @var string The two-character ISO 3166 country code this region belongs to
     */
    public $countryCode;

    /**
     * @var string The three character ISO 3166 region code
     */
    public $code;

    /**
     * @var string The full name, using localized characters, for this region
     */
    public $name;

    /**
     * @var string The word in the local language that classifies what type of a region this represents
     */
    public $classification;

    /**
     * @var boolean For the United States, this flag indicates whether a U.S. State participates in the Streamlined Sales Tax program. For countries other than the US, this flag is null.
     */
    public $streamlinedSalesTax;

}

/**
 * Represents a code describing the intended use for a product that may affect its taxability
 */
class EntityUseCodeModel
{

    /**
     * @var string The Avalara-recognized entity use code for this definition
     */
    public $code;

    /**
     * @var string The name of this entity use code
     */
    public $name;

    /**
     * @var string Text describing the meaning of this use code
     */
    public $description;

    /**
     * @var string[] A list of countries where this use code is valid
     */
    public $validCountries;

}

/**
 * Tax Authority Type Model
 */
class TaxAuthorityTypeModel
{

    /**
     * @var int The unique ID number of this tax Authority customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var string Tax Authority Group
     */
    public $taxAuthorityGroup;

}

/**
 * Tax Notice Status Model
 */
class NoticeStatusModel
{

    /**
     * @var int The unique ID number of this tax authority type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean True if a tax notice in this status is considered 'open' and has more work expected to be done before it is closed.
     */
    public $isOpen;

    /**
     * @var int If a list of status values is to be displayed in a dropdown, they should be displayed in this numeric order.
     */
    public $sortOrder;

}

/**
 * Tax Authority Model
 */
class NoticeCustomerTypeModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * Tax Notice Reason Model
 */
class NoticeReasonModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * FilingFrequency Model
 */
class FilingFrequencyModel
{

    /**
     * @var int The unique ID number of this filing frequency.
     */
    public $id;

    /**
     * @var string The description name of this filing frequency
     */
    public $description;

}

/**
 * Tax Notice FilingType Model
 */
class NoticeFilingTypeModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * Tax Notice Type Model
 */
class NoticeTypeModel
{

    /**
     * @var int The unique ID number of this tax notice customer type.
     */
    public $id;

    /**
     * @var string The description name of this tax authority type.
     */
    public $description;

    /**
     * @var boolean A flag if the type is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the types
     */
    public $sortOrder;

}

/**
 * Tax Authority Model
 */
class NoticeCustomerFundingOptionModel
{

    /**
     * @var int The unique ID number of this tax notice customer FundingOption.
     */
    public $id;

    /**
     * @var string The description name of this tax authority FundingOption.
     */
    public $description;

    /**
     * @var boolean A flag if the FundingOption is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the FundingOptions
     */
    public $sortOrder;

}

/**
 * Tax Notice Priority Model
 */
class NoticePriorityModel
{

    /**
     * @var int The unique ID number of this tax notice customer Priority.
     */
    public $id;

    /**
     * @var string The description name of this tax authority Priority.
     */
    public $description;

    /**
     * @var boolean A flag if the Priority is active
     */
    public $activeFlag;

    /**
     * @var int sort order of the Prioritys
     */
    public $sortOrder;

}

/**
 * NoticeResponsibility Model
 */
class NoticeResponsibilityModel
{

    /**
     * @var int The unique ID number of this notice responsibility.
     */
    public $id;

    /**
     * @var string The description name of this notice responsibility
     */
    public $description;

    /**
     * @var boolean Defines if the responsibility is active
     */
    public $isActive;

    /**
     * @var int The sort order of this responsibility
     */
    public $sortOrder;

}

/**
 * NoticeRootCause Model
 */
class NoticeRootCauseModel
{

    /**
     * @var int The unique ID number of this notice RootCause.
     */
    public $id;

    /**
     * @var string The description name of this notice RootCause
     */
    public $description;

    /**
     * @var boolean Defines if the RootCause is active
     */
    public $isActive;

    /**
     * @var int The sort order of this RootCause
     */
    public $sortOrder;

}

/**
 * Represents a list of statuses of returns available in skyscraper
 */
class SkyscraperStatusModel
{

    /**
     * @var string The specific name of the returns available in skyscraper
     */
    public $name;

    /**
     * @var string[] The tax form codes available to file through skyscrper
     */
    public $taxFormCodes;

    /**
     * @var string The country of the returns
     */
    public $country;

    /**
     * @var string They Scraper type (See ScraperType::* for a list of allowable values)
     */
    public $scraperType;

    /**
     * @var boolean Indicates if the return is currently available
     */
    public $isAvailable;

    /**
     * @var string The expected response time of the call
     */
    public $expectedResponseTime;

    /**
     * @var string Message on the returns
     */
    public $message;

    /**
     * @var requiredFilingCalendarDataFieldModel[] A list of required fields to file
     */
    public $requiredFilingCalendarDataFields;

}

/**
 * Represents a verification request using Skyscraper for a company
 */
class requiredFilingCalendarDataFieldModel
{

    /**
     * @var string Region of the verification request
     */
    public $name;

    /**
     * @var string Username that we are using for verification
     */
    public $description;

}

/**
 * Represents an override of tax jurisdictions for a specific address.
 * 
 * During the time period represented by EffDate through EndDate, all tax decisions for addresses matching
 * this override object will be assigned to the list of jurisdictions designated in this object.
 */
class JurisdictionOverrideModel
{

    /**
     * @var int The unique ID number of this override.
     */
    public $id;

    /**
     * @var int The unique ID number assigned to this account.
     */
    public $accountId;

    /**
     * @var string A description of why this jurisdiction override was created.
     */
    public $description;

    /**
     * @var string The street address of the physical location affected by this override.
     */
    public $line1;

    /**
     * @var string The city address of the physical location affected by this override.
     */
    public $city;

    /**
     * @var string The two or three character ISO region code of the region, state, or province affected by this override.
     */
    public $region;

    /**
     * @var string The two character ISO-3166 country code of the country affected by this override. Note that only United States addresses are affected by the jurisdiction override system.
     */
    public $country;

    /**
     * @var string The postal code of the physical location affected by this override.
     */
    public $postalCode;

    /**
     * @var string The date when this override first takes effect. Set this value to null to affect all dates up to the end date.
     */
    public $effectiveDate;

    /**
     * @var string The date when this override will cease to take effect. Set this value to null to never expire.
     */
    public $endDate;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var JurisdictionModel[] A list of the tax jurisdictions that will be assigned to this overridden address.
     */
    public $jurisdictions;

    /**
     * @var int The TaxRegionId of the new location affected by this jurisdiction override.
     */
    public $taxRegionId;

    /**
     * @var string The boundary level of this override (See BoundaryLevel::* for a list of allowable values)
     */
    public $boundaryLevel;

    /**
     * @var boolean True if this is a default boundary
     */
    public $isDefault;

}

/**
 * Represents information about a single legal taxing jurisdiction
 */
class JurisdictionModel
{

    /**
     * @var string The code that is used to identify this jurisdiction
     */
    public $code;

    /**
     * @var string The name of this jurisdiction
     */
    public $name;

    /**
     * @var string The type of the jurisdiction, indicating whether it is a country, state/region, city, for example. (See JurisdictionType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var float The base rate of tax specific to this jurisdiction.
     */
    public $rate;

    /**
     * @var float The "Sales" tax rate specific to this jurisdiction.
     */
    public $salesRate;

    /**
     * @var string The Avalara-supplied signature code for this jurisdiction.
     */
    public $signatureCode;

    /**
     * @var string The state assigned code for this jurisdiction, if any.
     */
    public $region;

    /**
     * @var float The "Seller's Use" tax rate specific to this jurisdiction.
     */
    public $useRate;

}

/**
 * Resource File Type Model
 */
class ResourceFileTypeModel
{

    /**
     * @var int The resource file type id
     */
    public $resourceFileTypeId;

    /**
     * @var string The name of the file type
     */
    public $name;

}

/**
 * Rate type Model
 */
class RateTypeModel
{

    /**
     * @var string The unique ID number of this tax authority.
     */
    public $id;

    /**
     * @var string Description of this rate type.
     */
    public $description;

    /**
     * @var string Country code for this rate type
     */
    public $country;

}

/**
 * Represents a commitment to file a tax return on a recurring basis.
 * Only used if you subscribe to Avalara Returns.
 */
class FilingCalendarModel
{

    /**
     * @var int The unique ID number of this filing calendar.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this filing calendar belongs.
     */
    public $companyId;

    /**
     * @var string The name of the tax form to file.
     */
    public $returnName;

    /**
     * @var string If this calendar is for a location-specific tax return, specify the location code here. To file for all locations, leave this value NULL.
     */
    public $locationCode;

    /**
     * @var string If this calendar is for a location-specific tax return, specify the location-specific behavior here. (See OutletTypeId::* for a list of allowable values)
     */
    public $outletTypeId;

    /**
     * @var string Specify the ISO 4217 currency code for the currency to remit for this tax return. For all tax returns in the United States, specify "USD".
     */
    public $paymentCurrency;

    /**
     * @var string The frequency on which this tax form is filed. (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequencyId;

    /**
     * @var int A 16-bit bitmap containing a 1 for each month when the return should be filed.
     */
    public $months;

    /**
     * @var string Tax Registration ID for this Region - in the U.S., this is for your state.
     */
    public $stateRegistrationId;

    /**
     * @var string Tax Registration ID for the local jurisdiction, if any.
     */
    public $localRegistrationId;

    /**
     * @var string The Employer Identification Number or Taxpayer Identification Number that is to be used when filing this return.
     */
    public $employerIdentificationNumber;

    /**
     * @var string The first line of the physical address to be used when filing this tax return.
     */
    public $line1;

    /**
     * @var string The second line of the physical address to be used when filing this tax return. Please note that some tax forms do not support multiple address lines.
     */
    public $line2;

    /**
     * @var string The city name of the physical address to be used when filing this tax return.
     */
    public $city;

    /**
     * @var string The state, region, or province of the physical address to be used when filing this tax return.
     */
    public $region;

    /**
     * @var string The postal code or zip code of the physical address to be used when filing this tax return.
     */
    public $postalCode;

    /**
     * @var string The two character ISO-3166 country code of the physical address to be used when filing this return.
     */
    public $country;

    /**
     * @var string The phone number to be used when filing this return.
     */
    public $phone;

    /**
     * @var string Special filing instructions to be used when filing this return. Please note that requesting special filing instructions may incur additional costs.
     */
    public $customerFilingInstructions;

    /**
     * @var string The legal entity name to be used when filing this return.
     */
    public $legalEntityName;

    /**
     * @var string The earliest date for the tax period when this return should be filed. This date specifies the earliest date for tax transactions that should be reported on this filing calendar. Please note that tax is usually filed one month in arrears: for example, tax for January transactions is typically filed during the month of February.
     */
    public $effectiveDate;

    /**
     * @var string The last date for the tax period when this return should be filed. This date specifies the last date for tax transactions that should be reported on this filing calendar. Please note that tax is usually filed one month in arrears: for example, tax for January transactions is typically filed during the month of February.
     */
    public $endDate;

    /**
     * @var string The method to be used when filing this return. (See FilingTypeId::* for a list of allowable values)
     */
    public $filingTypeId;

    /**
     * @var string If you file electronically, this is the username you use to log in to the tax authority's website.
     */
    public $eFileUsername;

    /**
     * @var string If you file electronically, this is the password or pass code you use to log in to the tax authority's website.
     */
    public $eFilePassword;

    /**
     * @var int If you are required to prepay a percentage of taxes for future periods, please specify the percentage in whole numbers;  for example, the value 90 would indicate 90%.
     */
    public $prepayPercentage;

    /**
     * @var string The type of tax to report on this return. (See MatchingTaxType::* for a list of allowable values)
     */
    public $taxTypeId;

    /**
     * @var string Internal filing notes.
     */
    public $internalNotes;

    /**
     * @var string Custom filing information field for Alabama.
     */
    public $alSignOn;

    /**
     * @var string Custom filing information field for Alabama.
     */
    public $alAccessCode;

    /**
     * @var string Custom filing information field for Maine.
     */
    public $meBusinessCode;

    /**
     * @var string Custom filing information field for Iowa.
     */
    public $iaBen;

    /**
     * @var string Custom filing information field for Connecticut.
     */
    public $ctReg;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other1Name;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other1Value;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other2Name;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other2Value;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other3Name;

    /**
     * @var string Custom filing information field. Leave blank.
     */
    public $other3Value;

    /**
     * @var int The unique ID of the tax authority of this return.
     */
    public $taxAuthorityId;

    /**
     * @var string The name of the tax authority of this return.
     */
    public $taxAuthorityName;

    /**
     * @var string The type description of the tax authority of this return.
     */
    public $taxAuthorityType;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Model with options for adding a new filing calendar
 */
class CycleAddOptionModel
{

    /**
     * @var boolean True if this form can be added and filed for the current cycle. "Current cycle" is considered one month before the month of today's date.
     */
    public $available;

    /**
     * @var string The period start date for the customer's first transaction in the jurisdiction being added
     */
    public $transactionalPeriodStart;

    /**
     * @var string The period end date for the customer's last transaction in the jurisdiction being added
     */
    public $transactionalPeriodEnd;

    /**
     * @var string The jurisdiction-assigned due date for the form
     */
    public $filingDueDate;

    /**
     * @var string A descriptive name of the cycle and due date of form.
     */
    public $cycleName;

    /**
     * @var string The filing frequency of the form
     */
    public $frequencyName;

    /**
     * @var string A code assigned to the filing frequency
     */
    public $filingFrequencyCode;

    /**
     * @var string The filing frequency of the request (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequencyId;

    /**
     * @var string An explanation for why this form cannot be added for the current cycle
     */
    public $cycleUnavailableReason;

    /**
     * @var string[] A list of outlet codes that can be assigned to this form for the current cycle
     */
    public $availableLocationCodes;

}

/**
 * Cycle Safe Expiration results.
 */
class CycleExpireModel
{

    /**
     * @var boolean Whether or not the filing calendar can be expired. e.g. if user makes end date of a calendar earlier than latest filing, this would be set to false.
     */
    public $success;

    /**
     * @var string The message to present to the user if expiration is successful or unsuccessful.
     */
    public $message;

    /**
     * @var CycleExpireOptionModel[] A list of options for expiring the filing calendar.
     */
    public $cycleExpirationOptions;

}

/**
 * Options for expiring a filing calendar.
 */
class CycleExpireOptionModel
{

    /**
     * @var string The period start date for the customer's first transaction in the jurisdiction being expired.
     */
    public $transactionalPeriodStart;

    /**
     * @var string The period end date for the customer's last transaction in the jurisdiction being expired.
     */
    public $transactionalPeriodEnd;

    /**
     * @var string The jurisdiction-assigned due date for the form.
     */
    public $filingDueDate;

    /**
     * @var string A descriptive name of the cycle and due date of the form.
     */
    public $cycleName;

}

/**
 * An edit to be made on a filing calendar.
 */
class FilingCalendarEditModel
{

    /**
     * @var string The name of the field to be modified.
     */
    public $fieldName;

    /**
     * @var int The unique ID of the filing calendar question. "Filing calendar question" is the wording displayed to users for a given field.
     */
    public $questionId;

    /**
     * @var object The current value of the field.
     */
    public $oldValue;

    /**
     * @var object The new/proposed value of the field.
     */
    public $newValue;

}

/**
 * Model with options for actual filing calendar output based on user edits to filing calendar.
 */
class CycleEditOptionModel
{

    /**
     * @var boolean Whether or not changes can be made to the filing calendar.
     */
    public $success;

    /**
     * @var string The message to present to the user when calendar is successfully or unsuccessfully changed.
     */
    public $message;

    /**
     * @var boolean Whether or not the user should be warned of a change, because some changes are risky and may be being done not in accordance with jurisdiction rules. For example, user would be warned if user changes filing frequency to new frequency with a start date during an accrual month of the existing frequency.
     */
    public $customerMustApprove;

    /**
     * @var boolean True if the filing calendar must be cloned to allow this change; false if the existing filing calendar can be changed itself.
     */
    public $mustCloneFilingCalendar;

    /**
     * @var string The effective date of the filing calendar (only applies if cloning).
     */
    public $clonedCalendarEffDate;

    /**
     * @var string The expired end date of the old filing calendar (only applies if cloning).
     */
    public $expiredCalendarEndDate;

}

/**
 * Represents a commitment to file a tax return on a recurring basis.
 * Only used if you subscribe to Avalara Returns.
 */
class FilingRequestModel
{

    /**
     * @var int The unique ID number of this filing request.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this filing request belongs.
     */
    public $companyId;

    /**
     * @var string The current status of this request (See FilingRequestStatus::* for a list of allowable values)
     */
    public $filingRequestStatusId;

    /**
     * @var FilingRequestDataModel The data model object of the request
     */
    public $data;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents a commitment to file a tax return on a recurring basis.
 * Only used if you subscribe to Avalara Returns.
 */
class FilingRequestDataModel
{

    /**
     * @var int The company return ID if requesting an update.
     */
    public $companyReturnId;

    /**
     * @var string The return name of the requested calendar
     */
    public $returnName;

    /**
     * @var string The filing frequency of the request (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequencyId;

    /**
     * @var string State registration ID of the company requesting the filing calendar.
     */
    public $registrationId;

    /**
     * @var int The months of the request
     */
    public $months;

    /**
     * @var string The type of tax to report on this return. (See MatchingTaxType::* for a list of allowable values)
     */
    public $taxTypeId;

    /**
     * @var string Location code of the request
     */
    public $locationCode;

    /**
     * @var string Filing cycle effective date of the request
     */
    public $effDate;

    /**
     * @var string Filing cycle end date of the request
     */
    public $endDate;

    /**
     * @var boolean Flag if the request is a clone of a current filing calendar
     */
    public $isClone;

    /**
     * @var string The region this request is for
     */
    public $region;

    /**
     * @var int The tax authority id of the return
     */
    public $taxAuthorityId;

    /**
     * @var string The tax authority name on the return
     */
    public $taxAuthorityName;

    /**
     * @var FilingAnswerModel[] Filing question answers
     */
    public $answers;

}

/**
 * 
 */
class FilingAnswerModel
{

    /**
     * @var int The ID number for a filing question
     */
    public $filingQuestionId;

    /**
     * @var object The value of the answer for the filing question identified by filingQuestionId
     */
    public $answer;

}

/**
 * This is the output model coming from skyscraper services
 */
class LoginVerificationOutputModel
{

    /**
     * @var int The job Id returned from skyscraper
     */
    public $jobId;

    /**
     * @var string The operation status of the job
     */
    public $operationStatus;

    /**
     * @var string The message returned from the job
     */
    public $message;

    /**
     * @var boolean Indicates if the login was successful
     */
    public $loginSuccess;

}

/**
 * Represents a verification request using Skyscraper for a company
 */
class LoginVerificationInputModel
{

    /**
     * @var int CompanyId that we are verifying the login information for
     */
    public $companyId;

    /**
     * @var int AccountId of the login verification
     */
    public $accountId;

    /**
     * @var string Region of the verification request
     */
    public $region;

    /**
     * @var string Username that we are using for verification
     */
    public $username;

    /**
     * @var string Password we are using for verification
     */
    public $password;

    /**
     * @var string Additional options of the verification
     */
    public $additionalOptions;

    /**
     * @var int Bulk Request Id of the verification
     */
    public $bulkRequestId;

    /**
     * @var int Priority of the verification request
     */
    public $priority;

}

/**
 * Represents a listing of all tax calculation data for filings and for accruing to future filings.
 */
class FilingModel
{

    /**
     * @var int The unique ID number of this filing.
     */
    public $id;

    /**
     * @var int The unique ID number of the company for this filing.
     */
    public $companyId;

    /**
     * @var int The month of the filing period for this tax filing.  The filing period represents the year and month of the last day of taxes being reported on this filing.  For example, an annual tax filing for Jan-Dec 2015 would have a filing period of Dec 2015.
     */
    public $month;

    /**
     * @var int The year of the filing period for this tax filing. The filing period represents the year and month of the last day of taxes being reported on this filing.  For example, an annual tax filing for Jan-Dec 2015 would have a filing period of Dec 2015.
     */
    public $year;

    /**
     * @var string Indicates whether this is an original or an amended filing. (See WorksheetTypeId::* for a list of allowable values)
     */
    public $type;

    /**
     * @var FilingRegionModel[] A listing of regional tax filings within this time period.
     */
    public $filingRegions;

}

/**
 * Regions
 */
class FilingRegionModel
{

    /**
     * @var int The unique ID number of this filing region.
     */
    public $id;

    /**
     * @var string The two-character ISO-3166 code for the country.
     */
    public $country;

    /**
     * @var string The two or three character region code for the region.
     */
    public $region;

    /**
     * @var float The sales amount.
     */
    public $salesAmount;

    /**
     * @var float The taxable amount.
     */
    public $taxableAmount;

    /**
     * @var float The tax amount.
     */
    public $taxAmount;

    /**
     * @var float The tax amount due.
     */
    public $taxDueAmount;

    /**
     * @var float Total remittance amount of all returns in region
     */
    public $totalRemittanceAmount;

    /**
     * @var float The non-taxable amount.
     */
    public $nonTaxableAmount;

    /**
     * @var float Consumer use tax liability.
     */
    public $consumerUseTaxAmount;

    /**
     * @var float Consumer use non-taxable amount.
     */
    public $consumerUseNonTaxableAmount;

    /**
     * @var float Consumer use taxable amount.
     */
    public $consumerUseTaxableAmount;

    /**
     * @var string The date the filing region was approved.
     */
    public $approveDate;

    /**
     * @var string The start date for the filing cycle.
     */
    public $startDate;

    /**
     * @var string The end date for the filing cycle.
     */
    public $endDate;

    /**
     * @var boolean Whether or not you have nexus in this region.
     */
    public $hasNexus;

    /**
     * @var string The current status of the filing region. (See FilingStatusId::* for a list of allowable values)
     */
    public $status;

    /**
     * @var FilingReturnModel[] A list of tax returns in this region.
     */
    public $returns;

    /**
     * @var FilingsCheckupSuggestedFormModel[] A list of tax returns in this region.
     */
    public $suggestReturns;

}

/**
 * Returns
 */
class FilingReturnModel
{

    /**
     * @var int The unique ID number of this filing return.
     */
    public $id;

    /**
     * @var int The unique ID number of the filing calendar associated with this return.
     */
    public $filingCalendarId;

    /**
     * @var int Tax Authority ID of this return
     */
    public $taxAuthorityId;

    /**
     * @var string The current status of the filing return. (See FilingStatusId::* for a list of allowable values)
     */
    public $status;

    /**
     * @var string The filing frequency of the return. (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequency;

    /**
     * @var string The date the return was filed by Avalara.
     */
    public $filedDate;

    /**
     * @var float The sales amount.
     */
    public $salesAmount;

    /**
     * @var string The filing type of the return. (See FilingTypeId::* for a list of allowable values)
     */
    public $filingType;

    /**
     * @var string The name of the form.
     */
    public $formName;

    /**
     * @var float The remittance amount of the return.
     */
    public $remitAmount;

    /**
     * @var string The unique code of the form.
     */
    public $formCode;

    /**
     * @var string A description for the return.
     */
    public $description;

    /**
     * @var float The taxable amount.
     */
    public $taxableAmount;

    /**
     * @var float The tax amount.
     */
    public $taxAmount;

    /**
     * @var float The tax due amount.
     */
    public $taxDueAmount;

    /**
     * @var float The non-taxable amount.
     */
    public $nonTaxableAmount;

    /**
     * @var float The non-taxable due amount.
     */
    public $nonTaxableDueAmount;

    /**
     * @var float Consumer use tax liability.
     */
    public $consumerUseTaxAmount;

    /**
     * @var float Consumer use non-taxable amount.
     */
    public $consumerUseNonTaxableAmount;

    /**
     * @var float Consumer use taxable amount.
     */
    public $consumerUseTaxableAmount;

    /**
     * @var float Total amount of adjustments on this return
     */
    public $totalAdjustments;

    /**
     * @var FilingAdjustmentModel[] The Adjustments for this return.
     */
    public $adjustments;

    /**
     * @var float Total amount of augmentations on this return
     */
    public $totalAugmentations;

    /**
     * @var FilingAugmentationModel[] The Augmentations for this return.
     */
    public $augmentations;

    /**
     * @var string Accrual type of the return (See AccrualType::* for a list of allowable values)
     */
    public $accrualType;

}

/**
 * Worksheet Checkup Report Suggested Form Model
 */
class FilingsCheckupSuggestedFormModel
{

    /**
     * @var int Tax Authority ID of the suggested form returned
     */
    public $taxAuthorityId;

    /**
     * @var string Country of the suggested form returned
     */
    public $country;

    /**
     * @var string Region of the suggested form returned
     */
    public $region;

    /**
     * @var string 
     */
    public $returnName;

    /**
     * @var string Name of the suggested form returned
     */
    public $taxFormCode;

}

/**
 * A model for return adjustments.
 */
class FilingAdjustmentModel
{

    /**
     * @var int The unique ID number for the adjustment.
     */
    public $id;

    /**
     * @var float The adjustment amount.
     */
    public $amount;

    /**
     * @var string The filing period the adjustment is applied to. (See AdjustmentPeriodTypeId::* for a list of allowable values)
     */
    public $period;

    /**
     * @var string The type of the adjustment. (See AdjustmentTypeId::* for a list of allowable values)
     */
    public $type;

    /**
     * @var boolean Whether or not the adjustment has been calculated.
     */
    public $isCalculated;

    /**
     * @var string The account type of the adjustment. (See PaymentAccountTypeId::* for a list of allowable values)
     */
    public $accountType;

    /**
     * @var string A descriptive reason for creating this adjustment.
     */
    public $reason;

}

/**
 * A model for return augmentations.
 */
class FilingAugmentationModel
{

    /**
     * @var int The unique ID number for the augmentation.
     */
    public $id;

    /**
     * @var float The field amount.
     */
    public $fieldAmount;

    /**
     * @var string The field name.
     */
    public $fieldName;

}

/**
 * Rebuild a set of filings.
 */
class RebuildFilingsModel
{

    /**
     * @var boolean Set this value to true in order to rebuild the filings.
     */
    public $rebuild;

}

/**
 * Approve a set of filings.
 */
class ApproveFilingsModel
{

    /**
     * @var boolean Set this value to true in order to approve the filings.
     */
    public $approve;

}

/**
 * Results of the Worksheet Checkup report
 */
class FilingsCheckupModel
{

    /**
     * @var FilingsCheckupAuthorityModel[] A collection of authorities in the report
     */
    public $authorities;

}

/**
 * Cycle Safe Expiration results.
 */
class FilingsCheckupAuthorityModel
{

    /**
     * @var int Unique ID of the tax authority
     */
    public $taxAuthorityId;

    /**
     * @var string Location Code of the tax authority
     */
    public $locationCode;

    /**
     * @var string Name of the tax authority
     */
    public $taxAuthorityName;

    /**
     * @var int Type Id of the tax authority
     */
    public $taxAuthorityTypeId;

    /**
     * @var int Jurisdiction Id of the tax authority
     */
    public $jurisdictionId;

    /**
     * @var float Amount of tax collected in this tax authority
     */
    public $tax;

    /**
     * @var string Tax Type collected in the tax authority
     */
    public $taxTypeId;

    /**
     * @var FilingsCheckupSuggestedFormModel[] Suggested forms to file due to tax collected
     */
    public $suggestedForms;

}

/**
 * Tells you whether this location object has been correctly set up to the local jurisdiction's standards
 */
class LocationValidationModel
{

    /**
     * @var boolean True if the location has a value for each jurisdiction-required setting. The user is required to ensure that the values are correct according to the jurisdiction; this flag does not indicate whether the taxing jurisdiction has accepted the data you have provided.
     */
    public $settingsValidated;

    /**
     * @var LocationQuestionModel[] A list of settings that must be defined for this location
     */
    public $requiredSettings;

}

/**
 * Represents a letter received from a tax authority regarding tax filing.
 * These letters often have the warning "Notice" printed at the top, which is why
 * they are called "Notices".
 */
class NoticeModel
{

    /**
     * @var int The unique ID number of this notice.
     */
    public $id;

    /**
     * @var int The unique ID number of the company to which this notice belongs.
     */
    public $companyId;

    /**
     * @var int The status id of the notice
     */
    public $statusId;

    /**
     * @var string The status of the notice
     */
    public $status;

    /**
     * @var string The received date of the notice
     */
    public $receivedDate;

    /**
     * @var string The closed date of the notice
     */
    public $closedDate;

    /**
     * @var string NoticeCustomerTypeID can be retrieved from the definitions API (See NoticeCustomerType::* for a list of allowable values)
     */
    public $customerTypeId;

    /**
     * @var string The country the notice is in
     */
    public $country;

    /**
     * @var string The region the notice is for
     */
    public $region;

    /**
     * @var int The tax authority id of the notice
     */
    public $taxAuthorityId;

    /**
     * @var string The filing frequency of the notice (See FilingFrequencyId::* for a list of allowable values)
     */
    public $filingFrequency;

    /**
     * @var string The filing type of the notice (See FilingTypeId::* for a list of allowable values)
     */
    public $filingTypeId;

    /**
     * @var string The ticket reference number of the notice
     */
    public $ticketReferenceNo;

    /**
     * @var string The ticket reference url of the notice
     */
    public $ticketReferenceUrl;

    /**
     * @var string The sales force case of the notice
     */
    public $salesForceCase;

    /**
     * @var string The URL to the sales force case
     */
    public $salesForceCaseUrl;

    /**
     * @var string The tax period of the notice
     */
    public $taxPeriod;

    /**
     * @var int The notice reason id
     */
    public $reasonId;

    /**
     * @var string The notice reason
     */
    public $reason;

    /**
     * @var int The tax notice type id
     */
    public $typeId;

    /**
     * @var string The tax notice type description
     */
    public $type;

    /**
     * @var string The notice customer funding options (See FundingOption::* for a list of allowable values)
     */
    public $customerFundingOptionId;

    /**
     * @var string The priority of the notice (See NoticePriorityId::* for a list of allowable values)
     */
    public $priorityId;

    /**
     * @var string Comments from the customer on this notice
     */
    public $customerComment;

    /**
     * @var boolean Indicator to hide from customer
     */
    public $hideFromCustomer;

    /**
     * @var string Expected resolution date of the notice
     */
    public $expectedResolutionDate;

    /**
     * @var boolean Indicator to show customer this resolution date
     */
    public $showResolutionDateToCustomer;

    /**
     * @var int The unique ID number of the user that closed the notice
     */
    public $closedByUserId;

    /**
     * @var string The user who created the notice
     */
    public $createdByUserName;

    /**
     * @var int The unique ID number of the user that owns the notice
     */
    public $ownedByUserId;

    /**
     * @var string The description of the notice
     */
    public $description;

    /**
     * @var int The ava file form id of the notice
     */
    public $avaFileFormId;

    /**
     * @var int The id of the revenue contact
     */
    public $revenueContactId;

    /**
     * @var int The id of the compliance contact
     */
    public $complianceContactId;

    /**
     * @var string The document reference of the notice
     */
    public $documentReference;

    /**
     * @var string The jurisdiction name of the notice
     */
    public $jurisdictionName;

    /**
     * @var string The jurisdiction type of the notice
     */
    public $jurisdictionType;

    /**
     * @var NoticeCommentModel[] Additional comments on the notice
     */
    public $comments;

    /**
     * @var NoticeFinanceModel[] Finance details of the notice
     */
    public $finances;

    /**
     * @var NoticeResponsibilityDetailModel[] Notice Responsibility Details
     */
    public $responsibility;

    /**
     * @var NoticeRootCauseDetailModel[] Notice Root Cause Details
     */
    public $rootCause;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

}

/**
 * Represents communication between Avalara and the company regarding the processing of a tax notice.
 */
class NoticeCommentModel
{

    /**
     * @var int The unique ID number of this notice.
     */
    public $id;

    /**
     * @var int 
     */
    public $noticeId;

    /**
     * @var string 
     */
    public $date;

    /**
     * @var string TaxNoticeComment
     */
    public $comment;

    /**
     * @var int TaxNoticeCommentUserId
     */
    public $commentUserId;

    /**
     * @var string TaxNoticeCommentUserName
     */
    public $commentUserName;

    /**
     * @var int taxNoticeCommentTypeId
     */
    public $commentTypeId;

    /**
     * @var string taxNoticeCommentType
     */
    public $commentType;

    /**
     * @var string TaxNoticeCommentLink
     */
    public $commentLink;

    /**
     * @var string TaxNoticeFileName
     */
    public $taxNoticeFileName;

    /**
     * @var int resourceFileId
     */
    public $resourceFileId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var ResourceFileUploadRequestModel An attachment to the detail
     */
    public $attachmentUploadRequest;

}

/**
 * Represents estimated financial results from responding to a tax notice.
 */
class NoticeFinanceModel
{

    /**
     * @var int 
     */
    public $id;

    /**
     * @var int 
     */
    public $noticeId;

    /**
     * @var string 
     */
    public $noticeDate;

    /**
     * @var string 
     */
    public $dueDate;

    /**
     * @var string 
     */
    public $noticeNumber;

    /**
     * @var float 
     */
    public $taxDue;

    /**
     * @var float 
     */
    public $penalty;

    /**
     * @var float 
     */
    public $interest;

    /**
     * @var float 
     */
    public $credits;

    /**
     * @var float 
     */
    public $taxAbated;

    /**
     * @var float 
     */
    public $customerPenalty;

    /**
     * @var float 
     */
    public $customerInterest;

    /**
     * @var float 
     */
    public $cspFeeRefund;

    /**
     * @var string resourceFileId
     */
    public $fileName;

    /**
     * @var int resourceFileId
     */
    public $resourceFileId;

    /**
     * @var string The date when this record was created.
     */
    public $createdDate;

    /**
     * @var int The User ID of the user who created this record.
     */
    public $createdUserId;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var ResourceFileUploadRequestModel An attachment to the finance detail
     */
    public $attachmentUploadRequest;

}

/**
 * NoticeResponsibility Model
 */
class NoticeResponsibilityDetailModel
{

    /**
     * @var int The unique ID number of this filing frequency.
     */
    public $id;

    /**
     * @var int TaxNoticeId
     */
    public $noticeId;

    /**
     * @var int TaxNoticeResponsibilityId
     */
    public $taxNoticeResponsibilityId;

    /**
     * @var string The description name of this filing frequency
     */
    public $description;

}

/**
 * NoticeRootCause Model
 */
class NoticeRootCauseDetailModel
{

    /**
     * @var int The unique ID number of this filing frequency.
     */
    public $id;

    /**
     * @var int TaxNoticeId
     */
    public $noticeId;

    /**
     * @var int TaxNoticeRootCauseId
     */
    public $taxNoticeRootCauseId;

    /**
     * @var string The description name of this root cause
     */
    public $description;

}

/**
 * A request to upload a file to Resource Files
 */
class ResourceFileUploadRequestModel
{

    /**
     * @var string This stream contains the bytes of the file being uploaded. (This value is encoded as a Base64 string)
     */
    public $content;

    /**
     * @var string The username adding the file
     */
    public $username;

    /**
     * @var int The account ID to which this file will be attached.
     */
    public $accountId;

    /**
     * @var int The company ID to which this file will be attached.
     */
    public $companyId;

    /**
     * @var string The original name of this file.
     */
    public $name;

    /**
     * @var int The resource type ID of this file.
     */
    public $resourceFileTypeId;

    /**
     * @var int Length of the file in bytes.
     */
    public $length;

}

/**
 * Password Change Model
 */
class PasswordChangeModel
{

    /**
     * @var string Old Password
     */
    public $oldPassword;

    /**
     * @var string New Password
     */
    public $newPassword;

}

/**
 * Set Password Model
 */
class SetPasswordModel
{

    /**
     * @var string New Password
     */
    public $newPassword;

}

/**
 * Point-of-Sale Data Request Model
 */
class PointOfSaleDataRequestModel
{

    /**
     * @var string A unique code that references a company within your account.
     */
    public $companyCode;

    /**
     * @var string The date associated with the response content. Default is current date. This field can be used to backdate or postdate the response content.
     */
    public $documentDate;

    /**
     * @var string The format of your response. Formats include JSON, CSV, and XML. (See PointOfSaleFileType::* for a list of allowable values)
     */
    public $responseType;

    /**
     * @var string[] A list of tax codes to include in this point-of-sale file. If no tax codes are specified, response will include all distinct tax codes associated with the Items within your company.
     */
    public $taxCodes;

    /**
     * @var string[] A list of location codes to include in this point-of-sale file. If no location codes are specified, response will include all locations within your company.
     */
    public $locationCodes;

    /**
     * @var boolean Set this value to true to include Juris Code in the response.
     */
    public $includeJurisCodes;

    /**
     * @var string A unique code assoicated with the Partner you may be working with. If you are not working with a Partner or your Partner has not provided you an ID, leave null. (See PointOfSalePartnerId::* for a list of allowable values)
     */
    public $partnerId;

}

/**
 * Tax Rate Model
 */
class TaxRateModel
{

    /**
     * @var float Total Rate
     */
    public $totalRate;

    /**
     * @var RateModel[] Rates
     */
    public $rates;

}

/**
 * Rate Model
 */
class RateModel
{

    /**
     * @var float Rate
     */
    public $rate;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Type (See JurisdictionType::* for a list of allowable values)
     */
    public $type;

}

/**
 * A single transaction - for example, a sales invoice or purchase order.
 */
class TransactionModel
{

    /**
     * @var int The unique ID number of this transaction.
     */
    public $id;

    /**
     * @var string A unique customer-provided code identifying this transaction.
     */
    public $code;

    /**
     * @var int The unique ID number of the company that recorded this transaction.
     */
    public $companyId;

    /**
     * @var string The date on which this transaction occurred.
     */
    public $date;

    /**
     * @var string The date when payment was made on this transaction. By default, this should be the same as the date of the transaction.
     */
    public $paymentDate;

    /**
     * @var string The status of the transaction. (See DocumentStatus::* for a list of allowable values)
     */
    public $status;

    /**
     * @var string The type of the transaction. For Returns customers, a transaction type of "Invoice" will be reported to the tax authorities. A sales transaction represents a sale from the company to a customer. A purchase transaction represents a purchase made by the company. A return transaction represents a customer who decided to request a refund after purchasing a product from the company. An inventory  transfer transaction represents goods that were moved from one location of the company to another location without changing ownership. (See DocumentType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string If this transaction was created as part of a batch, this code indicates which batch.
     */
    public $batchCode;

    /**
     * @var string The three-character ISO 4217 currency code that was used for payment for this transaction.
     */
    public $currencyCode;

    /**
     * @var string The customer usage type for this transaction. Customer usage types often affect exemption or taxability rules.
     */
    public $customerUsageType;

    /**
     * @var string CustomerVendorCode
     */
    public $customerVendorCode;

    /**
     * @var string If this transaction was exempt, this field will contain the word "Exempt".
     */
    public $exemptNo;

    /**
     * @var boolean If this transaction has been reconciled against the company's ledger, this value is set to true.
     */
    public $reconciled;

    /**
     * @var string If this transaction was made from a specific reporting location, this is the code string of the location. For customers using Returns, this indicates how tax will be reported according to different locations on the tax forms.
     */
    public $locationCode;

    /**
     * @var string The customer-supplied purchase order number of this transaction.
     */
    public $purchaseOrderNo;

    /**
     * @var string A user-defined reference code for this transaction.
     */
    public $referenceCode;

    /**
     * @var string The salesperson who provided this transaction. Not required.
     */
    public $salespersonCode;

    /**
     * @var string If a tax override was applied to this transaction, indicates what type of tax override was applied. (See TaxOverrideTypeId::* for a list of allowable values)
     */
    public $taxOverrideType;

    /**
     * @var float If a tax override was applied to this transaction, indicates the amount of tax that was requested by the customer.
     */
    public $taxOverrideAmount;

    /**
     * @var string If a tax override was applied to this transaction, indicates the reason for the tax override.
     */
    public $taxOverrideReason;

    /**
     * @var float The total amount of this transaction.
     */
    public $totalAmount;

    /**
     * @var float The amount of this transaction that was exempt.
     */
    public $totalExempt;

    /**
     * @var float The total tax calculated for all lines in this transaction.
     */
    public $totalTax;

    /**
     * @var float The portion of the total amount of this transaction that was taxable.
     */
    public $totalTaxable;

    /**
     * @var float If a tax override was applied to this transaction, indicates the amount of tax Avalara calculated for the transaction.
     */
    public $totalTaxCalculated;

    /**
     * @var string If this transaction was adjusted, indicates the unique ID number of the reason why the transaction was adjusted. (See AdjustmentReason::* for a list of allowable values)
     */
    public $adjustmentReason;

    /**
     * @var string If this transaction was adjusted, indicates a description of the reason why the transaction was adjusted.
     */
    public $adjustmentDescription;

    /**
     * @var boolean If this transaction has been reported to a tax authority, this transaction is considered locked and may not be adjusted after reporting.
     */
    public $locked;

    /**
     * @var string The two-or-three character ISO region code of the region for this transaction.
     */
    public $region;

    /**
     * @var string The two-character ISO 3166 code of the country for this transaction.
     */
    public $country;

    /**
     * @var int If this transaction was adjusted, this indicates the version number of this transaction. Incremented each time the transaction is adjusted.
     */
    public $version;

    /**
     * @var string The software version used to calculate this transaction.
     */
    public $softwareVersion;

    /**
     * @var int The unique ID number of the origin address for this transaction.
     */
    public $originAddressId;

    /**
     * @var int The unique ID number of the destination address for this transaction.
     */
    public $destinationAddressId;

    /**
     * @var string If this transaction included foreign currency exchange, this is the date as of which the exchange rate was calculated.
     */
    public $exchangeRateEffectiveDate;

    /**
     * @var float If this transaction included foreign currency exchange, this is the exchange rate that was used.
     */
    public $exchangeRate;

    /**
     * @var boolean If true, this seller was considered the importer of record of a product shipped internationally.
     */
    public $isSellerImporterOfRecord;

    /**
     * @var string Description of this transaction.
     */
    public $description;

    /**
     * @var string Email address associated with this transaction.
     */
    public $email;

    /**
     * @var string The date/time when this record was last modified.
     */
    public $modifiedDate;

    /**
     * @var int The user ID of the user who last modified this record.
     */
    public $modifiedUserId;

    /**
     * @var string Tax date for this transaction
     */
    public $taxDate;

    /**
     * @var TransactionLineModel[] Optional: A list of line items in this transaction. To fetch this list, add the query string "?$include=Lines" or "?$include=Details" to your URL.
     */
    public $lines;

    /**
     * @var TransactionAddressModel[] Optional: A list of line items in this transaction. To fetch this list, add the query string "?$include=Addresses" to your URL.
     */
    public $addresses;

    /**
     * @var TransactionLocationTypeModel[] Optional: A list of location types in this transaction. To fetch this list, add the query string "?$include=Addresses" to your URL.
     */
    public $locationTypes;

    /**
     * @var TransactionModel[] If this transaction has been adjusted, this list contains all the previous versions of the document.
     */
    public $history;

    /**
     * @var TransactionSummary[] Contains a summary of tax on this transaction.
     */
    public $summary;

    /**
     * @var object Contains a list of extra parameters that were set when the transaction was created.
     */
    public $parameters;

    /**
     * @var AvaTaxMessage[] List of informational and warning messages regarding this API call. These messages are only relevant to the current API call.
     */
    public $messages;

}

/**
 * One line item on this transaction.
 */
class TransactionLineModel
{

    /**
     * @var int The unique ID number of this transaction line item.
     */
    public $id;

    /**
     * @var int The unique ID number of the transaction to which this line item belongs.
     */
    public $transactionId;

    /**
     * @var string The line number or code indicating the line on this invoice or receipt or document.
     */
    public $lineNumber;

    /**
     * @var int The unique ID number of the boundary override applied to this line item.
     */
    public $boundaryOverrideId;

    /**
     * @var string The customer usage type for this line item. Usage type often affects taxability rules.
     */
    public $customerUsageType;

    /**
     * @var string A description of the item or service represented by this line.
     */
    public $description;

    /**
     * @var int The unique ID number of the destination address where this line was delivered or sold. In the case of a point-of-sale transaction, the destination address and origin address will be the same. In the case of a shipped transaction, they will be different.
     */
    public $destinationAddressId;

    /**
     * @var int The unique ID number of the origin address where this line was delivered or sold. In the case of a point-of-sale transaction, the origin address and destination address will be the same. In the case of a shipped transaction, they will be different.
     */
    public $originAddressId;

    /**
     * @var float The amount of discount that was applied to this line item. This represents the difference between list price and sale price of the item. In general, a discount represents money that did not change hands; tax is calculated on only the amount of money that changed hands.
     */
    public $discountAmount;

    /**
     * @var int The type of discount, if any, that was applied to this line item.
     */
    public $discountTypeId;

    /**
     * @var float The amount of this line item that was exempt.
     */
    public $exemptAmount;

    /**
     * @var int The unique ID number of the exemption certificate that applied to this line item.
     */
    public $exemptCertId;

    /**
     * @var string If this line item was exempt, this string contains the word 'Exempt'.
     */
    public $exemptNo;

    /**
     * @var boolean True if this item is taxable.
     */
    public $isItemTaxable;

    /**
     * @var boolean True if this item is a Streamlined Sales Tax line item.
     */
    public $isSSTP;

    /**
     * @var string The code string of the item represented by this line item.
     */
    public $itemCode;

    /**
     * @var float The total amount of the transaction, including both taxable and exempt. This is the total price for all items. To determine the individual item price, divide this by quantity.
     */
    public $lineAmount;

    /**
     * @var float The quantity of products sold on this line item.
     */
    public $quantity;

    /**
     * @var string A user-defined reference identifier for this transaction line item.
     */
    public $ref1;

    /**
     * @var string A user-defined reference identifier for this transaction line item.
     */
    public $ref2;

    /**
     * @var string The date when this transaction should be reported. By default, all transactions are reported on the date when the actual transaction took place. In some cases, line items may be reported later due to delayed shipments or other business reasons.
     */
    public $reportingDate;

    /**
     * @var string The revenue account number for this line item.
     */
    public $revAccount;

    /**
     * @var string Indicates whether this line item was taxed according to the origin or destination. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var float The amount of tax generated for this line item.
     */
    public $tax;

    /**
     * @var float The taxable amount of this line item.
     */
    public $taxableAmount;

    /**
     * @var float The tax calculated for this line by Avalara. If the transaction was calculated with a tax override, this amount will be different from the "tax" value.
     */
    public $taxCalculated;

    /**
     * @var string The code string for the tax code that was used to calculate this line item.
     */
    public $taxCode;

    /**
     * @var int The unique ID number for the tax code that was used to calculate this line item.
     */
    public $taxCodeId;

    /**
     * @var string The date that was used for calculating tax amounts for this line item. By default, this date should be the same as the document date. In some cases, for example when a consumer returns a product purchased previously, line items may be calculated using a tax date in the past so that the consumer can receive a refund for the correct tax amount that was charged when the item was originally purchased.
     */
    public $taxDate;

    /**
     * @var string The tax engine identifier that was used to calculate this line item.
     */
    public $taxEngine;

    /**
     * @var string If a tax override was specified, this indicates the type of tax override. (See TaxOverrideTypeId::* for a list of allowable values)
     */
    public $taxOverrideType;

    /**
     * @var float If a tax override was specified, this indicates the amount of tax that was requested.
     */
    public $taxOverrideAmount;

    /**
     * @var string If a tax override was specified, represents the reason for the tax override.
     */
    public $taxOverrideReason;

    /**
     * @var boolean True if tax was included in the purchase price of the item.
     */
    public $taxIncluded;

    /**
     * @var TransactionLineDetailModel[] Optional: A list of tax details for this line item. To fetch this list, add the query string "?$include=Details" to your URL.
     */
    public $details;

    /**
     * @var TransactionLineLocationTypeModel[] Optional: A list of location types for this line item. To fetch this list, add the query string "?$include=LineLocationTypes" to your URL.
     */
    public $lineLocationTypes;

    /**
     * @var object Contains a list of extra parameters that were set when the transaction was created.
     */
    public $parameters;

}

/**
 * An address used within this transaction.
 */
class TransactionAddressModel
{

    /**
     * @var int The unique ID number of this address.
     */
    public $id;

    /**
     * @var int The unique ID number of the document to which this address belongs.
     */
    public $transactionId;

    /**
     * @var string The boundary level at which this address was validated. (See BoundaryLevel::* for a list of allowable values)
     */
    public $boundaryLevel;

    /**
     * @var string The first line of the address.
     */
    public $line1;

    /**
     * @var string The second line of the address.
     */
    public $line2;

    /**
     * @var string The third line of the address.
     */
    public $line3;

    /**
     * @var string The city for the address.
     */
    public $city;

    /**
     * @var string The region, state, or province for the address.
     */
    public $region;

    /**
     * @var string The postal code or zip code for the address.
     */
    public $postalCode;

    /**
     * @var string The country for the address.
     */
    public $country;

    /**
     * @var int The unique ID number of the tax region for this address.
     */
    public $taxRegionId;

    /**
     * @var string Latitude for this address (CALC - 13394)
     */
    public $latitude;

    /**
     * @var string Longitude for this address (CALC - 13394)
     */
    public $longitude;

}

/**
 * Information about a location type
 */
class TransactionLocationTypeModel
{

    /**
     * @var int Location type ID for this location type in transaction
     */
    public $documentLocationTypeId;

    /**
     * @var int Transaction ID
     */
    public $documentId;

    /**
     * @var int Address ID for the transaction
     */
    public $documentAddressId;

    /**
     * @var string Location type code
     */
    public $locationTypeCode;

}

/**
 * Summary information about an overall transaction.
 */
class TransactionSummary
{

    /**
     * @var string Two character ISO-3166 country code.
     */
    public $country;

    /**
     * @var string Two or three character ISO region, state or province code, if applicable.
     */
    public $region;

    /**
     * @var string The type of jurisdiction that collects this tax. (See JurisdictionType::* for a list of allowable values)
     */
    public $jurisType;

    /**
     * @var string Jurisdiction Code for the taxing jurisdiction
     */
    public $jurisCode;

    /**
     * @var string The name of the jurisdiction that collects this tax.
     */
    public $jurisName;

    /**
     * @var int The unique ID of the Tax Authority Type that collects this tax.
     */
    public $taxAuthorityType;

    /**
     * @var string The state assigned number of the jurisdiction that collects this tax.
     */
    public $stateAssignedNo;

    /**
     * @var string The tax type of this tax. (See TaxType::* for a list of allowable values)
     */
    public $taxType;

    /**
     * @var string The name of the tax.
     */
    public $taxName;

    /**
     * @var string Group code when special grouping is enabled.
     */
    public $taxGroup;

    /**
     * @var string Indicates the tax rate type.
     */
    public $rateType;

    /**
     * @var float Tax Base - The adjusted taxable amount.
     */
    public $taxable;

    /**
     * @var float Tax Rate - The rate of taxation, as a fraction of the amount.
     */
    public $rate;

    /**
     * @var float Tax amount - The calculated tax (Base * Rate).
     */
    public $tax;

    /**
     * @var float Tax Calculated by Avalara AvaTax. This may be overriden by a TaxOverride.TaxAmount.
     */
    public $taxCalculated;

    /**
     * @var float The amount of the transaction that was non-taxable.
     */
    public $nonTaxable;

    /**
     * @var float The amount of the transaction that was exempt.
     */
    public $exemption;

}

/**
 * An individual tax detail element. Represents the amount of tax calculated for a particular jurisdiction, for a particular line in an invoice.
 */
class TransactionLineDetailModel
{

    /**
     * @var int The unique ID number of this tax detail.
     */
    public $id;

    /**
     * @var int The unique ID number of the line within this transaction.
     */
    public $transactionLineId;

    /**
     * @var int The unique ID number of this transaction.
     */
    public $transactionId;

    /**
     * @var int The unique ID number of the address used for this tax detail.
     */
    public $addressId;

    /**
     * @var string The two character ISO 3166 country code of the country where this tax detail is assigned.
     */
    public $country;

    /**
     * @var string The two-or-three character ISO region code for the region where this tax detail is assigned.
     */
    public $region;

    /**
     * @var string For U.S. transactions, the Federal Information Processing Standard (FIPS) code for the county where this tax detail is assigned.
     */
    public $countyFIPS;

    /**
     * @var string For U.S. transactions, the Federal Information Processing Standard (FIPS) code for the state where this tax detail is assigned.
     */
    public $stateFIPS;

    /**
     * @var float The amount of this line that was considered exempt in this tax detail.
     */
    public $exemptAmount;

    /**
     * @var int The unique ID number of the exemption reason for this tax detail.
     */
    public $exemptReasonId;

    /**
     * @var boolean True if this detail element represented an in-state transaction.
     */
    public $inState;

    /**
     * @var string The code of the jurisdiction to which this tax detail applies.
     */
    public $jurisCode;

    /**
     * @var string The name of the jurisdiction to which this tax detail applies.
     */
    public $jurisName;

    /**
     * @var int The unique ID number of the jurisdiction to which this tax detail applies.
     */
    public $jurisdictionId;

    /**
     * @var string The Avalara-specified signature code of the jurisdiction to which this tax detail applies.
     */
    public $signatureCode;

    /**
     * @var string The state assigned number of the jurisdiction to which this tax detail applies.
     */
    public $stateAssignedNo;

    /**
     * @var string The type of the jurisdiction to which this tax detail applies. (See JurisTypeId::* for a list of allowable values)
     */
    public $jurisType;

    /**
     * @var float The amount of this line item that was considered nontaxable in this tax detail.
     */
    public $nonTaxableAmount;

    /**
     * @var int The rule according to which portion of this detail was considered nontaxable.
     */
    public $nonTaxableRuleId;

    /**
     * @var string The type of nontaxability that was applied to this tax detail. (See TaxRuleTypeId::* for a list of allowable values)
     */
    public $nonTaxableType;

    /**
     * @var float The rate at which this tax detail was calculated.
     */
    public $rate;

    /**
     * @var int The unique ID number of the rule according to which this tax detail was calculated.
     */
    public $rateRuleId;

    /**
     * @var int The unique ID number of the source of the rate according to which this tax detail was calculated.
     */
    public $rateSourceId;

    /**
     * @var string For Streamlined Sales Tax customers, the SST Electronic Return code under which this tax detail should be applied.
     */
    public $serCode;

    /**
     * @var string Indicates whether this tax detail applies to the origin or destination of the transaction. (See Sourcing::* for a list of allowable values)
     */
    public $sourcing;

    /**
     * @var float The amount of tax for this tax detail.
     */
    public $tax;

    /**
     * @var float The taxable amount of this tax detail.
     */
    public $taxableAmount;

    /**
     * @var string The type of tax that was calculated. Depends on the company's nexus settings as well as the jurisdiction's tax laws. (See TaxType::* for a list of allowable values)
     */
    public $taxType;

    /**
     * @var string The name of the tax against which this tax amount was calculated.
     */
    public $taxName;

    /**
     * @var int The type of the tax authority to which this tax will be remitted.
     */
    public $taxAuthorityTypeId;

    /**
     * @var int The unique ID number of the tax region.
     */
    public $taxRegionId;

    /**
     * @var float The amount of tax that was calculated. This amount may be different if a tax override was used. If the customer specified a tax override, this calculated tax value represents the amount of tax that would have been charged if Avalara had calculated the tax for the rule.
     */
    public $taxCalculated;

    /**
     * @var float The amount of tax override that was specified for this tax line.
     */
    public $taxOverride;

    /**
     * @var string The rate type for this tax detail.
     */
    public $rateType;

    /**
     * @var float Number of units in this line item that were calculated to be taxable according to this rate detail.
     */
    public $taxableUnits;

    /**
     * @var float Number of units in this line item that were calculated to be nontaxable according to this rate detail.
     */
    public $nonTaxableUnits;

    /**
     * @var float Number of units in this line item that were calculated to be exempt according to this rate detail.
     */
    public $exemptUnits;

    /**
     * @var string When calculating units, what basis of measurement did we use for calculating the units?
     */
    public $unitOfBasis;

}

/**
 * Represents information about location types stored in a line
 */
class TransactionLineLocationTypeModel
{

    /**
     * @var int The unique ID number of this line location address model
     */
    public $documentLineLocationTypeId;

    /**
     * @var int The unique ID number of the document line associated with this line location address model
     */
    public $documentLineId;

    /**
     * @var int The address ID corresponding to this model
     */
    public $documentAddressId;

    /**
     * @var string The location type code corresponding to this model
     */
    public $locationTypeCode;

}

/**
 * A request to adjust tax for a previously existing transaction
 */
class AdjustTransactionModel
{

    /**
     * @var string A reason code indicating why this adjustment was made (See AdjustmentReason::* for a list of allowable values)
     */
    public $adjustmentReason;

    /**
     * @var string If the AdjustmentReason is "Other", specify the reason here
     */
    public $adjustmentDescription;

    /**
     * @var CreateTransactionModel Replace the current transaction with tax data calculated for this new transaction
     */
    public $newTransaction;

}

/**
 * Create a transaction
 */
class CreateTransactionModel
{

    /**
     * @var string Document Type (See DocumentType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var string Transaction Code - the internal reference code used by the client application. This is used for operations such as Get, Adjust, Settle, and Void. If you leave the transaction code blank, a GUID will be assigned to each transaction.
     */
    public $code;

    /**
     * @var string Company Code - Specify the code of the company creating this transaction here. If you leave this value null, your account's default company will be used instead.
     */
    public $companyCode;

    /**
     * @var string Transaction Date - The date on the invoice, purchase order, etc.
     */
    public $date;

    /**
     * @var string Salesperson Code - The client application salesperson reference code.
     */
    public $salespersonCode;

    /**
     * @var string Customer Code - The client application customer reference code.
     */
    public $customerCode;

    /**
     * @var string Customer Usage Type - The client application customer or usage type.
     */
    public $customerUsageType;

    /**
     * @var float Discount - The discount amount to apply to the document.
     */
    public $discount;

    /**
     * @var string Purchase Order Number for this document
     */
    public $purchaseOrderNo;

    /**
     * @var string Exemption Number for this document
     */
    public $exemptionNo;

    /**
     * @var AddressesModel Default addresses for all lines in this document
     */
    public $addresses;

    /**
     * @var LineItemModel[] Document line items list
     */
    public $lines;

    /**
     * @var object Special parameters for this transaction. To get a full list of available parameters, please use the /api/v2/definitions/parameters endpoint.
     */
    public $parameters;

    /**
     * @var string Reference Code used to reference the original document for a return invoice
     */
    public $referenceCode;

    /**
     * @var string Sets the sale location code (Outlet ID) for reporting this document to the tax authority.
     */
    public $reportingLocationCode;

    /**
     * @var boolean Causes the document to be committed if true.
     */
    public $commit;

    /**
     * @var string BatchCode for batch operations.
     */
    public $batchCode;

    /**
     * @var TaxOverrideModel Specifies a tax override for the entire document
     */
    public $taxOverride;

    /**
     * @var string 3 character ISO 4217 currency code.
     */
    public $currencyCode;

    /**
     * @var string Specifies whether the tax calculation is handled Local, Remote, or Automatic (default) (See ServiceMode::* for a list of allowable values)
     */
    public $serviceMode;

    /**
     * @var float Currency exchange rate from this transaction to the company base currency.
     */
    public $exchangeRate;

    /**
     * @var string Effective date of the exchange rate.
     */
    public $exchangeRateEffectiveDate;

    /**
     * @var string Sets the POS Lane Code sent by the User for this document.
     */
    public $posLaneCode;

    /**
     * @var string BusinessIdentificationNo
     */
    public $businessIdentificationNo;

    /**
     * @var boolean Specifies if the Transaction has the seller as IsSellerImporterOfRecord
     */
    public $isSellerImporterOfRecord;

    /**
     * @var string Description
     */
    public $description;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string If the user wishes to request additional debug information from this transaction, specify a level higher than 'normal' (See TaxDebugLevel::* for a list of allowable values)
     */
    public $debugLevel;

}

/**
 * A series of addresses information in a GetTax call
 */
class AddressesModel
{

    /**
     * @var AddressLocationInfo If this transaction occurred at a retail point-of-sale location, use this
     */
    public $singleLocation;

    /**
     * @var AddressLocationInfo If this transaction was shipped from a warehouse location to a customer location, specify both "ShipFrom" and "ShipTo".
     */
    public $shipFrom;

    /**
     * @var AddressLocationInfo If this transaction was shipped from a warehouse location to a customer location, specify both "ShipFrom" and "ShipTo".
     */
    public $shipTo;

    /**
     * @var AddressLocationInfo The place of business where you receive the customer's order.
     */
    public $pointOfOrderOrigin;

    /**
     * @var AddressLocationInfo The place of business where you accept/approve the customerâ€™s order, thereby becoming contractually obligated to make the sale.
     */
    public $pointOfOrderAcceptance;

}

/**
 * Represents one line item in a transaction
 */
class LineItemModel
{

    /**
     * @var string Line number within this document
     */
    public $number;

    /**
     * @var float Quantity of items in this line
     */
    public $quantity;

    /**
     * @var float Total amount for this line
     */
    public $amount;

    /**
     * @var AddressesModel Specify any differences for addresses between this line and the rest of the document
     */
    public $addresses;

    /**
     * @var string Tax Code - System or Custom Tax Code.
     */
    public $taxCode;

    /**
     * @var string Customer Usage Type - The client application customer or usage type.
     */
    public $customerUsageType;

    /**
     * @var string Item Code (SKU)
     */
    public $itemCode;

    /**
     * @var string Exemption number for this line
     */
    public $exemptionCode;

    /**
     * @var boolean True if the document discount should be applied to this line
     */
    public $discounted;

    /**
     * @var boolean Indicates if line has Tax Included; defaults to false
     */
    public $taxIncluded;

    /**
     * @var string Revenue Account
     */
    public $revenueAccount;

    /**
     * @var string Reference 1 - Client specific reference field
     */
    public $ref1;

    /**
     * @var string Reference 2 - Client specific reference field
     */
    public $ref2;

    /**
     * @var string Item description. This is required for SST transactions if an unmapped ItemCode is used.
     */
    public $description;

    /**
     * @var string BusinessIdentificationNo
     */
    public $businessIdentificationNo;

    /**
     * @var TaxOverrideModel Specifies a tax override for this line
     */
    public $taxOverride;

    /**
     * @var object Special parameters that apply to this line within this transaction. To get a full list of available parameters, please use the /api/v2/definitions/parameters endpoint.
     */
    public $parameters;

}

/**
 * Represents a tax override for a transaction
 */
class TaxOverrideModel
{

    /**
     * @var string Identifies the type of tax override (See TaxOverrideType::* for a list of allowable values)
     */
    public $type;

    /**
     * @var float Indicates a total override of the calculated tax on the document. AvaTax will distribute the override across all the lines.
     */
    public $taxAmount;

    /**
     * @var string The override tax date to use
     */
    public $taxDate;

    /**
     * @var string This provides the reason for a tax override for audit purposes. It is required for types 2-4.
     */
    public $reason;

}

/**
 * Represents an address to resolve.
 */
class AddressLocationInfo
{

    /**
     * @var string If you wish to use the address of an existing location for this company, specify the address here. Otherwise, leave this value empty.
     */
    public $locationCode;

    /**
     * @var string Line1
     */
    public $line1;

    /**
     * @var string Line2
     */
    public $line2;

    /**
     * @var string Line3
     */
    public $line3;

    /**
     * @var string City
     */
    public $city;

    /**
     * @var string State / Province / Region
     */
    public $region;

    /**
     * @var string Two character ISO 3166 Country Code
     */
    public $country;

    /**
     * @var string Postal Code / Zip Code
     */
    public $postalCode;

    /**
     * @var float Geospatial latitude measurement
     */
    public $latitude;

    /**
     * @var float Geospatial longitude measurement
     */
    public $longitude;

}

/**
 * A request to void a previously created transaction
 */
class VoidTransactionModel
{

    /**
     * @var string Please specify the reason for voiding or cancelling this transaction (See VoidReasonCode::* for a list of allowable values)
     */
    public $code;

}

/**
 * Settle this transaction with your ledger by executing one or many actions against that transaction. 
 * You may use this endpoint to verify the transaction, change the transaction's code, and commit the transaction for reporting purposes.
 * This endpoint may be used to execute any or all of these actions at once.
 */
class SettleTransactionModel
{

    /**
     * @var VerifyTransactionModel To use the "Settle" endpoint to verify a transaction, fill out this value.
     */
    public $verify;

    /**
     * @var ChangeTransactionCodeModel To use the "Settle" endpoint to change a transaction's code, fill out this value.
     */
    public $changeCode;

    /**
     * @var CommitTransactionModel To use the "Settle" endpoint to commit a transaction for reporting purposes, fill out this value. If you use Avalara Returns, committing a transaction will cause that transaction to be filed.
     */
    public $commit;

}

/**
 * Verify this transaction by matching it to values in your accounting system.
 */
class VerifyTransactionModel
{

    /**
     * @var string Transaction Date - The date on the invoice, purchase order, etc.
     */
    public $verifyTransactionDate;

    /**
     * @var float Total Amount - The total amount (not including tax) for the document.
     */
    public $verifyTotalAmount;

    /**
     * @var float Total Tax - The total tax for the document.
     */
    public $verifyTotalTax;

}

/**
 * Settle this transaction with your ledger by verifying its amounts.
 * If the transaction is not yet committed, you may specify the "commit" value to commit it to the ledger and allow it to be reported.
 * You may also optionally change the transaction's code by specifying the "newTransactionCode" value.
 */
class ChangeTransactionCodeModel
{

    /**
     * @var string To change the transaction code for this transaction, specify the new transaction code here.
     */
    public $newCode;

}

/**
 * Commit this transaction as permanent
 */
class CommitTransactionModel
{

    /**
     * @var boolean Set this value to be true to commit this transaction. Committing a transaction allows it to be reported on a tax return. Uncommitted transactions will not be reported.
     */
    public $commit;

}

/**
 * Commit this transaction as permanent
 */
class LockTransactionModel
{

    /**
     * @var boolean Set this value to be true to commit this transaction. Committing a transaction allows it to be reported on a tax return. Uncommitted transactions will not be reported.
     */
    public $isLocked;

}

/**
 * Bulk lock documents model
 */
class BulkLockTransactionModel
{

    /**
     * @var int[] List of documents to lock
     */
    public $documentIds;

    /**
     * @var boolean The lock status to set for the documents designated in this API
     */
    public $isLocked;

}

/**
 * Returns information about transactions that were locked
 */
class BulkLockTransactionResult
{

    /**
     * @var int Number of records that have been modified
     */
    public $numberOfRecords;

}

/**
 * Information about a previously created transaction
 */
class AuditTransactionModel
{

    /**
     * @var int Unique ID number of the company that created this transaction
     */
    public $companyId;

    /**
     * @var string Server timestamp, in UTC, of the date/time when the original transaction was created
     */
    public $serverTimestamp;

    /**
     * @var string Length of time the original API call took
     */
    public $serverDuration;

    /**
     * @var string api call status (See ApiCallStatus::* for a list of allowable values)
     */
    public $apiCallStatus;

    /**
     * @var OriginalApiRequestResponseModel Original API request/response
     */
    public $original;

    /**
     * @var ReconstructedApiRequestResponseModel Reconstructed API request/response
     */
    public $reconstructed;

}

/**
 * Represents the exact API request and response from the original transaction API call, if available
 */
class OriginalApiRequestResponseModel
{

    /**
     * @var string API request
     */
    public $request;

    /**
     * @var string API response
     */
    public $response;

}

/**
 * This model contains a reconstructed CreateTransaction request object that could potentially be used
 * to recreate this transaction.
 * 
 * Note that the API changes over time, and this reconstructed model is likely different from the exact request
 * that was originally used to create this transaction.
 */
class ReconstructedApiRequestResponseModel
{

    /**
     * @var CreateTransactionModel API request
     */
    public $request;

}

/**
 * Refund a committed transaction
 */
class RefundTransactionModel
{

    /**
     * @var string the committed transaction code to be refunded
     */
    public $refundTransactionCode;

    /**
     * @var string The date when the refund happens
     */
    public $refundDate;

    /**
     * @var string Type of this refund (See RefundType::* for a list of allowable values)
     */
    public $refundType;

    /**
     * @var float Percentage for refund
     */
    public $refundPercentage;

    /**
     * @var string[] Process refund for these lines
     */
    public $refundLines;

}

/**
 * User Entitlement Model
 */
class UserEntitlementModel
{

    /**
     * @var string[] List of API names and categories that this user is permitted to access
     */
    public $permissions;

    /**
     * @var string What access privileges does the current user have to see companies? (See CompanyAccessLevel::* for a list of allowable values)
     */
    public $accessLevel;

    /**
     * @var int[] The identities of all companies this user is permitted to access
     */
    public $companies;

}

/**
 * Ping Result Model
 */
class PingResultModel
{

    /**
     * @var string Version number
     */
    public $version;

    /**
     * @var boolean Returns true if you provided authentication for this API call; false if you did not.
     */
    public $authenticated;

    /**
     * @var string Returns the type of authentication you provided, if authenticated (See AuthenticationTypeId::* for a list of allowable values)
     */
    public $authenticationType;

    /**
     * @var string The username of the currently authenticated user, if any.
     */
    public $authenticatedUserName;

    /**
     * @var int The ID number of the currently authenticated user, if any.
     */
    public $authenticatedUserId;

    /**
     * @var int The ID number of the currently authenticated user's account, if any.
     */
    public $authenticatedAccountId;

}


/*****************************************************************************
 *                              Enumerated constants                         *
 *****************************************************************************/


/**
 * Lists of acceptable values for the enumerated data type TextCase
 */
class TextCase
{
    const C_UPPER = "Upper";
    const C_MIXED = "Mixed";

}


/**
 * Lists of acceptable values for the enumerated data type DocumentType
 */
class DocumentType
{
    const C_SALESORDER = "SalesOrder";
    const C_SALESINVOICE = "SalesInvoice";
    const C_PURCHASEORDER = "PurchaseOrder";
    const C_PURCHASEINVOICE = "PurchaseInvoice";
    const C_RETURNORDER = "ReturnOrder";
    const C_RETURNINVOICE = "ReturnInvoice";
    const C_INVENTORYTRANSFERORDER = "InventoryTransferOrder";
    const C_INVENTORYTRANSFERINVOICE = "InventoryTransferInvoice";
    const C_REVERSECHARGEORDER = "ReverseChargeOrder";
    const C_REVERSECHARGEINVOICE = "ReverseChargeInvoice";
    const C_ANY = "Any";

}


/**
 * Lists of acceptable values for the enumerated data type PointOfSaleFileType
 */
class PointOfSaleFileType
{
    const C_JSON = "Json";
    const C_CSV = "Csv";
    const C_XML = "Xml";

}


/**
 * Lists of acceptable values for the enumerated data type PointOfSalePartnerId
 */
class PointOfSalePartnerId
{
    const C_DMA = "DMA";
    const C_AX7 = "AX7";

}


/**
 * Lists of acceptable values for the enumerated data type ServiceTypeId
 */
class ServiceTypeId
{
    const C_NONE = "None";
    const C_AVATAXST = "AvaTaxST";
    const C_AVATAXPRO = "AvaTaxPro";
    const C_AVATAXGLOBAL = "AvaTaxGlobal";
    const C_AUTOADDRESS = "AutoAddress";
    const C_AUTORETURNS = "AutoReturns";
    const C_TAXSOLVER = "TaxSolver";
    const C_AVATAXCSP = "AvaTaxCsp";
    const C_TWE = "Twe";
    const C_MRS = "Mrs";
    const C_AVACERT = "AvaCert";
    const C_AUTHORIZATIONPARTNER = "AuthorizationPartner";
    const C_CERTCAPTURE = "CertCapture";
    const C_AVAUPC = "AvaUpc";
    const C_AVACUT = "AvaCUT";
    const C_AVALANDEDCOST = "AvaLandedCost";
    const C_AVALODGING = "AvaLodging";
    const C_AVABOTTLE = "AvaBottle";

}


/**
 * Lists of acceptable values for the enumerated data type AccountStatusId
 */
class AccountStatusId
{
    const C_INACTIVE = "Inactive";
    const C_ACTIVE = "Active";
    const C_TEST = "Test";
    const C_NEW = "New";

}


/**
 * Lists of acceptable values for the enumerated data type SecurityRoleId
 */
class SecurityRoleId
{
    const C_NOACCESS = "NoAccess";
    const C_SITEADMIN = "SiteAdmin";
    const C_ACCOUNTOPERATOR = "AccountOperator";
    const C_ACCOUNTADMIN = "AccountAdmin";
    const C_ACCOUNTUSER = "AccountUser";
    const C_SYSTEMADMIN = "SystemAdmin";
    const C_REGISTRAR = "Registrar";
    const C_CSPTESTER = "CSPTester";
    const C_CSPADMIN = "CSPAdmin";
    const C_SYSTEMOPERATOR = "SystemOperator";
    const C_TECHNICALSUPPORTUSER = "TechnicalSupportUser";
    const C_TECHNICALSUPPORTADMIN = "TechnicalSupportAdmin";
    const C_TREASURYUSER = "TreasuryUser";
    const C_TREASURYADMIN = "TreasuryAdmin";
    const C_COMPLIANCEUSER = "ComplianceUser";
    const C_COMPLIANCEADMIN = "ComplianceAdmin";
    const C_PROSTORESOPERATOR = "ProStoresOperator";
    const C_COMPANYUSER = "CompanyUser";
    const C_COMPANYADMIN = "CompanyAdmin";
    const C_COMPLIANCETEMPUSER = "ComplianceTempUser";
    const C_COMPLIANCEROOTUSER = "ComplianceRootUser";
    const C_COMPLIANCEOPERATOR = "ComplianceOperator";
    const C_SSTADMIN = "SSTAdmin";

}


/**
 * Lists of acceptable values for the enumerated data type PasswordStatusId
 */
class PasswordStatusId
{
    const C_USERCANNOTCHANGE = "UserCannotChange";
    const C_USERCANCHANGE = "UserCanChange";
    const C_USERMUSTCHANGE = "UserMustChange";

}


/**
 * Lists of acceptable values for the enumerated data type ErrorCodeId
 */
class ErrorCodeId
{
    const C_SERVERCONFIGURATION = "ServerConfiguration";
    const C_ACCOUNTINVALIDEXCEPTION = "AccountInvalidException";
    const C_COMPANYINVALIDEXCEPTION = "CompanyInvalidException";
    const C_ENTITYNOTFOUNDERROR = "EntityNotFoundError";
    const C_VALUEREQUIREDERROR = "ValueRequiredError";
    const C_RANGEERROR = "RangeError";
    const C_RANGECOMPAREERROR = "RangeCompareError";
    const C_RANGESETERROR = "RangeSetError";
    const C_TAXPAYERNUMBERREQUIRED = "TaxpayerNumberRequired";
    const C_COMMONPASSWORD = "CommonPassword";
    const C_WEAKPASSWORD = "WeakPassword";
    const C_STRINGLENGTHERROR = "StringLengthError";
    const C_EMAILVALIDATIONERROR = "EmailValidationError";
    const C_EMAILMISSINGERROR = "EmailMissingError";
    const C_PARSERFIELDNAMEERROR = "ParserFieldNameError";
    const C_PARSERFIELDVALUEERROR = "ParserFieldValueError";
    const C_PARSERSYNTAXERROR = "ParserSyntaxError";
    const C_PARSERTOOMANYPARAMETERSERROR = "ParserTooManyParametersError";
    const C_PARSERUNTERMINATEDVALUEERROR = "ParserUnterminatedValueError";
    const C_DELETEUSERSELFERROR = "DeleteUserSelfError";
    const C_OLDPASSWORDINVALID = "OldPasswordInvalid";
    const C_CANNOTCHANGEPASSWORD = "CannotChangePassword";
    const C_CANNOTCHANGECOMPANYCODE = "CannotChangeCompanyCode";
    const C_DATEFORMATERROR = "DateFormatError";
    const C_NODEFAULTCOMPANY = "NoDefaultCompany";
    const C_AUTHENTICATIONEXCEPTION = "AuthenticationException";
    const C_AUTHORIZATIONEXCEPTION = "AuthorizationException";
    const C_VALIDATIONEXCEPTION = "ValidationException";
    const C_INACTIVEUSERERROR = "InactiveUserError";
    const C_AUTHENTICATIONINCOMPLETE = "AuthenticationIncomplete";
    const C_BASICAUTHINCORRECT = "BasicAuthIncorrect";
    const C_IDENTITYSERVERERROR = "IdentityServerError";
    const C_BEARERTOKENINVALID = "BearerTokenInvalid";
    const C_MODELREQUIREDEXCEPTION = "ModelRequiredException";
    const C_ACCOUNTEXPIREDEXCEPTION = "AccountExpiredException";
    const C_VISIBILITYERROR = "VisibilityError";
    const C_BEARERTOKENNOTSUPPORTED = "BearerTokenNotSupported";
    const C_INVALIDSECURITYROLE = "InvalidSecurityRole";
    const C_INVALIDREGISTRARACTION = "InvalidRegistrarAction";
    const C_REMOTESERVERERROR = "RemoteServerError";
    const C_NOFILTERCRITERIAEXCEPTION = "NoFilterCriteriaException";
    const C_OPENCLAUSEEXCEPTION = "OpenClauseException";
    const C_JSONFORMATERROR = "JsonFormatError";
    const C_UNHANDLEDEXCEPTION = "UnhandledException";
    const C_REPORTINGCOMPANYMUSTHAVECONTACTSERROR = "ReportingCompanyMustHaveContactsError";
    const C_COMPANYPROFILENOTSET = "CompanyProfileNotSet";
    const C_MODELSTATEINVALID = "ModelStateInvalid";
    const C_DATERANGEERROR = "DateRangeError";
    const C_INVALIDDATERANGEERROR = "InvalidDateRangeError";
    const C_DELETEINFORMATION = "DeleteInformation";
    const C_CANNOTCREATEDELETEDOBJECTS = "CannotCreateDeletedObjects";
    const C_CANNOTMODIFYDELETEDOBJECTS = "CannotModifyDeletedObjects";
    const C_RETURNNAMENOTFOUND = "ReturnNameNotFound";
    const C_INVALIDADDRESSTYPEANDCATEGORY = "InvalidAddressTypeAndCategory";
    const C_DEFAULTCOMPANYLOCATION = "DefaultCompanyLocation";
    const C_INVALIDCOUNTRY = "InvalidCountry";
    const C_INVALIDCOUNTRYREGION = "InvalidCountryRegion";
    const C_BRAZILVALIDATIONERROR = "BrazilValidationError";
    const C_BRAZILEXEMPTVALIDATIONERROR = "BrazilExemptValidationError";
    const C_BRAZILPISCOFINSERROR = "BrazilPisCofinsError";
    const C_JURISDICTIONNOTFOUNDERROR = "JurisdictionNotFoundError";
    const C_MEDICALEXCISEERROR = "MedicalExciseError";
    const C_RATEDEPENDSTAXABILITYERROR = "RateDependsTaxabilityError";
    const C_RATEDEPENDSEUROPEERROR = "RateDependsEuropeError";
    const C_RATETYPENOTSUPPORTED = "RateTypeNotSupported";
    const C_CANNOTUPDATENESTEDOBJECTS = "CannotUpdateNestedObjects";
    const C_UPCCODEINVALIDCHARS = "UPCCodeInvalidChars";
    const C_UPCCODEINVALIDLENGTH = "UPCCodeInvalidLength";
    const C_INCORRECTPATHERROR = "IncorrectPathError";
    const C_INVALIDJURISDICTIONTYPE = "InvalidJurisdictionType";
    const C_MUSTCONFIRMRESETLICENSEKEY = "MustConfirmResetLicenseKey";
    const C_DUPLICATECOMPANYCODE = "DuplicateCompanyCode";
    const C_TINFORMATERROR = "TINFormatError";
    const C_DUPLICATENEXUSERROR = "DuplicateNexusError";
    const C_UNKNOWNNEXUSERROR = "UnknownNexusError";
    const C_PARENTNEXUSNOTFOUND = "ParentNexusNotFound";
    const C_INVALIDTAXCODETYPE = "InvalidTaxCodeType";
    const C_CANNOTACTIVATECOMPANY = "CannotActivateCompany";
    const C_DUPLICATEENTITYPROPERTY = "DuplicateEntityProperty";
    const C_REPORTINGENTITYERROR = "ReportingEntityError";
    const C_INVALIDRETURNOPERATIONERROR = "InvalidReturnOperationError";
    const C_CANNOTDELETECOMPANY = "CannotDeleteCompany";
    const C_COUNTRYOVERRIDESNOTAVAILABLE = "CountryOverridesNotAvailable";
    const C_JURISDICTIONOVERRIDEMISMATCH = "JurisdictionOverrideMismatch";
    const C_DUPLICATESYSTEMTAXCODE = "DuplicateSystemTaxCode";
    const C_SSTOVERRIDESNOTAVAILABLE = "SSTOverridesNotAvailable";
    const C_NEXUSDATEMISMATCH = "NexusDateMismatch";
    const C_TECHSUPPORTAUDITREQUIRED = "TechSupportAuditRequired";
    const C_NEXUSPARENTDATEMISMATCH = "NexusParentDateMismatch";
    const C_BEARERTOKENPARSEUSERIDERROR = "BearerTokenParseUserIdError";
    const C_RETRIEVEUSERERROR = "RetrieveUserError";
    const C_BATCHSALESAUDITMUSTBEZIPPEDERROR = "BatchSalesAuditMustBeZippedError";
    const C_BATCHZIPMUSTCONTAINONEFILEERROR = "BatchZipMustContainOneFileError";
    const C_BATCHINVALIDFILETYPEERROR = "BatchInvalidFileTypeError";
    const C_POINTOFSALEFILESIZE = "PointOfSaleFileSize";
    const C_POINTOFSALESETUP = "PointOfSaleSetup";
    const C_GETTAXERROR = "GetTaxError";
    const C_ADDRESSCONFLICTEXCEPTION = "AddressConflictException";
    const C_DOCUMENTCODECONFLICT = "DocumentCodeConflict";
    const C_MISSINGADDRESS = "MissingAddress";
    const C_INVALIDPARAMETER = "InvalidParameter";
    const C_INVALIDPARAMETERVALUE = "InvalidParameterValue";
    const C_COMPANYCODECONFLICT = "CompanyCodeConflict";
    const C_DOCUMENTFETCHLIMIT = "DocumentFetchLimit";
    const C_ADDRESSINCOMPLETE = "AddressIncomplete";
    const C_ADDRESSLOCATIONNOTFOUND = "AddressLocationNotFound";
    const C_MISSINGLINE = "MissingLine";
    const C_INVALIDADDRESSTEXTCASE = "InvalidAddressTextCase";
    const C_DOCUMENTNOTCOMMITTED = "DocumentNotCommitted";
    const C_MULTIDOCUMENTTYPESERROR = "MultiDocumentTypesError";
    const C_INVALIDDOCUMENTTYPESTOFETCH = "InvalidDocumentTypesToFetch";
    const C_BADDOCUMENTFETCH = "BadDocumentFetch";
    const C_SERVERUNREACHABLE = "ServerUnreachable";
    const C_SUBSCRIPTIONREQUIRED = "SubscriptionRequired";
    const C_ACCOUNTEXISTS = "AccountExists";
    const C_INVITATIONONLY = "InvitationOnly";
    const C_ZTBLISTCONNECTORFAIL = "ZTBListConnectorFail";
    const C_ZTBCREATESUBSCRIPTIONSFAIL = "ZTBCreateSubscriptionsFail";
    const C_FREETRIALNOTAVAILABLE = "FreeTrialNotAvailable";
    const C_INVALIDDOCUMENTSTATUSFORREFUND = "InvalidDocumentStatusForRefund";
    const C_REFUNDTYPEANDPERCENTAGEMISMATCH = "RefundTypeAndPercentageMismatch";
    const C_INVALIDDOCUMENTTYPEFORREFUND = "InvalidDocumentTypeForRefund";
    const C_REFUNDTYPEANDLINEMISMATCH = "RefundTypeAndLineMismatch";
    const C_NULLREFUNDPERCENTAGEANDLINES = "NullRefundPercentageAndLines";
    const C_INVALIDREFUNDTYPE = "InvalidRefundType";
    const C_REFUNDPERCENTAGEFORTAXONLY = "RefundPercentageForTaxOnly";
    const C_LINENOOUTOFRANGE = "LineNoOutOfRange";
    const C_REFUNDPERCENTAGEOUTOFRANGE = "RefundPercentageOutOfRange";
    const C_TAXRATENOTAVAILABLEFORFREEINTHISCOUNTRY = "TaxRateNotAvailableForFreeInThisCountry";
    const C_FILINGCALENDARCHANGENOTALLOWED = "FilingCalendarChangeNotAllowed";

}


/**
 * Lists of acceptable values for the enumerated data type SeverityLevel
 */
class SeverityLevel
{
    const C_SUCCESS = "Success";
    const C_WARNING = "Warning";
    const C_ERROR = "Error";
    const C_EXCEPTION = "Exception";

}


/**
 * Lists of acceptable values for the enumerated data type ResolutionQuality
 */
class ResolutionQuality
{
    const C_NOTCODED = "NotCoded";
    const C_EXTERNAL = "External";
    const C_COUNTRYCENTROID = "CountryCentroid";
    const C_REGIONCENTROID = "RegionCentroid";
    const C_PARTIALCENTROID = "PartialCentroid";
    const C_POSTALCENTROIDGOOD = "PostalCentroidGood";
    const C_POSTALCENTROIDBETTER = "PostalCentroidBetter";
    const C_POSTALCENTROIDBEST = "PostalCentroidBest";
    const C_INTERSECTION = "Intersection";
    const C_INTERPOLATED = "Interpolated";
    const C_ROOFTOP = "Rooftop";
    const C_CONSTANT = "Constant";

}


/**
 * Lists of acceptable values for the enumerated data type JurisdictionType
 */
class JurisdictionType
{
    const C_COUNTRY = "Country";
    const C_COMPOSITE = "Composite";
    const C_STATE = "State";
    const C_COUNTY = "County";
    const C_CITY = "City";
    const C_SPECIAL = "Special";

}


/**
 * Lists of acceptable values for the enumerated data type BatchType
 */
class BatchType
{
    const C_AVACERTUPDATE = "AvaCertUpdate";
    const C_AVACERTUPDATEALL = "AvaCertUpdateAll";
    const C_BATCHMAINTENANCE = "BatchMaintenance";
    const C_COMPANYLOCATIONIMPORT = "CompanyLocationImport";
    const C_DOCUMENTIMPORT = "DocumentImport";
    const C_EXEMPTCERTIMPORT = "ExemptCertImport";
    const C_ITEMIMPORT = "ItemImport";
    const C_SALESAUDITEXPORT = "SalesAuditExport";
    const C_SSTPTESTDECKIMPORT = "SstpTestDeckImport";
    const C_TAXRULEIMPORT = "TaxRuleImport";
    const C_TRANSACTIONIMPORT = "TransactionImport";
    const C_UPCBULKIMPORT = "UPCBulkImport";
    const C_UPCVALIDATIONIMPORT = "UPCValidationImport";

}


/**
 * Lists of acceptable values for the enumerated data type BatchStatus
 */
class BatchStatus
{
    const C_WAITING = "Waiting";
    const C_SYSTEMERRORS = "SystemErrors";
    const C_CANCELLED = "Cancelled";
    const C_COMPLETED = "Completed";
    const C_CREATING = "Creating";
    const C_DELETED = "Deleted";
    const C_ERRORS = "Errors";
    const C_PAUSED = "Paused";
    const C_PROCESSING = "Processing";

}


/**
 * Lists of acceptable values for the enumerated data type RoundingLevelId
 */
class RoundingLevelId
{
    const C_LINE = "Line";
    const C_DOCUMENT = "Document";

}


/**
 * Lists of acceptable values for the enumerated data type TaxDependencyLevelId
 */
class TaxDependencyLevelId
{
    const C_DOCUMENT = "Document";
    const C_STATE = "State";
    const C_TAXREGION = "TaxRegion";
    const C_ADDRESS = "Address";

}


/**
 * Lists of acceptable values for the enumerated data type AddressTypeId
 */
class AddressTypeId
{
    const C_LOCATION = "Location";
    const C_SALESPERSON = "Salesperson";

}


/**
 * Lists of acceptable values for the enumerated data type AddressCategoryId
 */
class AddressCategoryId
{
    const C_STOREFRONT = "Storefront";
    const C_MAINOFFICE = "MainOffice";
    const C_WAREHOUSE = "Warehouse";
    const C_SALESPERSON = "Salesperson";
    const C_OTHER = "Other";

}


/**
 * Lists of acceptable values for the enumerated data type JurisTypeId
 */
class JurisTypeId
{
    const C_STA = "STA";
    const C_CTY = "CTY";
    const C_CIT = "CIT";
    const C_STJ = "STJ";
    const C_CNT = "CNT";

}


/**
 * Lists of acceptable values for the enumerated data type NexusTypeId
 */
class NexusTypeId
{
    const C_NONE = "None";
    const C_SALESORSELLERSUSETAX = "SalesOrSellersUseTax";
    const C_SALESTAX = "SalesTax";
    const C_SSTVOLUNTEER = "SSTVolunteer";
    const C_SSTNONVOLUNTEER = "SSTNonVolunteer";

}


/**
 * Lists of acceptable values for the enumerated data type Sourcing
 */
class Sourcing
{
    const C_MIXED = "Mixed";
    const C_DESTINATION = "Destination";
    const C_ORIGIN = "Origin";

}


/**
 * Lists of acceptable values for the enumerated data type LocalNexusTypeId
 */
class LocalNexusTypeId
{
    const C_SELECTED = "Selected";
    const C_STATEADMINISTERED = "StateAdministered";
    const C_ALL = "All";

}


/**
 * Lists of acceptable values for the enumerated data type MatchingTaxType
 */
class MatchingTaxType
{
    const C_ALL = "All";
    const C_BOTHSALESANDUSETAX = "BothSalesAndUseTax";
    const C_CONSUMERUSETAX = "ConsumerUseTax";
    const C_CONSUMERSUSEANDSELLERSUSETAX = "ConsumersUseAndSellersUseTax";
    const C_CONSUMERUSEANDSALESTAX = "ConsumerUseAndSalesTax";
    const C_FEE = "Fee";
    const C_VATINPUTTAX = "VATInputTax";
    const C_VATNONRECOVERABLEINPUTTAX = "VATNonrecoverableInputTax";
    const C_VATOUTPUTTAX = "VATOutputTax";
    const C_RENTAL = "Rental";
    const C_SALESTAX = "SalesTax";
    const C_USETAX = "UseTax";

}


/**
 * Lists of acceptable values for the enumerated data type TaxRuleTypeId
 */
class TaxRuleTypeId
{
    const C_RATERULE = "RateRule";
    const C_RATEOVERRIDERULE = "RateOverrideRule";
    const C_BASERULE = "BaseRule";
    const C_EXEMPTENTITYRULE = "ExemptEntityRule";
    const C_PRODUCTTAXABILITYRULE = "ProductTaxabilityRule";
    const C_NEXUSRULE = "NexusRule";

}


/**
 * Lists of acceptable values for the enumerated data type ParameterBagDataType
 */
class ParameterBagDataType
{
    const C_STRING = "String";
    const C_BOOLEAN = "Boolean";
    const C_NUMERIC = "Numeric";

}


/**
 * Lists of acceptable values for the enumerated data type ScraperType
 */
class ScraperType
{
    const C_LOGIN = "Login";
    const C_CUSTOMERDORDATA = "CustomerDorData";

}


/**
 * Lists of acceptable values for the enumerated data type BoundaryLevel
 */
class BoundaryLevel
{
    const C_ADDRESS = "Address";
    const C_ZIP9 = "Zip9";
    const C_ZIP5 = "Zip5";

}


/**
 * Lists of acceptable values for the enumerated data type OutletTypeId
 */
class OutletTypeId
{
    const C_NONE = "None";
    const C_SCHEDULE = "Schedule";
    const C_DUPLICATE = "Duplicate";

}


/**
 * Lists of acceptable values for the enumerated data type FilingFrequencyId
 */
class FilingFrequencyId
{
    const C_MONTHLY = "Monthly";
    const C_QUARTERLY = "Quarterly";
    const C_SEMIANNUALLY = "SemiAnnually";
    const C_ANNUALLY = "Annually";
    const C_BIMONTHLY = "Bimonthly";
    const C_OCCASIONAL = "Occasional";
    const C_INVERSEQUARTERLY = "InverseQuarterly";

}


/**
 * Lists of acceptable values for the enumerated data type FilingTypeId
 */
class FilingTypeId
{
    const C_PAPERRETURN = "PaperReturn";
    const C_ELECTRONICRETURN = "ElectronicReturn";
    const C_SER = "SER";
    const C_EFTPAPER = "EFTPaper";
    const C_PHONEPAPER = "PhonePaper";
    const C_SIGNATUREREADY = "SignatureReady";
    const C_EFILECHECK = "EfileCheck";

}


/**
 * Lists of acceptable values for the enumerated data type FilingRequestStatus
 */
class FilingRequestStatus
{
    const C_NEW = "New";
    const C_VALIDATED = "Validated";
    const C_PENDING = "Pending";
    const C_ACTIVE = "Active";
    const C_PENDINGSTOP = "PendingStop";
    const C_INACTIVE = "Inactive";
    const C_CHANGEREQUEST = "ChangeRequest";
    const C_REQUESTAPPROVED = "RequestApproved";
    const C_REQUESTDENIED = "RequestDenied";

}


/**
 * Lists of acceptable values for the enumerated data type WorksheetTypeId
 */
class WorksheetTypeId
{
    const C_ORIGINAL = "Original";
    const C_AMENDED = "Amended";
    const C_TEST = "Test";

}


/**
 * Lists of acceptable values for the enumerated data type FilingStatusId
 */
class FilingStatusId
{
    const C_PENDINGAPPROVAL = "PendingApproval";
    const C_DIRTY = "Dirty";
    const C_APPROVEDTOFILE = "ApprovedToFile";
    const C_PENDINGFILING = "PendingFiling";
    const C_PENDINGFILINGONBEHALF = "PendingFilingOnBehalf";
    const C_FILED = "Filed";
    const C_FILEDONBEHALF = "FiledOnBehalf";
    const C_RETURNACCEPTED = "ReturnAccepted";
    const C_RETURNACCEPTEDONBEHALF = "ReturnAcceptedOnBehalf";
    const C_PAYMENTREMITTED = "PaymentRemitted";
    const C_VOIDED = "Voided";
    const C_PENDINGRETURN = "PendingReturn";
    const C_PENDINGRETURNONBEHALF = "PendingReturnOnBehalf";
    const C_DONOTFILE = "DoNotFile";
    const C_RETURNREJECTED = "ReturnRejected";
    const C_RETURNREJECTEDONBEHALF = "ReturnRejectedOnBehalf";
    const C_APPROVEDTOFILEONBEHALF = "ApprovedToFileOnBehalf";

}


/**
 * Lists of acceptable values for the enumerated data type AccrualType
 */
class AccrualType
{
    const C_FILING = "Filing";
    const C_ACCRUAL = "Accrual";

}


/**
 * Lists of acceptable values for the enumerated data type AdjustmentPeriodTypeId
 */
class AdjustmentPeriodTypeId
{
    const C_NONE = "None";
    const C_CURRENTPERIOD = "CurrentPeriod";
    const C_NEXTPERIOD = "NextPeriod";

}


/**
 * Lists of acceptable values for the enumerated data type AdjustmentTypeId
 */
class AdjustmentTypeId
{
    const C_OTHER = "Other";
    const C_CURRENTPERIODROUNDING = "CurrentPeriodRounding";
    const C_PRIORPERIODROUNDING = "PriorPeriodRounding";
    const C_CURRENTPERIODDISCOUNT = "CurrentPeriodDiscount";
    const C_PRIORPERIODDISCOUNT = "PriorPeriodDiscount";
    const C_CURRENTPERIODCOLLECTION = "CurrentPeriodCollection";
    const C_PRIORPERIODCOLLECTION = "PriorPeriodCollection";
    const C_PENALTY = "Penalty";
    const C_INTEREST = "Interest";
    const C_DISCOUNT = "Discount";
    const C_ROUNDING = "Rounding";
    const C_CSPFEE = "CspFee";

}


/**
 * Lists of acceptable values for the enumerated data type PaymentAccountTypeId
 */
class PaymentAccountTypeId
{
    const C_NONE = "None";
    const C_ACCOUNTSRECEIVABLEACCOUNTSPAYABLE = "AccountsReceivableAccountsPayable";
    const C_ACCOUNTSRECEIVABLE = "AccountsReceivable";
    const C_ACCOUNTSPAYABLE = "AccountsPayable";

}


/**
 * Lists of acceptable values for the enumerated data type NoticeCustomerType
 */
class NoticeCustomerType
{
    const C_AVATAXRETURNS = "AvaTaxReturns";
    const C_STANDALONE = "StandAlone";
    const C_STRATEGIC = "Strategic";
    const C_SST = "SST";
    const C_TRUSTFILE = "TrustFile";

}


/**
 * Lists of acceptable values for the enumerated data type FundingOption
 */
class FundingOption
{
    const C_PULL = "Pull";
    const C_WIRE = "Wire";

}


/**
 * Lists of acceptable values for the enumerated data type NoticePriorityId
 */
class NoticePriorityId
{
    const C_IMMEDIATEATTENTIONREQUIRED = "ImmediateAttentionRequired";
    const C_HIGH = "High";
    const C_NORMAL = "Normal";
    const C_LOW = "Low";

}


/**
 * Lists of acceptable values for the enumerated data type DocumentStatus
 */
class DocumentStatus
{
    const C_TEMPORARY = "Temporary";
    const C_SAVED = "Saved";
    const C_POSTED = "Posted";
    const C_COMMITTED = "Committed";
    const C_CANCELLED = "Cancelled";
    const C_ADJUSTED = "Adjusted";
    const C_QUEUED = "Queued";
    const C_PENDINGAPPROVAL = "PendingApproval";
    const C_ANY = "Any";

}


/**
 * Lists of acceptable values for the enumerated data type TaxOverrideTypeId
 */
class TaxOverrideTypeId
{
    const C_NONE = "None";
    const C_TAXAMOUNT = "TaxAmount";
    const C_EXEMPTION = "Exemption";
    const C_TAXDATE = "TaxDate";
    const C_ACCRUEDTAXAMOUNT = "AccruedTaxAmount";

}


/**
 * Lists of acceptable values for the enumerated data type AdjustmentReason
 */
class AdjustmentReason
{
    const C_NOTADJUSTED = "NotAdjusted";
    const C_SOURCINGISSUE = "SourcingIssue";
    const C_RECONCILEDWITHGENERALLEDGER = "ReconciledWithGeneralLedger";
    const C_EXEMPTCERTAPPLIED = "ExemptCertApplied";
    const C_PRICEADJUSTED = "PriceAdjusted";
    const C_PRODUCTRETURNED = "ProductReturned";
    const C_PRODUCTEXCHANGED = "ProductExchanged";
    const C_BADDEBT = "BadDebt";
    const C_OTHER = "Other";
    const C_OFFLINE = "Offline";

}


/**
 * Lists of acceptable values for the enumerated data type TaxType
 */
class TaxType
{
    const C_CONSUMERUSE = "ConsumerUse";
    const C_EXCISE = "Excise";
    const C_FEE = "Fee";
    const C_INPUT = "Input";
    const C_NONRECOVERABLE = "Nonrecoverable";
    const C_OUTPUT = "Output";
    const C_RENTAL = "Rental";
    const C_SALES = "Sales";
    const C_USE = "Use";

}


/**
 * Lists of acceptable values for the enumerated data type ServiceMode
 */
class ServiceMode
{
    const C_AUTOMATIC = "Automatic";
    const C_LOCAL = "Local";
    const C_REMOTE = "Remote";

}


/**
 * Lists of acceptable values for the enumerated data type TaxDebugLevel
 */
class TaxDebugLevel
{
    const C_NORMAL = "Normal";
    const C_DIAGNOSTIC = "Diagnostic";

}


/**
 * Lists of acceptable values for the enumerated data type TaxOverrideType
 */
class TaxOverrideType
{
    const C_NONE = "None";
    const C_TAXAMOUNT = "TaxAmount";
    const C_EXEMPTION = "Exemption";
    const C_TAXDATE = "TaxDate";
    const C_ACCRUEDTAXAMOUNT = "AccruedTaxAmount";
    const C_DERIVETAXABLE = "DeriveTaxable";

}


/**
 * Lists of acceptable values for the enumerated data type VoidReasonCode
 */
class VoidReasonCode
{
    const C_UNSPECIFIED = "Unspecified";
    const C_POSTFAILED = "PostFailed";
    const C_DOCDELETED = "DocDeleted";
    const C_DOCVOIDED = "DocVoided";
    const C_ADJUSTMENTCANCELLED = "AdjustmentCancelled";

}


/**
 * Lists of acceptable values for the enumerated data type ApiCallStatus
 */
class ApiCallStatus
{
    const C_ORIGINALAPICALLAVAILABLE = "OriginalApiCallAvailable";
    const C_RECONSTRUCTEDAPICALLAVAILABLE = "ReconstructedApiCallAvailable";
    const C_ANY = "Any";

}


/**
 * Lists of acceptable values for the enumerated data type RefundType
 */
class RefundType
{
    const C_FULL = "Full";
    const C_PARTIAL = "Partial";
    const C_TAXONLY = "TaxOnly";
    const C_PERCENTAGE = "Percentage";

}


/**
 * Lists of acceptable values for the enumerated data type CompanyAccessLevel
 */
class CompanyAccessLevel
{
    const C_NONE = "None";
    const C_SINGLECOMPANY = "SingleCompany";
    const C_SINGLEACCOUNT = "SingleAccount";
    const C_ALLCOMPANIES = "AllCompanies";

}


/**
 * Lists of acceptable values for the enumerated data type AuthenticationTypeId
 */
class AuthenticationTypeId
{
    const C_NONE = "None";
    const C_USERNAMEPASSWORD = "UsernamePassword";
    const C_ACCOUNTIDLICENSEKEY = "AccountIdLicenseKey";
    const C_OPENIDBEARERTOKEN = "OpenIdBearerToken";

}


/**
 * Lists of acceptable values for the enumerated data type TransactionAddressType
 */
class TransactionAddressType
{
    const C_SHIPFROM = "ShipFrom";
    const C_SHIPTO = "ShipTo";
    const C_POINTOFORDERACCEPTANCE = "PointOfOrderAcceptance";
    const C_POINTOFORDERORIGIN = "PointOfOrderOrigin";
    const C_SINGLELOCATION = "SingleLocation";

}


/*****************************************************************************
 *                              Transaction Builder                          *
 *****************************************************************************/

/**
 * TransactionBuilder helps you construct a new transaction using a literate interface
 */
class TransactionBuilder
{
    /**
     * The in-progress model
     */
    private $_model;

    /**
     * Keeps track of the line number when adding multiple lines
     */
    private $_line_number;
    
    /**
     * The client that will be used to create the transaction
     */
    private $_client;
        
    /**
     * TransactionBuilder helps you construct a new transaction using a literate interface
     *
     * @param AvaTaxClient  $client        The AvaTaxClient object to use to create this transaction
     * @param string        $companyCode   The code of the company for this transaction
     * @param DocumentType  $type          The type of transaction to create (See DocumentType::* for a list of allowable values)
     * @param string        $customerCode  The customer code for this transaction
     */
    public function __construct($client, $companyCode, $type, $customerCode)
    {
        $this->_client = $client;
        $this->_line_number = 1;
        $this->_model = [
            'companyCode' => $companyCode,
            'customerCode' => $customerCode,
            'date' => date('Y-m-d H:i:s'),
            'type' => $type,
            'lines' => [],
        ];
    }

    /**
     * Set the commit flag of the transaction.
     *
     * @return
     */
    public function withCommit()
    {
        $this->_model['commit'] = true;
        return $this;
    }

    /**
     * Enable diagnostic information
     *
     * @return  TransactionBuilder
     */
    public function withDiagnostics()
    {
        $this->_model['debugLevel'] = Constants::TAXDEBUGLEVEL_DIAGNOSTIC;
        return $this;
    }

    /**
     * Set a specific discount amount
     *
     * @param   float               $discount
     * @return  TransactionBuilder
     */
    public function withDiscountAmount($discount)
    {
        $this->_model['discount'] = $discount;
        return $this;
    }

    /**
     * Set if discount is applicable for the current line
     *
     * @param   boolean             discounted
     * @return  TransactionBuilder
     */
    public function withItemDiscount($discounted)
    {
        $l = GetMostRecentLine("WithItemDiscount");
        $l['discounted'] = $discounted;
        return $this;
    }

    /**
     * Set a specific transaction code
     *
     * @param   string              code
     * @return  TransactionBuilder
     */
    public function withTransactionCode($code)
    {
        $this->_model['code'] = $code;
        return $this;
    }

    /**
     * Set the document type
     *
     * @param   string              type    (See DocumentType::* for a list of allowable values)
     * @return  TransactionBuilder
     */
    public function withType($type)
    {
        $this->_model['type'] = $type;
        return $this;
    }

    /**
     * Add a parameter at the document level
     *
     * @param   string              name
     * @param   string              value
     * @return  TransactionBuilder
     */
    public function withParameter($name, $value)
    {
        if (empty($this->_model['parameters'])) $this->_model['parameters'] = [];
        $this->_model['parameters'][$name] = $value;
        return $this;
    }

    /**
     * Add a parameter to the current line
     *
     * @param   string              name
     * @param   string              value
     * @return  TransactionBuilder
     */
    public function withLineParameter($name, $value)
    {
        $l = GetMostRecentLine("WithLineParameter");
        if (empty($l['parameters'])) $l['parameters'] = [];
        $l[$name] = $value;
        return $this;
    }

    /**
     * Add an address to this transaction
     *
     * @param   string              type          Address Type (See AddressType::* for a list of allowable values)
     * @param   string              line1         The street address, attention line, or business name of the location.
     * @param   string              line2         The street address, business name, or apartment/unit number of the location.
     * @param   string              line3         The street address or apartment/unit number of the location.
     * @param   string              city          City of the location.
     * @param   string              region        State or Region of the location.
     * @param   string              postalCode    Postal/zip code of the location.
     * @param   string              country       The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        if (empty($this->_model['addresses'])) $this->_model['addresses'] = [];
        $ai = [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'city' => $city,
            'region' => $region,
            'postalCode' => $postalCode,
            'country' => $country
        ];
        $this->_model['addresses'][$type] = $ai;
        return $this;
    }

    /**
     * Add a lat/long coordinate to this transaction
     *
     * @param   string              $type       Address Type (See AddressType::* for a list of allowable values)
     * @param   float               $latitude   The latitude of the geolocation for this transaction
     * @param   float               $longitude  The longitude of the geolocation for this transaction
     * @return  TransactionBuilder
     */
     public function withLatLong($type, $latitude, $longitude)
    {
        $this->_model['addresses'][$type] = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        return $this;
    }

    /**
     * Add an address to this line
     *
     * @param   string              type        Address Type (See AddressType::* for a list of allowable values)
     * @param   string              line1       The street address, attention line, or business name of the location.
     * @param   string              line2       The street address, business name, or apartment/unit number of the location.
     * @param   string              line3       The street address or apartment/unit number of the location.
     * @param   string              city        City of the location.
     * @param   string              region      State or Region of the location.
     * @param   string              postalCode  Postal/zip code of the location.
     * @param   string              country     The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withLineAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $line = $this->GetMostRecentLine("WithLineAddress");
        $line['addresses'][$type] = [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'city' => $city,
            'region' => $region,
            'postalCode' => $postalCode,
            'country' => $country
        ];
        return $this;
    }

    /**
     * Add a document-level Tax Override to the transaction.
     *  - A TaxDate override requires a valid DateTime object to be passed.
     * TODO: Verify Tax Override constraints and add exceptions.
     *
     * @param   string              $type       Type of the Tax Override (See TaxOverrideType::* for a list of allowable values)
     * @param   string              $reason     Reason of the Tax Override.
     * @param   float               $taxAmount  Amount of tax to apply. Required for a TaxAmount Override.
     * @param   date                $taxDate    Date of a Tax Override. Required for a TaxDate Override.
     * @return  TransactionBuilder
     */
    public function withTaxOverride($type, $reason, $taxAmount, $taxDate)
    {
        $this->_model['taxOverride'] = [
            'type' => $type,
            'reason' => $reason,
            'taxAmount' => $taxAmount,
            'taxDate' => $taxDate
        ];

        // Continue building
        return $this;
    }

    /**
     * Add a line-level Tax Override to the current line.
     *  - A TaxDate override requires a valid DateTime object to be passed.
     * TODO: Verify Tax Override constraints and add exceptions.
     *
     * @param   string              $type        Type of the Tax Override (See TaxOverrideType::* for a list of allowable values)
     * @param   string              $reason      Reason of the Tax Override.
     * @param   float               $taxAmount   Amount of tax to apply. Required for a TaxAmount Override.
     * @param   date                $taxDate     Date of a Tax Override. Required for a TaxDate Override.
     * @return  TransactionBuilder
     */
    public function withLineTaxOverride($type, $reason, $taxAmount, $taxDate)
    {
        // Address the DateOverride constraint.
        if (($type == Constants::TAXOVERRIDETYPE_TAXDATE) && (empty($taxDate))) {
            throw new Exception("A valid date is required for a Tax Date Tax Override.");
        }

        $line = $this->GetMostRecentLine("WithLineTaxOverride");
        $line['taxOverride'] = [
            'type' => $type,
            'reason' => $reason,
            'taxAmount' => $taxAmount,
            'taxDate' => $taxDate
        ];

        // Continue building
        return $this;
    }

    /**
     * Add a line to this transaction
     *
     * @param   float               $amount      Value of the item.
     * @param   float               $quantity    Quantity of the item.
     * @param   string              $taxCode     Tax Code of the item. If left blank, the default item (P0000000) is assumed.
     * @return  TransactionBuilder
     */
    public function withLine($amount, $quantity, $taxCode)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => $quantity,
            'amount' => $amount,
            'taxCode' => $taxCode
        ];
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Add a line to this transaction
     *
     * @param   float               $amount      Value of the line
     * @param   string              $type        Address Type  (See AddressType::* for a list of allowable values)
     * @param   string              $line1       The street address, attention line, or business name of the location.
     * @param   string              $line2       The street address, business name, or apartment/unit number of the location.
     * @param   string              $line3       The street address or apartment/unit number of the location.
     * @param   string              $city        City of the location.
     * @param   string              $region      State or Region of the location.
     * @param   string              $postalCode  Postal/zip code of the location.
     * @param   string              $country     The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withSeparateAddressLine($amount, $type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => 1,
            'amount' => $amount,
            'addresses' => [
                $type => [
                    'line1' => $line1,
                    'line2' => $line2,
                    'line3' => $line3,
                    'city' => $city,
                    'region' => $region,
                    'postalCode' => $postalCode,
                    'country' => $country
                ]
            ]
        ];

        // Put this line in the model
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Add a line with an exemption to this transaction
     *
     * @param   float               $amount         The amount of this line item
     * @param   string              $exemptionCode  The exemption code for this line item
     * @return  TransactionBuilder
     */
    public function withExemptLine($amount, $exemptionCode)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => 1,
            'amount' => $amount,
            'exemptionCode' => $exemptionCode
        ];
        array_push($this->_model['lines'], $l); 
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Checks to see if the current model has a line.
     *
     * @return  TransactionBuilder
     */
    private function getMostRecentLine($memberName)
    {
        $c = count($this->_model['lines']);
        if ($c <= 0) {
            throw new Exception("No lines have been added. The $memberName method applies to the most recent line.  To use this function, first add a line.");
        }

        return $this->_model['lines'][$c-1];
    }

    /**
     * Create this transaction
     *
     * @return  TransactionModel
     */
    public function create()
    {
        return $this->_client->createTransaction(null, $this->_model);
    }

    /**
     * Create a transaction adjustment request that can be used with the AdjustTransaction() API call
     *
     * @return  AdjustTransactionModel
     */
    public function createAdjustmentRequest($desc, $reason)
    {
        return [
            'newTransaction' => $this->_model,
            'adjustmentDescription' => $desc,
            'adjustmentReason' => $reason
        ];
    }
}