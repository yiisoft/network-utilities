<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii network utilities</h1>
    <br>
</p>

The package contains various network utilities useful for:

- Getting info about IP address
- Checking if IP is in a certain range
- Expanding IP v6
- Converiting IP to bits representation

[![Latest Stable Version](https://poser.pugx.org/yiisoft/network-utilities/v/stable.png)](https://packagist.org/packages/yiisoft/network-utilities)
[![Total Downloads](https://poser.pugx.org/yiisoft/network-utilities/downloads.png)](https://packagist.org/packages/yiisoft/network-utilities)
[![Build Status](https://github.com/yiisoft/network-utilities/workflows/build/badge.svg)](https://github.com/yiisoft/network-utilities/actions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/network-utilities/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/network-utilities/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/network-utilities/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/network-utilities/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fnetwork-utilities%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/network-utilities/master)
[![static analysis](https://github.com/yiisoft/network-utilities/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/network-utilities/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/network-utilities/coverage.svg)](https://shepherd.dev/github/yiisoft/network-utilities)

## General usage

### `IpHelper`

```php
use Yiisoft\NetworkUtilities\IpHelper;

// checking IP version
$version = IpHelper::getIpVersion('192.168.1.1');
if ($version === IpHelper::IPV4) {
    // ...
}

// checking if IP is in a certain range
if (!IpHelper::inRange('192.168.1.21/32', '192.168.1.0/24')) {
    throw new \RuntimeException('Access denied!');
}

// expanding IP v6
echo IpHelper::expandIPv6('2001:db8::1');

// converting IP to bits representation
echo IpHelper::ip2bin('192.168.1.1');

// gets bits from CIDR Notation
echo IpHelper::getCidrBits('192.168.1.21/32');
```

### `DnsHelper`

```php
use Yiisoft\NetworkUtilities\DnsHelper;

// checking DNS record availability
if(!DnsHelper::checkA('yiiframework.com')) {
  // record not found
}
```

## Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```php
./vendor/bin/infection
```

## Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```php
./vendor/bin/psalm
