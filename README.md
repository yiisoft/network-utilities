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
[![Build Status](https://travis-ci.com/yiisoft/network-utilities.svg?branch=master)](https://travis-ci.com/yiisoft/network-utilities)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/network-utilities/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/network-utilities/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/network-utilities/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/network-utilities/?branch=master)

## General usage

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
