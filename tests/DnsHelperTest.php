<?php

namespace Yiisoft\NetworkUtilities\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\NetworkUtilities\DnsHelper;

/**
 * @group potentially-slow
 */
class DnsHelperTest extends TestCase
{
    private const NOT_EXISTS_DOMAIN = 'not-exist-for-ever.eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeedomain.xxxxxxxxxxxxxxxxxxxxxxx';
    private const NOT_EXISTS_DOMAIN_EMAIL = 'any@' . self::NOT_EXISTS_DOMAIN;

    public function testMx(): void
    {
        $this->assertTrue(DnsHelper::checkMx('google.com'));
        $this->assertFalse(DnsHelper::checkMx(self::NOT_EXISTS_DOMAIN));
    }

    public function testA(): void
    {
        $this->assertTrue(DnsHelper::checkA('google.com'));
        $this->assertFalse(DnsHelper::checkA(self::NOT_EXISTS_DOMAIN));
    }

    public function testForEmail(): void
    {
        $this->assertTrue(DnsHelper::checkForEmail('google.com'));
        $this->assertTrue(DnsHelper::checkForEmail('noreply@google.com'));
        $this->assertFalse(DnsHelper::checkForEmail(self::NOT_EXISTS_DOMAIN));
        $this->assertFalse(DnsHelper::checkForEmail(self::NOT_EXISTS_DOMAIN_EMAIL));
    }
}