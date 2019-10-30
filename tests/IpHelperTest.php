<?php

namespace Yiisoft\NetworkUtilities\Tests;


use InvalidArgumentException;
use Yiisoft\NetworkUtilities\IpHelper;
use PHPUnit\Framework\TestCase;

class IpHelperTest extends TestCase
{
    /**
     * @dataProvider getIpVersionProvider
     */
    public function testGetIpVersion(string $value, bool $validation, ?int $expectedVersion, ?string $expectedException = null, string $message = ''): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }
        $version = IpHelper::getIpVersion($value, $validation);
        if ($expectedException === null) {
            $this->assertSame($expectedVersion, $version, $message);
        }
    }

    public function getIpVersionProvider(): array
    {
        return [
            'emptyString' => ['', false, null, \InvalidArgumentException::class],
            'emptyStringValidate' => ['', true, null, \InvalidArgumentException::class],
            'tooShort' => ['1', false, null, \InvalidArgumentException::class],
            'tooShortValidate' => ['1', true, null, \InvalidArgumentException::class],
            'ipv4Minimal' => ['0.0.0.0', false, IpHelper::IPV4],
            'ipv4TooShort' => ['0.0.0.', false, null, \InvalidArgumentException::class],
            'ipv4' => ['192.168.0.1', false, IpHelper::IPV4],
            'ipv4Max' => ['255.255.255.255', false, IpHelper::IPV4],
            'ipv4MaxValidation' => ['255.255.255.255', true, IpHelper::IPV4],
            'ipv4OverMax' => ['255.255.255.256', true, null, \InvalidArgumentException::class],
            'ipv4Cidr' => ['192.168.0.1/24', false, IpHelper::IPV4, null, 'IPv4 with CIDR is resolved correctly'],
            'ipv4CidrValidation' => ['192.168.0.1/24', true, null, \InvalidArgumentException::class],
            'ipv6' => ['fb01::1', false, IpHelper::IPV6],
            'ipv6Cidr' => ['fb01::1/24', false, IpHelper::IPV6, null, 'IPv6 with CIDR is resolved correctly'],
            'ipv6CidrValidation' => ['fb01::1/24', true, null, \InvalidArgumentException::class],
            'ipv6Minimal' => ['::', false, IpHelper::IPV6],
            'ipv6MinimalValidation' => ['::', true, IpHelper::IPV6],
            'ipv6MappedIpv4' => ['::ffff:192.168.0.2', false, IpHelper::IPV6],
            'ipv6MappedIpv4Validation' => ['::ffff:192.168.0.2', true, IpHelper::IPV6],
            'ipv6Full' => ['fa01:0000:0000:0000:0000:0000:0000:0001', false, IpHelper::IPV6],
            'ipv6FullValidation' => ['fa01:0000:0000:0000:0000:0000:0000:0001', true, IpHelper::IPV6],
        ];
    }

    /**
     * @dataProvider expandIpv6Provider
     */
    public function testExpandIpv6(string $value, string $expected): void
    {
        $expanded = IpHelper::expandIPv6($value);
        $this->assertSame($expected, $expanded);
    }

    public function expandIpv6Provider(): array
    {
        return [
            ['fa01::1', 'fa01:0000:0000:0000:0000:0000:0000:0001'],
            ['2001:db0:1:2::7', '2001:0db0:0001:0002:0000:0000:0000:0007'],
        ];
    }

    public function testIpv6ExpandingWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IpHelper::expandIPv6('fa01::1/64');
    }

    /**
     * @dataProvider ip2binProvider
     */
    public function testIp2bin(string $value, string $expected): void
    {
        $result = IpHelper::ip2bin($value);
        $this->assertSame($expected, $result);
    }

    public function ip2binProvider(): array
    {
        return [
            ['192.168.1.1', '11000000101010000000000100000001'],
            ['fa01:0000:0000:0000:0000:0000:0000:0001', '11111010000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001'],
            ['fa01::1', '11111010000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001'],
            ['2620:0:2d0:200::7', '00100110001000000000000000000000000000101101000000000010000000000000000000000000000000000000000000000000000000000000000000000111'],
        ];
    }

    /**
     * @dataProvider inRangeProvider
     */
    public function testInRange(string $value, string $range, bool $expected): void
    {
        $result = IpHelper::inRange($value, $range);
        $this->assertSame($expected, $result);
    }

    public function inRangeProvider(): array
    {
        return [
            ['192.168.1.1/24', '192.168.0.0/23', true],
            ['192.168.1.1/24', '192.168.0.0/24', false],
            ['192.168.1.1/24', '0.0.0.0/0', true],
            ['192.168.1.1/32', '192.168.1.1', true],
            ['192.168.1.1/32', '192.168.1.1/32', true],
            ['192.168.1.1', '192.168.1.1/32', true],
            ['fa01::1/128', 'fa01::/64', true],
            ['fa01::1/128', 'fa01::1/128', true],
            ['fa01::1/64', 'fa01::1/128', false],
            ['2620:0:0:0:0:0:0:0', '2620:0:2d0:200::7/32', true],
        ];
    }

    public function getCidrBitsDataProvider(): array
    {
        return [
            'invalidEmpty' => ['', null, \InvalidArgumentException::class],
            'invalidTooShort' => ['1', null, \InvalidArgumentException::class],
            'invalidIp' => ['999.999.999.999', null, \InvalidArgumentException::class],
            'invalidIpCidr' => ['999.999.999.999/22', null, \InvalidArgumentException::class],
            'shortestIp' => ['::', 128],
            'ipv4' => ['127.0.0.1', 32],
            'ipv6' => ['::1', 128],
            'ipv4-negative' => ['127.0.0.1/-1', null, \InvalidArgumentException::class],
            'ipv4-min' => ['127.0.0.1/0', 0],
            'ipv4-normal' => ['127.0.0.1/13', 13],
            'ipv4-max' => ['127.0.0.1/32', 32],
            'ipv4-overflow' => ['127.0.0.1/33', null, \InvalidArgumentException::class],
            'ipv6-negative' => ['::1/-1', null, \InvalidArgumentException::class],
            'ipv6-min' => ['::1/0', 0],
            'ipv6-normal' => ['::1/72', 72],
            'ipv6-normalExpanded' => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334/23', 23],
            'ipv6-normalIpv4Mapped' => ['::ffff:192.0.2.128/109', 109],
            'ipv6-max' => ['::1/128', 128],
            'ipv6-overflow' => ['::1/129', null, \InvalidArgumentException::class],
        ];
    }

    /**
     * @dataProvider getCidrBitsDataProvider
     */
    public function testGetCidrBits(string $ip, ?int $expectedCidr, ?string $expectedException = null): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }
        $this->assertSame($expectedCidr, IpHelper::getCidrBits($ip));
    }

    public function ipv4RegexpDataProvider(): array
    {
        return [
            'min' => ['0.0.0.0', 1],
            'max' => ['255.255.255.255', 1],
            'overflow' => ['255.255.255.256', 0],
            'overflow2' => ['256.0.0.0', 0],
            'random' => ['31.88.247.9', 1],
            'broken' => ['1.', 0],
            'broken2' => ['1.1', 0],
            'broken3' => ['1.1.', 0],
            'broken4' => ['1.1.1.', 0],
            'empty' => ['', 0],
            'invalid' => ['apple', 0],
            'trailingLeadingSpace' => [' 0.0.0.0 ', 0],
        ];
    }

    /**
     * @dataProvider ipv4RegexpDataProvider
     */
    public function testIpv4Regexp(string $ip, int $result): void
    {
        $this->assertSame($result, preg_match(IpHelper::IPV4_REGEXP, $ip));
    }

    public function ipv6RegexpDataProvider(): array
    {
        return [
            'shortest' => ['::', 1],
            'full' => ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 1],
            'overflow' => ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffg', 0],
            'overflow2' => ['fffg:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 0],
            'empty' => ['', 0],
            'invalid' => ['apple', 0],
            'trailingLeadingSpace' => [' :: ', 0],
            'mappedIpv4' => ['::ffff:192.168.0.2', 1],
            'random' => ['2001:db8:a::123', 1],
            'random2' => ['2001:db8:85a3::8a2e:370:7334', 1],
            'allHexCharacters' => ['0123:4567:89ab:cdef:CDEF:AB12:1:1', 1],
            'variableGroupLength' => ['1:12:123:1234::', 1]
        ];
    }

    /**
     * @dataProvider ipv6RegexpDataProvider
     */
    public function testIpv6Regexp(string $ip, int $result): void
    {
        $this->assertSame($result, preg_match(IpHelper::IPV6_REGEXP, $ip));
    }
}
