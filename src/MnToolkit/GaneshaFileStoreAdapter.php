<?php

namespace MnToolkit;

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Configuration;
use Ackintosh\Ganesha\Storage\Adapter\TumblingTimeWindowInterface;
use Ackintosh\Ganesha\Storage\AdapterInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Helper\Psr16Adapter;

class GaneshaFileStoreAdapter implements AdapterInterface, TumblingTimeWindowInterface
{
    private $cache = null;

    public function __construct()
    {
        $this->cache = MnToolkitBase::checkCache();
    }

    /**
     * @inheritDoc
     */
    public function supportCountStrategy()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportRateStrategy()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(Configuration $configuration)
    {
        // we don't need the configuration
    }

    /**
     * @inheritDoc
     */
    public function load($service)
    {
        $key = md5($service);
        return (int)$this->cache->get($key, false);
    }

    /**
     * @inheritDoc
     */
    public function save($service, $count)
    {
        $key = md5($service);
        $this->cache->set($key, $count);
    }

    /**
     * @inheritDoc
     */
    public function increment($service)
    {
        $key = md5($service);
        if ($this->cache->has($key)) {
            $value = (int)$this->cache->get($key, false);
            $value++;
        } else {
            $value = 1;
        }
        $this->cache->set($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function decrement($service)
    {
        $key = md5($service);
        if ($this->cache->has($key)) {
            $value = (int)$this->cache->get($key, false);
            $value = max($value - 1, 0);
        } else {
            $value = 0;
        }
        $this->cache->set($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function saveLastFailureTime($service, $lastFailureTime)
    {
        $key = md5($service);
        $this->cache->set($key, $lastFailureTime);
    }

    /**
     * @inheritDoc
     */
    public function loadLastFailureTime($service)
    {
        $key = md5($service);
        return $this->cache->get($key, false);
    }

    /**
     * @inheritDoc
     */
    public function saveStatus($service, $status)
    {
        $key = md5($service);
        $this->cache->set($key, $status);
    }

    /**
     * @inheritDoc
     */
    public function loadStatus($service)
    {
        $key = md5($service);
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        } else {
            $this->cache->set($key, Ganesha::STATUS_CALMED_DOWN);
            return Ganesha::STATUS_CALMED_DOWN;
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->cache->clear();
    }
}
