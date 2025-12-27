<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getTestClientId(): string
    {
        return 'test_client_id';
    }

    protected function getTestClientSecret(): string
    {
        return 'test_client_secret';
    }
}
