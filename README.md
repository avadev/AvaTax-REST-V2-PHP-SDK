# AvaTax-REST-V2-PHP-SDK
AvaTax v2 SDK for languages using PHP.

# Build Status

Automatically built using Travis-CI.org

* ![](https://api.travis-ci.org/avadev/AvaTax-REST-V2-PHP-SDK.svg?branch=master&style=plastic)

# Using the PHP SDK

To use the AvaTax PHP SDK, you can download the SDK from Composer:

```
<?php

// Include the AvaTaxClient library
require_once '/src/AvaTaxClient.php';
use Avalara\AvaTaxClient;

// Create a new client
$client = new Avalara\AvaTaxClient('phpTestApp', '1.0', 'localhost', 'sandbox');
$client->withSecurity('myUsername', 'myPassword');

// Call 'Ping' to verify that we are connected
$p = $client->Ping();
if ($p->authenticated) {

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

}
?>
```
