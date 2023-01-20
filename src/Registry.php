<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter;

use Psr\Cache\InvalidArgumentException;
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

    private AbstractAdapter $cache;

    public function __construct(?AbstractAdapter $cache = null)
    {
        $this->cache = $cache ?? new FilesystemAdapter(
            '',
            self::CACHE_LIFETIME,
            Utils::env('CACHE_DIR', self::CACHE_DIR)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value): self
    {
        /** @var CacheItem $item */
        $item = $this->cache->getItem($key);
        $item->set(serialize($value));
        $this->cache->save($item);

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        /** @var CacheItem $item */
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return null;
        }

        return unserialize($item->get());
    }
}
