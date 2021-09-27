# PHP Library for Mitek API
PHP API Library for Mitek Identity Cloud that provides authenticating and extracting data from identity documents. 

This library aims to provice all the API found on the [Mitek API Docs](https://docs.us.mitekcloud.com/#introduction) page.

### What's Mitek
Mitek is a Digital identity solutions that is designed to prevent fraud, increase conversions and streamline operations

Mitek uses OAuth v2 with JWT tokens for authorization and OpenID for authentication. This token-based standard leverages temporary tokens that provide access to a resource for a limited duration. In our production environments, these tokens will be valid for 5 minutes from the time the token is first issued. When you are requesting one of these temporary tokens, you need to provide the resource you are trying to access (in OAuth this is called the scope). It is best practice to request only the minimally required scope(s) for the operation(s) in order to limit the rights of the access token provided.

for more Mitek see https://docs.us.mitekcloud.com/#introduction 

# Usage


## Mobile Verify - Auto

### Code

```php
<?php

use Unbank\Identity\Mitek\MitekAPI;


$mitek = new MitekAPI(
    null,
    $request->input('sandbox', null)
);
$mitek->auth(
    $request->input('client_id', config('mitek.client_id')),
    $request->input('client_secret', config('mitek.client_secret')),
    $request->input('grant_type', config('mitek.grant_type')),
    $request->input('scope', config('mitek.scope'))
);
$response = $mitek->verify(
    $request->input('images', array()),
    $user->getCustomerReferenceId(true),
    $request->input('type','IdDocument')
);

echo $response['evidence']["extractedData"]["name"]["fullName"]

?>
```

For more information about Verify Auto - Request, see
https://docs.us.mitekcloud.com/#mobile-verify-auto

### Result

```
OSHANE BAILEY
```


For more information about Verify Auto - Response, see
https://docs.us.mitekcloud.com/#verify-auto-response


# Issue Tracker
Please use the `issue tracker <http://github.com/unbank/mitek-api/issues>`_


# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
