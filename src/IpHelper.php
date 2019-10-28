<?php

namespace Yiisoft\NetworkUtilities;

class IpHelper
{
    public const IPV4 = 4;
    public const IPV6 = 6;

    /**
     * The length of IPv6 address in bits
     */
    private const IPV6_ADDRESS_LENGTH = 128;
    /**
     * The length of IPv4 address in bits
     */
    private const IPV4_ADDRESS_LENGTH = 32;


    /**
     * Gets the IP version. Does not perform IP address validation.
     *
     * @param string $ip the valid IPv4 or IPv6 address.
     * @return int [[IPV4]] or [[IPV6]]
     */
    public static function getIpVersion(string $ip): int
    {
        return strpos($ip, ':') === false ? self::IPV4 : self::IPV6;
    }

    /**
     * Checks whether IP address or subnet $subnet is contained by $subnet.
     *
     * For example, the following code checks whether subnet `192.168.1.0/24` is in subnet `192.168.0.0/22`:
     *
     * ```php
     * IpHelper::inRange('192.168.1.0/24', '192.168.0.0/22'); // true
     * ```
     *
     * In case you need to check whether a single IP address `192.168.1.21` is in the subnet `192.168.1.0/24`,
     * you can use any of theses examples:
     *
     * ```php
     * IpHelper::inRange('192.168.1.21', '192.168.1.0/24'); // true
     * IpHelper::inRange('192.168.1.21/32', '192.168.1.0/24'); // true
     * ```
     *
     * @param string $subnet the valid IPv4 or IPv6 address or CIDR range, e.g.: `10.0.0.0/8` or `2001:af::/64`
     * @param string $range the valid IPv4 or IPv6 CIDR range, e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool whether $subnet is contained by $range
     *
     * @see https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing
     */
    public static function inRange(string $subnet, string $range): bool
    {
        [$ip, $mask] = array_pad(explode('/', $subnet), 2, null);
        [$net, $netMask] = array_pad(explode('/', $range), 2, null);

        $ipVersion = static::getIpVersion($ip);
        $netVersion = static::getIpVersion($net);
        if ($ipVersion !== $netVersion) {
            return false;
        }

        $maxMask = $ipVersion === self::IPV4 ? self::IPV4_ADDRESS_LENGTH : self::IPV6_ADDRESS_LENGTH;
        $mask = $mask ?? $maxMask;
        $netMask = $netMask ?? $maxMask;

        $binIp = static::ip2bin($ip);
        $binNet = static::ip2bin($net);
        $masked = substr($binNet, 0, $netMask);

        return ($masked === '' || strpos($binIp, $masked) === 0) && $mask >= $netMask;
    }

    /**
     * Expands an IPv6 address to it's full notation.
     *
     * For example `2001:db8::1` will be expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`
     *
     * @param string $ip the original valid IPv6 address
     * @return string the expanded IPv6 address
     */
    public static function expandIPv6(string $ip): string
    {
        $ipRaw = @inet_pton($ip);
        if ($ipRaw === false) {
            if (@inet_pton('::1') === false) {
                throw new \RuntimeException('IPv6 is not supported by inet_pton()!');
            }
            throw new \InvalidArgumentException("Unrecognized address $ip");
        }
        $hex = unpack('H*hex', $ipRaw);
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * Converts IP address to bits representation.
     *
     * @param string $ip the valid IPv4 or IPv6 address
     * @return string bits as a string
     */
    public static function ip2bin(string $ip): string
    {
        $ipBinary = null;
        if (static::getIpVersion($ip) === self::IPV4) {
            $ipBinary = pack('N', ip2long($ip));
        } elseif (@inet_pton('::1') === false) {
            throw new \RuntimeException('IPv6 is not supported by inet_pton()!');
        } else {
            $ipBinary = inet_pton($ip);
        }

        $result = '';
        for ($i = 0, $iMax = strlen($ipBinary); $i < $iMax; $i += 4) {
            $result .= str_pad(decbin(unpack('N', substr($ipBinary, $i, 4))[1]), 32, '0', STR_PAD_LEFT);
        }
        return $result;
    }

    /**
     * Gets the bits from CIDR Notation.
     *
     * @param string $ip IP or IP with CIDR Notation (`127.0.0.1`, `2001:db8:a::123/64`)
     */
    public static function getCidrBits(string $ip): int
    {
        if (preg_match('/^(?<ip>.{2,}?)(?:\/(?<bits>-?\d+))?$/', $ip, $matches) === 0) {
            throw new \InvalidArgumentException("Unrecognized address $ip", 1);
        }
        $ipVersion = static::getIpVersion($matches['ip']);
        $maxBits = $ipVersion === self::IPV6 ? self::IPV6_ADDRESS_LENGTH : self::IPV4_ADDRESS_LENGTH;
        $bits = $matches['bits'] ?? null;
        if ($bits === null) {
            return $maxBits;
        }
        $bits = (int)$bits;
        if ($bits < 0) {
            throw new \InvalidArgumentException('The number of CIDR bits cannot be negative', 2);
        }
        if ($bits > $maxBits) {
            throw new \InvalidArgumentException("CIDR bits is greater than $bits", 3);
        }
        return $bits;
    }
}
