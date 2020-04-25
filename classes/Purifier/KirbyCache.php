<?php

namespace sgkirby\Commentions\Purifier;

use HTMLPurifier_DefinitionCache;

class KirbyCache extends HTMLPurifier_DefinitionCache
{
    /**
     * @var \Kirby\Cache\Cache
     */
    protected $cache;


    public function __construct($type)
    {
        parent::__construct($type);
        $this->cache = kirby()->cache('sgkirby.commentions.purifier-definitions');
    }

    public function add($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }

        $key = $this->generateKey($config);

        if ($this->cache->exists($key)) {
            return false;
        }

        return $this->cache->set($key, serialize($def), 0);
    }

    public function set($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }

        $key = $this->generateKey($config);

        return $this->cache->set($key, serialize($def), 0);
    }

    public function replace($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }

        $key = $this->generateKey($config);

        if (!$this->cache->exists($key)) {
            return false;
        }

        return $this->cache->set($key, serialize($def), 0);
    }

    public function get($config)
    {
        $value = $this->cache->get($this->generateKey($config), null);
        return $value ? unserialize($value): false;
    }

    public function remove($config)
    {
        return $this->cache->remove($this->generateKey($config));
    }

    public function flush($config)
    {
        return $this->cache->flush();
    }

    public function cleanup($config)
    {
        // Flush the whole cache, as Kirby’s cache API does not offer
        // a cleanup method
        return $this->cache->flush();
    }

    public static function triggerAutoload(): void
    {

    }
}
