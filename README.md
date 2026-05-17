# Kryve Studio — GitHub API

PHP wrapper untuk GitHub REST API oleh Kryve Studio.

## Instalasi

```bash
composer require kryvestudio/github-api
```

## Persiapan Token

1. Buka https://github.com/settings/tokens
2. Klik **Generate new token** → pilih scopes sesuai kebutuhan (minimal `public_repo`)
3. Copy token, lalu set ke environment variable:

```bash
set GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
```

Atau bisa langsung passing ke constructor tanpa env:

```php
$client = new Client('ghp_xxxxxxxxxxxxxxxxxxxx');
```

## Authentikasi

| Cara | Constructor | Keterangan |
|------|-------------|------------|
| **Bearer token** (default) | `new Client($token)` | Pake token doang — **recommended** |
| **Basic Auth** | `new Client($token, $baseUrl, $username)` | Buat legacy / account-level auth |
| **No auth (public)** | `new Client()` | Hanya bisa akses endpoint publik |

Basic Auth:
```php
$client = new Client('ghp_xxx', 'https://api.github.com', 'kryvestudio');
```

## API Reference

### `Client`

Inti dari library. Handle HTTP request ke GitHub API via cURL.

Method:
```
__construct(?string $token, string $baseUrl, ?string $username)
get(string $endpoint, array $params) : array
```

Contoh:
```php
$client = new Client(getenv('GITHUB_TOKEN') ?: null);
$result = $client->get('/users/kryvestudio');
$repos = $client->get('/users/kryvestudio/repos');
```

### `User`

Bungkus endpoint `/users/:username`.

```php
$user = new User($client);
$data = $user->get('kryvestudio');
```

### `Repos`

Bungkus endpoint `/orgs/:org/repos` dan `/users/:username/repos`.

```php
$repos = new Repos($client);
$list = $repos->forUser('kryvestudio');
$list = $repos->forOrg('google', ['sort' => 'stars']);
```

### `Issues`

Bungkus endpoint `/repos/:owner/:repo/issues`.

```php
$issues = new Issues($client);
$list = $issues->list('laravel', 'laravel');
```

## Error Handling

Semua error (4xx, 5xx, cURL error) dilempar sebagai `ApiException`:

```php
use Kryve\GithubApi\Exception\ApiException;

try {
    $user->get('nonexistent_user');
} catch (ApiException $e) {
    echo $e->getMessage();        // "Not Found"
    echo $e->getCode();           // 404
    print_r($e->getResponseBody());// Array dari GitHub { message, documentation_url, ... }
}
```

## Contoh dengan HTML

### Card Profil User

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Kryve\GithubApi\Client;
use Kryve\GithubApi\Api\User;

$client = new Client(getenv('GITHUB_TOKEN') ?: null);
$user = new User($client);
$data = $user->get('kryvestudio');
?>
<!DOCTYPE html>
<html>
<body>
    <div style="border:1px solid #ddd;padding:20px;max-width:400px;border-radius:8px;">
        <img src="<?= $data['avatar_url'] ?>" width="80" style="border-radius:50%;">
        <h2><?= $data['name'] ?? $data['login'] ?></h2>
        <p><?= $data['bio'] ?? '-' ?></p>
        <p>
            📍 <?= $data['location'] ?? '-' ?> |
            🏢 <?= $data['company'] ?? '-' ?>
        </p>
        <p>
            Repos: <?= $data['public_repos'] ?> |
            Followers: <?= $data['followers'] ?> |
            Following: <?= $data['following'] ?>
        </p>
    </div>
</body>
</html>
```

### Daftar Repository

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Kryve\GithubApi\Client;
use Kryve\GithubApi\Api\Repos;

$client = new Client(getenv('GITHUB_TOKEN') ?: null);
$repos = new Repos($client);
$list = $repos->forUser('kryvestudio');
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Repositories</h2>
    <ul>
    <?php foreach ($list as $repo): ?>
        <li>
            <strong><?= $repo['name'] ?></strong>
            <?php if ($repo['description']): ?>
                <br><small><?= $repo['description'] ?></small>
            <?php endif; ?>
            <br>
            ⭐ <?= $repo['stargazers_count'] ?> |
            🍴 <?= $repo['forks_count'] ?> |
            🐛 <?= $repo['open_issues_count'] ?>
            <br>
            <a href="<?= $repo['html_url'] ?>">Lihat</a>
        </li>
        <hr>
    <?php endforeach; ?>
    </ul>
</body>
</html>
```

### Daftar Issues

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Kryve\GithubApi\Client;
use Kryve\GithubApi\Api\Issues;

$client = new Client(getenv('GITHUB_TOKEN') ?: null);
$issues = new Issues($client);
$list = $issues->list('laravel', 'laravel', ['per_page' => 5]);
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Open Issues (terbaru)</h2>
    <?php foreach ($list as $issue): ?>
        <div style="border:1px solid #eee;padding:10px;margin-bottom:10px;">
            <strong>
                <a href="<?= $issue['html_url'] ?>">
                    #<?= $issue['number'] ?> — <?= $issue['title'] ?>
                </a>
            </strong>
            <p>👤 <?= $issue['user']['login'] ?> |
               🏷️ <?= implode(', ', array_column($issue['labels'], 'name')) ?> |
               💬 <?= $issue['comments'] ?>
            </p>
            <p><?= date('d M Y', strtotime($issue['created_at'])) ?></p>
        </div>
    <?php endforeach; ?>
</body>
</html>
```

## Testing

```bash
composer test
```

## Struktur Folder

```
├── src/
│   ├── Client.php          # HTTP client (cURL wrapper)
│   ├── Api/
│   │   ├── User.php        # GET /users/:username
│   │   ├── Repos.php       # GET /orgs/:org/repos, /users/:username/repos
│   │   └── Issues.php      # GET /repos/:owner/:repo/issues
│   └── Exception/
│       └── ApiException.php
├── tests/
│   └── ClientTest.php
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

