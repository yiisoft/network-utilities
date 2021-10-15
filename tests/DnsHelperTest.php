<?php

declare(strict_types=1);

namespace Yiisoft\NetworkUtilities\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\NetworkUtilities\DnsHelper;

/**
 * @group potentially-slow
 */
final class DnsHelperTest extends TestCase
{
    private const NOT_EXISTS_DOMAIN = 'non-exist-for-everrrrrr.domain';
    private const NOT_EXISTS_DOMAIN_EMAIL = 'any@' . self::NOT_EXISTS_DOMAIN;

    public function testMx(): void
    {
        $this->assertTrue(DnsHelper::existsMx('google.com'));
        $this->assertFalse(DnsHelper::existsMx(self::NOT_EXISTS_DOMAIN));
    }

    public function testMxWithWrongDomain(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to get DNS record "wrong/domain".');
        DnsHelper::existsMx('wrong/domain');
    }

    public function testA(): void
    {
        $this->assertTrue(DnsHelper::existsA('google.com'));
        $this->assertFalse(DnsHelper::existsA(self::NOT_EXISTS_DOMAIN));
    }

    public function testAWithWrongDomain(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to get DNS record "wrong/domain".');
        DnsHelper::existsA('wrong/domain');
    }

    public function testAcceptsEmail(): void
    {
        $this->assertTrue(DnsHelper::acceptsEmails('google.com'));
        $this->assertTrue(DnsHelper::acceptsEmails('noreply@google.com'));
        $this->assertFalse(DnsHelper::acceptsEmails(self::NOT_EXISTS_DOMAIN));
        $this->assertFalse(DnsHelper::acceptsEmails(self::NOT_EXISTS_DOMAIN_EMAIL));
    }
}
