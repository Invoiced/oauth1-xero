# Xero Provider for OAuth 1.0 Client

[![Latest Stable Version](https://poser.pugx.org/Invoiced/oauth1-xero/v/stable.svg?style=flat)](https://packagist.org/packages/Invoiced/oauth1-xero)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Build Status](https://travis-ci.org/Invoiced/oauth1-xero.svg?branch=master&style=flat)](https://travis-ci.org/Invoiced/oauth1-xero)
[![Coverage Status](https://coveralls.io/repos/Invoiced/oauth1-xero/badge.svg?style=flat)](https://coveralls.io/r/Invoiced/oauth1-xero)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Invoiced/oauth1-xero/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Invoiced/oauth1-xero/?branch=master)
[![Total Downloads](https://poser.pugx.org/Invoiced/oauth1-xero/downloads.svg?style=flat)](https://packagist.org/packages/Invoiced/oauth1-xero)
[![HHVM Status](http://hhvm.h4cc.de/badge/Invoiced/oauth1-xero.svg?style=flat)](http://hhvm.h4cc.de/package/Invoiced/oauth1-xero)


This package provides Xero OAuth 1.0 support for the PHP League's [OAuth 1.0 Client](https://github.com/thephpleague/oauth1-client).

## Installation

To install, use composer:

```
composer require invoiced/oauth1-xero
```

## Usage

Usage is the same as The League's OAuth client, using `Invoiced\OAuth1\Client\Server\Xero` as the provider.

### Public API

Follows [Xero Public Applications](https://developer.xero.com/documentation/auth-and-limits/public-applications).

```php
$server = new Invoiced\OAuth1\Client\Server\Xero([
    'identifier'      => 'your-identifier',
    'secret'          => 'your-secret',
    'callback_uri'    => 'https://your-callback-uri/',
    'partner'         => false,
]);
```

### Private API

Follows [Xero Private Applications](https://developer.xero.com/documentation/auth-and-limits/private-applications).

```php
$server = new Invoiced\OAuth1\Client\Server\Xero([
    'identifier'      => 'your-identifier',
    'secret'          => 'your-secret',
    'callback_uri'    => 'https://your-callback-uri/',
    'partner'         => false,
    'rsa_private_key' => '/path/private.pem',
    'rsa_public_key'  => '/path/public.pem',
]);
```

### Partner API

Follows [Xero Partner Applications](https://developer.xero.com/documentation/auth-and-limits/partner-applications).

```php
$server = new Invoiced\OAuth1\Client\Server\Xero([
    'identifier'      => 'your-identifier',
    'secret'          => 'your-secret',
    'callback_uri'    => 'https://your-callback-uri/',
    'rsa_private_key' => '/path/private.pem',
    'rsa_public_key'  => '/path/public.pem',
]);
```
