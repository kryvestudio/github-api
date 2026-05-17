<?php

namespace Kryve\GithubApi\Api;

use Kryve\GithubApi\Client;

class Issues
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function list(string $owner, string $repo, array $params = []): array
    {
        return $this->client->get('/repos/' . $owner . '/' . $repo . '/issues', $params);
    }
}
