# PLHW OAuth2 Client Provider

OAuth2 Client provides means to retrieve and cache an access token to be used whilst communicating with our API.

Currently this library only supports the `ClientCredentialGrant` OAuth2 methodology.


## Installation

```bash
composer require plhw/hf-oauth2-client:^0.1
```

## Usage

Your application needs to authenticate all requests to our api with a Bearer access token. This is done by adding a Authorization header.

There are many ways to do this, so we'll leave that part to you (for now)

Getting a token : see the example directory

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
