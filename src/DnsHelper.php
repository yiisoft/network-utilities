<?php

declare(strict_types=1);

namespace Yiisoft\NetworkUtilities;

use RuntimeException;

final class DnsHelper
{
    /**
     * Checks DNS MX record availability.
     *
     * @param string $hostname hostname without dot at end
     *
     * @return bool
     */
    public static function existsMx(string $hostname): bool
    {
        /** @psalm-suppress InvalidArgument, MixedArgumentTypeCoercion */
        set_error_handler(
            static function (int $errorNumber, string $errorString) use ($hostname): ?bool {
                throw new RuntimeException(
                    sprintf('Failed to get DNS record "%s". ', $hostname) . $errorString,
                    $errorNumber
                );
            }
        );
        $hostname = rtrim($hostname, '.') . '.';
        $result = dns_get_record($hostname, DNS_MX);

        restore_error_handler();

        return count($result) > 0;
    }

    /**
     * Checks DNS A record availability.
     *
     * @param string $hostname
     *
     * @return bool
     */
    public static function existsA(string $hostname): bool
    {
        /** @psalm-suppress InvalidArgument, MixedArgumentTypeCoercion */
        set_error_handler(
            static function (int $errorNumber, string $errorString) use ($hostname): ?bool {
                throw new RuntimeException(
                    sprintf('Failed to get DNS record "%s". ', $hostname) . $errorString,
                    $errorNumber
                );
            }
        );

        $result = dns_get_record($hostname, DNS_A);

        restore_error_handler();

        return count($result) > 0;
    }

    /**
     * Checks email's domain availability.
     *
     * @link https://tools.ietf.org/html/rfc5321#section-5
     *
     * @param string $hostnameOrEmail
     *
     * @return bool
     */
    public static function acceptsEmails(string $hostnameOrEmail): bool
    {
        if (strpos($hostnameOrEmail, '@') !== false) {
            [, $hostnameOrEmail] = explode('@', $hostnameOrEmail, 2);
        }
        return self::existsMx($hostnameOrEmail) || self::existsA($hostnameOrEmail);
    }
}
