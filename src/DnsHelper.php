<?php

namespace Yiisoft\NetworkUtilities;

class DnsHelper
{
    /**
     * @param string $hostname hostname without dot at end
     */
    public static function checkMx(string $hostname): bool
    {
        $hostname .= '.';
        try {
            if (!@dns_check_record($hostname, 'MX')) {
                return false;
            }
            $result = @dns_get_record($hostname, DNS_MX);
            return $result !== false && count($result) > 0;
        } catch (\Throwable $t) {
        }
        return false;
    }

    public static function checkA(string $hostname): bool
    {
        try {
            if (!@dns_check_record($hostname, 'A')) {
                return false;
            }
            $result = @dns_get_record($hostname, DNS_A);
            return $result !== false && count($result) > 0;
        } catch (\Throwable $t) {
        }
        return false;
    }

    public static function checkForEmail(string $hostnameOrEmail): bool
    {
        if (strpos($hostnameOrEmail, '@') !== false) {
            [$void, $hostnameOrEmail] = explode('@', $hostnameOrEmail, 2);
        }
        return self::checkMx($hostnameOrEmail) || self::checkA($hostnameOrEmail);
    }
}