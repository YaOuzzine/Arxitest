<?php

namespace App\Services;

use Exception;
use Throwable;

class ApiException extends Exception
{
    protected int $statusCode;
    protected ?array $response;

    /**
     * @param string          $message
     * @param int             $statusCode
     * @param Throwable|null  $previous
     * @param array|null      $response
     */
    public function __construct(string $message = "", int $statusCode = 0, ?Throwable $previous = null, ?array $response = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->response   = $response;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }
}
