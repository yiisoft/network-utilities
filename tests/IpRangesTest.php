<?php

declare(strict_types=1);

namespace Yiisoft\NetworkUtilities\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\NetworkUtilities\IpRanges;

final class IpRangesTest extends TestCase
{
    public function testReadmeExample(): void
    {
        $ipRanges = new IpRanges(
            [
                '10.0.1.0/24',
                '2001:db0:1:2::/64',
                IpRanges::LOCALHOST,
                'myNetworkEu',
                '!' . IpRanges::ANY,
            ],
            [
                'myNetworkEu' => ['1.2.3.4/10', '5.6.7.8'],
            ],
        );

        $this->assertTrue($ipRanges->isAllowed('10.0.1.28/28'));
        $this->assertTrue($ipRanges->isAllowed('1.2.3.4'));
        $this->assertFalse($ipRanges->isAllowed('192.168.0.1'));
    }

    public function testNetworkAliasException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Network alias "*" already set as default');
        new IpRanges(['*'], ['*' => ['wrong']]);
    }

    public static function dataGetNetworks(): array
    {
        return [
            'default' => [
                [],
                [
                    '*' => ['any'],
                    'any' => ['0.0.0.0/0', '::/0'],
                    'private' => ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fd00::/8'],
                    'multicast' => ['224.0.0.0/4', 'ff00::/8'],
                    'linklocal' => ['169.254.0.0/16', 'fe80::/10'],
                    'localhost' => ['127.0.0.0/8', '::1'],
                    'documentation' => ['192.0.2.0/24', '198.51.100.0/24', '203.0.113.0/24', '2001:db8::/32'],
                    'system' => ['multicast', 'linklocal', 'localhost', 'documentation'],
                ],
            ],
            'custom' => [
                ['custom' => ['1.1.1.1/1', '2.2.2.2/2']],
                [
                    '*' => ['any'],
                    'any' => ['0.0.0.0/0', '::/0'],
                    'private' => ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fd00::/8'],
                    'multicast' => ['224.0.0.0/4', 'ff00::/8'],
                    'linklocal' => ['169.254.0.0/16', 'fe80::/10'],
                    'localhost' => ['127.0.0.0/8', '::1'],
                    'documentation' => ['192.0.2.0/24', '198.51.100.0/24', '203.0.113.0/24', '2001:db8::/32'],
                    'system' => ['multicast', 'linklocal', 'localhost', 'documentation'],
                    'custom' => ['1.1.1.1/1', '2.2.2.2/2'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetNetworks
     */
    public function testGetNetworks(array $networks, array $expected): void
    {
        $ipRanges = new IpRanges([], $networks);
        $this->assertSame($expected, $ipRanges->getNetworks());
    }

    public static function dataGetRange(): array
    {
        return [
            'ipv4' => [['10.0.0.1'], ['10.0.0.1']],
            'any' => [['192.168.0.32', 'fa::/32', 'any'], ['192.168.0.32', 'fa::/32', '0.0.0.0/0', '::/0']],
            'ipv4+!private' => [
                ['10.0.0.1', '!private'],
                ['10.0.0.1', '!10.0.0.0/8', '!172.16.0.0/12', '!192.168.0.0/16', '!fd00::/8'],
            ],
            'private+!system' => [
                ['private', '!system'],
                [
                    '10.0.0.0/8',
                    '172.16.0.0/12',
                    '192.168.0.0/16',
                    'fd00::/8',
                    '!224.0.0.0/4',
                    '!ff00::/8',
                    '!169.254.0.0/16',
                    '!fe80::/10',
                    '!127.0.0.0/8',
                    '!::1',
                    '!192.0.2.0/24',
                    '!198.51.100.0/24',
                    '!203.0.113.0/24',
                    '!2001:db8::/32',
                ],
            ],
            'containing duplicates' => [
                ['10.0.0.1', '10.0.0.2', '10.0.0.2', '10.0.0.3'],
                ['10.0.0.1', '10.0.0.2', 3 => '10.0.0.3'],
            ],
        ];
    }

    /**
     * @dataProvider dataGetRange
     */
    public function testGetRange(array $ranges, array $expected): void
    {
        $ipRanges = new IpRanges($ranges);
        $this->assertSame($expected, $ipRanges->getRanges());
    }

    public static function dataIsAllowed(): array
    {
        return [
            [true, '192.168.10.11'],
            [true, '10.0.0.1', ['10.0.0.1', '!10.0.0.0/8', '!babe::/8', 'any']],
            [true, '192.168.5.101', ['10.0.0.1', '!10.0.0.0/8', '!babe::/8', 'any']],
            [true, 'cafe::babe', ['10.0.0.1', '!10.0.0.0/8', '!babe::/8', 'any']],
            [true, '10.0.1.2', ['10.0.1.0/24']],
            [true, '10.0.1.2', ['10.0.1.0/24']],
            [true, '127.0.0.1', ['!10.0.1.0/24', '10.0.0.0/8', 'localhost']],
            [true, '10.0.1.2', ['10.0.1.0/24', '!10.0.0.0/8', 'localhost']],
            [true, '127.0.0.1', ['10.0.1.0/24', '!10.0.0.0/8', 'localhost']],
            [true, '10.0.1.28/28', ['10.0.1.0/24', '!10.0.0.0/8', 'localhost']],
            [true, '2001:db0:1:1::6', ['2001:db0:1:1::/64']],
            [true, '2001:db0:1:2::7', ['2001:db0:1:2::/64']],
            [true, '2001:db0:1:2::7', ['2001:db0:1:2::/64', '!2001:db0::/32']],
            [true, '10.0.1.2', ['10.0.1.0/24']],
            [true, '2001:db0:1:2::7', ['10.0.1.0/24', '2001:db0:1:2::/64', '127.0.0.1']],
            [true, '10.0.1.2', ['10.0.1.0/24', '2001:db0:1:2::/64', '127.0.0.1']],
            [true, '8.8.8.8', ['!system', 'any']],
            [true, '10.0.1.2', ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!any']],
            [true, '2001:db0:1:2::7', ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!any']],
            [true, '127.0.0.1', ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!any']],
            [true, '10.0.1.28/28', ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!any']],
            [true, '1.2.3.4', ['myNetworkEu'], ['myNetworkEu' => ['1.2.3.4/10', '5.6.7.8']]],
            [true, '5.6.7.8', ['myNetworkEu'], ['myNetworkEu' => ['1.2.3.4/10', '5.6.7.8']]],
            [false, 'babe::cafe', ['10.0.0.1', '!10.0.0.0/8', '!babe::/8', 'any']],
            [false, '10.0.0.2', ['10.0.0.1', '!10.0.0.0/8', '!babe::/8', 'any']],
            [false, '192.5.1.1', ['10.0.1.0/24']],
            [false, '10.0.3.2', ['10.0.1.0/24']],
            [false, '10.0.1.2', ['!10.0.1.0/24', '10.0.0.0/8', 'localhost']],
            [false, '10.2.2.2', ['10.0.1.0/24', '!10.0.0.0/8', 'localhost']],
            [false, '10.0.1.1/22', ['10.0.1.0/24', '!10.0.0.0/8', 'localhost']],
            [false, '2001:db0:1:2::7', ['2001:db0:1:1::/64']],
            [false, '2001:db0:1:2::7', ['!2001:db0::/32', '2001:db0:1:2::/64']],
            [false, '192.5.1.1', ['10.0.1.0/24']],
            [false, '2001:db0:1:2::7', ['10.0.1.0/24']],
            [false, '10.0.3.2', ['10.0.1.0/24', '2001:db0:1:2::/64', '127.0.0.1']],
            [false, '127.0.0.1', ['!system', 'any']],
            [false, 'fe80::face', ['!system', 'any']],
            [false, '10.2.2.2', ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!any']],
            [false, '10.0.1.1/22', ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!any']],
        ];
    }

    /**
     * @dataProvider dataIsAllowed
     */
    public function testIsAllowed(bool $expected, string $ip, array $ranges = [], array $networks = []): void
    {
        $ipRanges = new IpRanges($ranges, $networks);
        $this->assertSame($expected, $ipRanges->isAllowed($ip));
    }
}
