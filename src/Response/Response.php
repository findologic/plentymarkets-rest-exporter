<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response;

use Psr\Http\Message\ResponseInterface;

abstract class Response
{
    /** @var ResponseInterface */
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Parses the given response. You may only access getters after calling this method.
     * Calling this method may write all response data, formatted into the ram. This may cause issues
     * when having really really huge responses, provided by Plentymarkets.
     */
    abstract public function parse(): void;

    protected function jsonSerialize(): array
    {
        return json_decode($this->response->getBody(), true);
    }
}
