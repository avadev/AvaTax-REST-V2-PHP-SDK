# AvaTax-REST-V2-PHP-SDK

AvaTax v2 SDK for languages using PHP.

The AvaTax REST v2 API is a fully REST implementation of Avalara's world-class tax service, AvaTax.  For more information about AvaTax REST v2, please visit [Avalara's Developer Network](http://developer.avalara.com/) or view the [online Swagger documentation](https://sandbox-rest.avatax.com/swagger/ui/index.html).

# Build Status

Automatically built using Travis-CI.org

* ![](https://api.travis-ci.org/avadev/AvaTax-REST-V2-PHP-SDK.svg?branch=master&style=plastic)

# Installing the PHP SDK

The AvaTax PHP SDK is available as either a single file that you can download or a Composer package.  It requires [PHP Guzzle](http://docs.guzzlephp.org/en/latest/) and PHP 5.6 or later.

To download the AvaTax SDK as a single file, follow this link:
* https://raw.githubusercontent.com/avadev/AvaTax-REST-V2-PHP-SDK/master/src/AvaTaxClient.php

To use the AvaTax PHP SDK from Composer, specify `use Avalara\AvaTaxClient` in your program and run `composer install`.

# Using the PHP SDK

The PHP SDK uses a fluent interface to define a connection to AvaTax and to make API calls to calculate tax on transactions.  Here's an example of connecting to the API.

```
<?php

// Include the AvaTaxClient library
require_once '/src/AvaTaxClient.php';
use Avalara\AvaTaxClient;

// Create a new client
$client = new Avalara\AvaTaxClient('phpTestApp', '1.0', 'localhost', 'sandbox');
$client->withSecurity('myUsername', 'myPassword');

// If I am debugging, I may wish to call 'Ping' to verify whether I am connected to the server; but this is not required
$p = $client->Ping();
if ($p->authenticated) {
    echo 'Success!'
}

// Create a transaction using the fluent transaction builder
$tb = new Avalara\TransactionBuilder($client, $testCompany->companyCode, Avalara\DocumentType::C_SALESINVOICE, 'ABC');
$t = $tb->withAddress('ShipFrom', '123 Main Street', null, null, 'Irvine', 'CA', '92615', 'US')
    ->withAddress('ShipTo', '100 Ravine Lane', null, null, 'Bainbridge Island', 'WA', '98110', 'US')
    ->withLine(100.0, 1, "P0000000")
    ->withLine(1234.56, 1, "P0000000")
    ->withExemptLine(50.0, "NT")
    ->withLine(2000.0, 1, "P0000000")
    ->withLineAddress(Avalara\TransactionAddressType::C_SHIPFROM, "123 Main Street", null, null, "Irvine", "CA", "92615", "US")
    ->withLineAddress(Avalara\TransactionAddressType::C_SHIPTO, "1500 Broadway", null, null, "New York", "NY", "10019", "US")
    ->withLine(50.0, 1, "FR010000")
    ->create();

?>
```
