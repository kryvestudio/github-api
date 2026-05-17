# Kryve Studio — GitHub API

PHP wrapper untuk GitHub REST API. Simple, dependency-free, built dengan cURL.

📖 **[Full Documentation](https://kryvestudio.github.io/github-api/)** — Lengkap dengan authentication methods, API reference, error handling, dan testing guide.

---

## Quick Start

### Instalasi

```bash
composer require kryvestudio/github-api
```

**Requirements:** PHP 8.3+, ext-curl, ext-json

### Basic Usage

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Kryve\GithubApi\Client;
use Kryve\GithubApi\Api\User;
use Kryve\GithubApi\Api\Repos;

// Setup client (dengan token atau tanpa token untuk public endpoints)
$client = new Client(getenv('GITHUB_TOKEN') ?: null);

// Fetch user profile
$user = new User($client);
$profile = $user->get('kryvestudio');
echo "Bio: {$profile['bio']}\n";

// List repositories
$repos = new Repos($client);
$list = $repos->forUser('kryvestudio', ['per_page' => 5]);
foreach ($list as $repo) {
    echo "- {$repo['name']} ⭐ {$repo['stargazers_count']}\n";
}
```

### Token Setup

1. Generate token di [github.com/settings/tokens](https://github.com/settings/tokens)
2. Set environment variable:
   ```bash
   export GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
   ```

Atau pass langsung ke constructor:
```php
$client = new Client('ghp_xxxxxxxxxxxxxxxxxxxx');
```

---

## API yang Tersedia

| Class | Method | Endpoint |
|-------|--------|----------|
| **Client** | `get($endpoint, $params)` | Generic GET request |
| **User** | `get($username)` | `GET /users/:username` |
| **Repos** | `forUser($username, $params)` | `GET /users/:username/repos` |
| | `forOrg($org, $params)` | `GET /orgs/:org/repos` |
| **Issues** | `list($owner, $repo, $params)` | `GET /repos/:owner/:repo/issues` |

Query parameters opsional—cek [docs](https://kryvestudio.github.io/github-api/#repos) untuk detail.

---

## Error Handling

Semua error (4xx, 5xx, cURL errors) dilempar sebagai `ApiException`:

```php
use Kryve\GithubApi\Exception\ApiException;

try {
    $user->get('nonexistent_user');
} catch (ApiException $e) {
    echo $e->getMessage();        // "Not Found"
    echo $e->getCode();           // 404
    print_r($e->getResponseBody()); // Full GitHub error response
}
```

---

## Use Cases

**Portfolio Integration**
```php
// Auto-update stats di landing page
$profile = $user->get('your-username');
echo "Total repos: {$profile['public_repos']}";
echo "Followers: {$profile['followers']}";
```

**Repo Showcase**
```php
// List project highlights dengan metadata real-time
$repos = $repos->forUser('your-username', [
    'sort' => 'updated',
    'per_page' => 10
]);
// Display dengan stars, forks, language
```

**Issue Tracker Dashboard**
```php
// Monitor open issues across projects
$issues = new Issues($client);
$openIssues = $issues->list('owner', 'repo', ['state' => 'open']);
```

---

## Documentation

📚 **[Baca dokumentasi lengkap](https://kryvestudio.github.io/github-api/)** untuk:

- **Authentication methods** (Bearer Token, Basic Auth, No Auth)
- **Complete API reference** dengan semua parameters
- **Advanced error handling** patterns
- **Testing guide** dengan PHPUnit
- **Real-world HTML examples** (profile card, repo list, issue tracker)

---

## Testing

```bash
composer test
```

Atau langsung:
```bash
vendor/bin/phpunit
```

---

## Project Structure

```
src/
├── Client.php           # HTTP client core (cURL wrapper)
├── Api/
│   ├── User.php        # User endpoints
│   ├── Repos.php       # Repository endpoints
│   └── Issues.php      # Issues endpoints
└── Exception/
    └── ApiException.php
```

---

## License

MIT License - Kryve Studio

**Made with** ☕ **by** [Kryve Studio](https://github.com/kryvestudio)

---

## Links

- 📖 [Full Documentation](https://kryvestudio.github.io/github-api/)
- 📦 [Packagist](https://packagist.org/packages/kryvestudio/github-api)
- 🐙 [GitHub Repository](https://github.com/kryvestudio/github-api)
- 🔑 [Generate GitHub Token](https://github.com/settings/tokens)
