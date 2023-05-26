# Forwarded

Forwarded is a PHP library which parse the `Forwarded` request header as defined in [RFC 7239](https://datatracker.ietf.org/doc/html/rfc7239). The `Forwarded` header is a standardized method for identifying the originating IP address of a client connecting to a web server through an HTTP proxy or load balancer, for example.

Forwarded is distributed as a composer library.

## Getting started

Just add the dependency to your composer project:

```shell
composer require madeorsk/forwarded
```

## How to use


### The easy way

Usually, you just want to get the Forwarded header from your current request (for example when using PHP-FPM), or even just the IP address of the origin of the request.

```php
<?php

use Madeorsk\Forwarded\Forwarded;

$forwarded = Forwarded::get(); // Getting the parsed Forwarded header.

$originIp = Forwarded::getOriginIp(); // Getting the IP address of the origin request from the parsed Forwarded header.

```

### If you need more control

If you cannot obtain the header content from the classic standard way, you can call directly the Forwarded header parse.

```php
<?php

use Madeorsk\Forwarded\Parser;

// Get a Forwarded parsed header from its raw string.
$forwarded = (new Parser())->parse("for=192.0.2.43,for=\"[2001:db8:cafe::17]\",for=unknown");

// Get the Forwarded header content parsed in a raw associative array from its raw string.
$forwardedAssoc = (new Parser())->parseAssoc("for=192.0.2.43,for=\"[2001:db8:cafe::17]\",for=unknown");

```
