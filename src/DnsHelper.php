<?php

declare(strict_types=1);

namespace Yiisoft\NetworkUtilities;

use RuntimeException;

/**
 * `DnsHelper` contains static methods to work with DNS.
 */
final class DnsHelper
{
    /**
     * Checks DNS MX record availability.
     *
     * @param string $hostname Hostname without dot at end.
     *
     * @return bool Whether MX record exists.
     */
    public static function existsMx(string $hostname): bool
    {
        set_error_handler(static function (int $errorNumber, string $errorString) use ($hostname): bool {
            throw new RuntimeException(
                sprintf('Failed to get DNS record "%s". ', $hostname) . $errorString,
                $errorNumber
            );
        });

        $hostname = rtrim($hostname, '.') . '.';
        /**
         * @var array $result We catch errors by `set_error_handler()` and throw exceptions if something goes wrong.
         * So `dns_get_record()` will always return an array.
         */
        $result = dns_get_record($hostname, DNS_MX);

        restore_error_handler();

        return count($result) > 0;
    }

    /**
     * Checks DNS A record availability.
     *
     * @param string $hostname Hostname without dot at end.
     *
     * @return bool Whether A records exists.
     */
    public static function existsA(string $hostname): bool
    {
        set_error_handler(static function (int $errorNumber, string $errorString) use ($hostname): bool {
            throw new RuntimeException(
                sprintf('Failed to get DNS record "%s". ', $hostname) . $errorString,
                $errorNumber
            );
        });

        /**
         * @var array $result We catch errors by `set_error_handler()` and throw exceptions if something goes wrong.
         * So `dns_get_record()` will always return an array.
         */
        $result = dns_get_record($hostname, DNS_A);

        restore_error_handler();

        return count($result) > 0;
    }

    /**
     * Checks email's domain availability.
     *
     * @link https://tools.ietf.org/html/rfc5321#section-5
     *
     * @param string $hostnameOrEmail Hostname without dot at end or an email.
     *
     * @return bool Whether email domain is available.
     */
    public static function acceptsEmails(string $hostnameOrEmail): bool
    {
        if (strpos($hostnameOrEmail, '@') !== false) {
            /**
             * @psalm-suppress PossiblyUndefinedArrayOffset In this case `explode()` always returns an array with 2 elements.
             */
            [, $hostnameOrEmail] = explode('@', $hostnameOrEmail, 2);
        }
        return self::existsMx($hostnameOrEmail) || self::existsA($hostnameOrEmail);
    }
}
