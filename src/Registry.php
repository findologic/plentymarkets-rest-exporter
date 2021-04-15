<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Entity;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

/**
 * The registry holds response data that is relevant for general purpose, like configuration, general store data, etc.
 */
class Registry
{
    public const CACHE_DIR = __DIR__ . '/../var/cache/';

    private const CACHE_LIFETIME = 60 * 60 * 24;

    /** @var AbstractAdapter */
    private $cache;

    public function __construct(?AbstractAdapter $cache = null)
    {
        $this->cache = $cache ?? new FilesystemAdapter(
            '',
            self::CACHE_LIFETIME,
            Utils::env('CACHE_DIR', self::CACHE_DIR)
        );
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, $value): self
    {
        /** @var CacheItem $item */
        $item = $this->cache->getItem($key);
        $item->set(serialize($value));
        $this->cache->save($item);

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        /** @var CacheItem $item */
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return null;
        }

        return unserialize($item->get());
    }
}
