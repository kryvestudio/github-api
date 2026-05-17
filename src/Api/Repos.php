<?php

namespace Kryve\GithubApi\Api;

use Kryve\GithubApi\Client;

class Repos
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function forOrg(string $org, array $params = []): array
    {
        return $this->client->get('/orgs/' . $org . '/repos', $params);
    }

    public function forUser(string $username, array $params = []): array
    {
        return $this->client->get('/users/' . $username . '/repos', $params);
    }
}
