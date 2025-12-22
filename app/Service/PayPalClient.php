<?php

namespace App\Service;

use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;

class PayPalClient
{
    public static function client()
    {
        return PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    config('services.paypal.sandbox.client_id'),
                    config('services.paypal.sandbox.client_secret')
                )
            )
            ->environment(Environment::SANDBOX)
            ->build();
    }
}
