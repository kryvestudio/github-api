<?php

namespace Kryve\GithubApi\Tests;

use PHPUnit\Framework\TestCase;
use Kryve\GithubApi\Client;
use Kryve\GithubApi\Exception\ApiException;

class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $token = getenv('GITHUB_TOKEN') ?: null;
        $this->client = new Client($token);
    }

    public function testGetUserReturnsArray(): void
    {
        $result = $this->client->get('/users/kryvestudio');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('login', $result);
    }

    public function testInvalidEndpointThrowsException(): void
    {
        $this->expectException(ApiException::class);
        $this->client->get('/users/nonexistent_user_xyz_999');
    }
}
