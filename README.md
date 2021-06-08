# PLHW Api Client

PLHW API Client provides means to authenticate clients against our OAuth2 server and issue authorized requests to our api endpoints.


## Installation

```bash
composer require plhw/hf-api-client
```

## Example API calls

Once you have succesfully installed the application, some example scripts are located at. To use them first copy an configuration file to your app root. 

```bash
cp ./vendor/plhw/hf-api-client/example/.hf-api-client-secrets.php.dist ./.hf-api-client-secrets.php
```

Open `.hf-api-client-secrets.php` and configure it with credentials you got from us.

Finally the example scripts are configured to use `data/cache` as directory for its cached accss tokens. This directory must exist (only for the examples).

Now you can run the example scripts;

```bash
php ./vendor/plhw/hf-api-client/example/practicesPos.php
```

## Usage

To implement usage in your application you can have a look at the example scripts.

```php
$options = \HF\ApiClient\Options\Options::fromArray(
    [
        'server_uri'    => 'https://api.plhw.nl',
        'client_id'     => 'id',
        'client_secret' => 'secret',    
        'scope'         => 'customer_pos',
        'grant_type'    => 'client_credentials',
    ]
);

$api = \HF\ApiClient\ApiClient::createClient($options, /* $cache OPTIONAL */);
```

The above is all that is nessesary to configure, though I recommend you tweak the caching meganism. Currently we use the [zendframework/zend-cache](https://docs.zendframework.com/zend-cache/) component. This might change when v3 is released to use the psr-7 caching FIG standard.

When you have `ApiClient` instance you can use it by calling methods as defined in `/vendor/plhw/hf-api-client/data/v1` on it. Additionally [API documentation](https://api.plhw.nl/docs) can be found here.

for example, search the published practices around a coordinate.

```php
$results = $api->customer_posAroundCoordinate('52.3629882,4.8593175', 15000, 'insoles');

if ($api->isSuccess()) {
    foreach ($results['data'] as $result) {
        printf("Practice %s on %skm\n", $result['attributes']['name'],
            round(($result['attributes']['distance'] / 100)) / 10);
        printf(" - sells %s\n", implode(', ', $result['attributes']['products']));
    }
} else {
    printf("Error (%d): %s\n", $api->getStatusCode(), $result);
}
```

## Under the hood

When you call any method on the APIClient instance an access token is requested from our OAuth2 server. This access token is then cached for aditional uses up to the moment it expires or is deleted. It will then get a new access token.

Any calls to our API are now `signed` with that access token and is used by our permission system to determain if you have access or not.

Our API will accept and return json payload with are automaticly (de)encoded.

### OAuth2 ClientCredentialsGrant

For machine to machine communication OAuth2 ClientCredentialGrant is appropiate. 
You must obtain the following information from us.

1. client ID
2. client secret
3. scope

Your client side application should communicate via a proxy to our server.

```

               
   [webbrowser] <----- internet ----->   [application]   <----- internet -----> [api.plhw.nl]

1.  request data    -> request ->      do we have valid access token?

2.                                     NO, get access token   -> request  -> id,secret,scope

3.                                                                                validate request

4.                                     store token in cache   <- response <- access token
                                                                     
5.                                     YES, request & token.  -> request  -> validate token

6.                                     data                   <- response <- data

7.  data             <- response <-    data

```




Run all examples in the 'commerce' domain:

```
find ./example/commerce/ -maxdepth 1 -type f -exec {} \;
```

## Upgrading

v2 has been rewritten for php 8+. Principals are the same but there are a few minor changes that will break your application.

Please see our examples to see how you interact with the api.
