<?php

use OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token\AbstractToken;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    const PARAMS = [
        'param1',
        'param2',
    ];

    public function testTokenCreationAndExtraction()
    {
        $testToken = new TestToken();

        $token = $testToken->createToken(self::PARAMS);

        /** @noinspection PhpUnhandledExceptionInspection */
        $extracted = TestToken::extractToken($token, count(self::PARAMS));

        $this->assertEquals($token, $extracted);
    }
}

class TestToken extends AbstractToken
{
    public static function extractToken(string $token, int $expectedSize = null): array
    {
        return parent::extractToken($token, $expectedSize);
    }



    public function createToken(array $params): string
    {
        return parent::createToken($params);
    }
}