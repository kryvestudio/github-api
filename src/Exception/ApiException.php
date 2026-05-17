<?php

namespace Kryve\GithubApi\Exception;

class ApiException extends \Exception
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