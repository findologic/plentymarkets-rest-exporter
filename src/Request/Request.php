<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

abstract class Request
{
    /** @var string */
    protected $method;

    /** @var string */
    protected $endpoint;

    /** @var array */
    protected $headers = [];

    /** @var string|null */
    protected $body = null;

    /** @var string */
    protected $responseClass;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getResponseClass(): string
    {
        return $this->responseClass;
    }
}
