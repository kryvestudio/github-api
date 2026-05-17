<?php

namespace Kryve\GithubApi\Api;

use Kryve\GithubApi\Client;

class User
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function get(string $username): array
    {
        return $this->client->get('/users/' . $username);
    }
}
