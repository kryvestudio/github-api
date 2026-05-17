# Tutorial: Bikin PHP GitHub API Wrapper dari Nol

## Step 0 — Reset folder

```bash
cd "G:\My Drive\CODE - Program\kryvestudio-github-api"
Remove-Item -Recurse -Force src, tests, vendor, composer.lock, phpunit.xml, example.php, test-raw.php, test-package.php, .phpunit.result.cache
```

---

## Step 1 — Buat `composer.json`

Bikin file pertama: `composer.json`

```json
{
    "name": "kryvestudio/github-api",
    "description": "PHP wrapper untuk GitHub REST API oleh Kryve Studio",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "Kryve\\GithubApi\\": "src/"
        }
    },
    "require": {
        "php": ">=8.3",
        "ext-curl": "*",
        "ext-json": "*"
    }
}
```

Jalankan:
```bash
composer install
```

Cek: muncul folder `vendor/` dan file `vendor/autoload.php`.

---

## Step 2 — Buat `src/Exception/ApiException.php`

Buat folder `src/Exception/`, lalu bikin file:

```php
<?php

namespace Kryve\GithubApi\Exception;

class ApiException extends \RuntimeException
{
    private ?array $responseBody;

    public function __construct(string $message, int $statusCode = 0, ?array $responseBody = null)
    {
        parent::__construct($message, $statusCode);
        $this->responseBody = $responseBody;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
```

**Jelaskan:** Ini class khusus untuk error. Kalau GitHub balas 404/401/500, kita lempar error pake class ini biar jelas.

---

## Step 3 — Buat `src/Client.php`

Bikin file:

```php
<?php

namespace Kryve\GithubApi;

use Kryve\GithubApi\Exception\ApiException;

class Client
{
    private string $baseUrl;
    private string $token;
    private array $headers;

    public function __construct(?string $token = null, string $baseUrl = 'https://api.github.com')
    {
        $this->token = $token ?? '';
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = [
            'Accept: application/vnd.github.v3+json',
            'User-Agent: kryvestudio-github-api',
        ];

        if ($this->token !== '') {
            $this->headers[] = 'Authorization: Bearer ' . $this->token;
        }
    }

    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            throw new ApiException('cURL error: ' . $error);
        }

        $decoded = json_decode($response, true);

        if ($statusCode >= 400) {
            $message = $decoded['message'] ?? 'Unknown API error';
            throw new ApiException($message, $statusCode, $decoded);
        }

        return $decoded ?? [];
    }
}
```

**Jelaskan:**
- `__construct()`: terima token (opsional), set header
- `get()`: bikin URL, kirim cURL, decode JSON, handle error
- Kalau sukses → return array
- Kalau gagal → throw ApiException

---

## Step 4 — Test `Client.php` langsung

Buat file `test-client.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Kryve\GithubApi\Client;

$token = getenv('GITHUB_TOKEN') ?: null;
$client = new Client($token);

// Coba ambil user
$result = $client->get('/users/kryvestudio');
echo "Login: " . $result['login'] . "\n";
echo "Nama: " . ($result['name'] ?? '-') . "\n";
echo "Bio: " . ($result['bio'] ?? '-') . "\n";

// Coba error 404
try {
    $client->get('/users/nonexistent_xyz_999');
} catch (\Kryve\GithubApi\Exception\ApiException $e) {
    echo "Error: " . $e->getMessage() . " (HTTP {$e->getCode()})\n";
}
```

Jalankan:
```bash
php test-client.php
```

Hasil yang diharapkan:
```
Login: kryvestudio
Nama: Kryve Studio
Bio: Indie web dev studio...
Error: Not Found (HTTP 404)
```

---

## Step 5 — Buat `src/Api/User.php`

Bikin folder `src/Api/`, lalu file:

```php
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
```

---

## Step 6 — Buat `src/Api/Repos.php`

```php
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
```

---

## Step 7 — Buat `src/Api/Issues.php`

```php
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
```

---

## Step 8 — Test pake semua API class

Buat `test-all.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Kryve\GithubApi\Client;
use Kryve\GithubApi\Api\User;
use Kryve\GithubApi\Api\Repos;
use Kryve\GithubApi\Api\Issues;

$client = new Client(getenv('GITHUB_TOKEN') ?: null);

$user = new User($client);
$data = $user->get('kryvestudio');
echo "User: {$data['login']}\n";

$repos = new Repos($client);
$list = $repos->forOrg('kryvestudio');
echo "Total repo: " . count($list) . "\n";

$issues = new Issues($client);
try {
    $list = $issues->list('kryvestudio', 'github-api');
    echo "Total issues: " . count($list) . "\n";
} catch (Exception $e) {
    echo "Issues: " . $e->getMessage() . "\n";
}
```

---

## Step 9 — Buat PHPUnit test (opsional)

Buat `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php">
    <testsuites>
        <testsuite name="Kryve GitHub API Tests">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

Buat `tests/ClientTest.php`:

```php
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
```

Install PHPUnit:
```bash
composer require --dev phpunit/phpunit ^11.0
```

Jalankan:
```bash
vendor\bin\phpunit
```

---

## Step 10 — Push ke GitHub

```bash
git init
git add .
git commit -m "Initial commit: GitHub API wrapper"
git remote add origin https://github.com/kryvestudio/github-api.git
git push -u origin main
```

---

## Step 11 — Publish ke Packagist (opsional)

1. Login ke https://packagist.org
2. Klik "Submit Package"
3. Masukkan URL: `https://github.com/kryvestudio/github-api`
4. Klik "Submit"

Selesai! Semua orang bisa pake:
```bash
composer require kryvestudio/github-api
```

---

## Ringkasan Alur

```
composer.json  (definisi package)
      │
      ▼
  vendor/autoload.php  (PSR-4 autoloading)
      │
      ▼
  Client.php  (curl → GitHub API → array)
      │
      ├── Api/User.php     (/users/:username)
      ├── Api/Repos.php    (/orgs/:org/repos)
      └── Api/Issues.php   (/repos/:owner/:repo/issues)
      │
      ▼
  ApiException.php  (error handler)
```
