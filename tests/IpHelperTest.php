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
    public function testGetIpVersion(string $value, int $expected, string $message = ''): void
    {
        $version = IpHelper::getIpVersion($value);
        $this->assertSame($expected, $version, $message);
    }

    public function getIpVersionProvider(): array
    {
        return [
            ['192.168.0.1', IpHelper::IPV4],
            ['192.168.0.1/24', IpHelper::IPV4, 'IPv4 with CIDR is resolved correctly'],
            ['fb01::1', IpHelper::IPV6],
            ['fb01::1/24', IpHelper::IPV6, 'IPv6 with CIDR is resolved correctly'],
            ['', IpHelper::IPV4, 'Empty string is treated as IPv4'],
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
            ['', '00000000000000000000000000000000'],
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
}
