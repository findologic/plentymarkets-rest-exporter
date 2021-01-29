<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

/**
 * Lists variations with the specified data.
 *
 * @see https://developers.plentymarkets.com/rest-doc#/Pim/get_rest_pim_variations
 */
class PimVariationRequest extends Request implements IterableRequestInterface
{
    use IterableRequest;

    public function __construct()
    {
        parent::__construct('GET', 'pim/variations');
    }

    public function setWith(array $with): self
    {
        $this->params['with'] = $with;

        return $this;
    }

    public function setParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }
}
