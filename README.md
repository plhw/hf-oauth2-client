# PLHW Api Client

PLHW API Client provides means to authenticate clients against with OAuth2 server and issue authorized requests to our api endpoints.


## Installation

```bash
composer require plhw/hf-api-client:^0.1
```

## Usage

```
$options = \HF\ApiClient\Options\Options::fromArray(
    [
        'server_uri'    => 'https://api.plhw.nl',
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,    
        'scope'         => 'customer_pos',
        'grant_type'    => 'client_credentials',
    ]
);

$api = \HF\ApiClient\ApiClient::createClient($options, $cache);

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

see api [documentation](https://api.plhw.nl/docs) for end points.

## ClientCredentialsGrant

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
