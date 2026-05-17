<?php

namespace Kryve\GithubApi;

use Kryve\GithubApi\Exception\ApiException;

class Client
{
    private string $baseUrl;
    private string $token;
    private array $headers;
    
    public function __construct(?string $token = null, string $baseUrl = 'https://api.github.com', ?string $username = null)
    {
        $this->token = $token ?? '';
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = [
            'Accept: application/vnd.github.v3+json',
            'User-Agent: kryvestudio-github-api',
        ];

        if ($this->token !== '' && $username !== null) {
            $this->headers[] = 'Authorization: Basic ' . base64_encode($username . ':' . $this->token);
        } elseif ($this->token !== '') {
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

        if($error) {
            throw new ApiException('cURL error: ' . $error);
        }

        $decoded = json_decode($response, true);

        if($statusCode >= 400) {
            $message = $decoded['message'] ?? 'Unknown API error';
            throw new ApiException($message, $statusCode, $decoded);
        }

        return $decoded ?? []; 
    }
}