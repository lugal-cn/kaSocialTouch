<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Cache\Storage\AdapterPluginManager;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\AbstractAdapter;

/**
 * Cache handler service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Cache extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'cache';

    /**
     * Namespace delimiter
     *
     * @var string
     */
    protected $nsDelimiter = '_';

    /**
     * Cache storage adapter
     *
     * @var AbstractAdapter
     */
    protected $storage;

    /**
     * Get cache storage, instantiate it if not exist yet
     *
     * @return AbstractAdapter
     */
    public function storage()
    {
        if (!$this->storage) {
            $this->storage = $this->loadStorage($this->options);
        }

        return $this->storage;
    }

    /**
     * Loads cache storage
     *
     * @param array|string $config
     *                  'adapter' - storage adapter; 'plugins'; 'options'
     * @return AbstractAdapter
     */
    public function loadStorage($config = array())
    {
        if (is_string($config)) {
            $configFile = sprintf('cache.%s.php', $config);
            $config = Pi::config()->load($configFile);
        }
        if (!isset($config['adapter']['options']['namespace'])) {
            $config['adapter']['options']['namespace'] = '';
        }
        $config['adapter']['options']['namespace'] = $this->getNamespace(
            $config['adapter']['options']['namespace']
        );
        StorageFactory::setAdapterPluginManager(new AdapterPluginManager);
        $storage = StorageFactory::factory($config);

        return $storage;
    }

    /**
     * Set namespace to current cache storage adapter
     *
     * @param string                                      $namespace
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storage
     * @params AbstractAdapter|null $storage
     *
     * @return $this
     */
    public function setNamespace(
        $namespace = '',
        AbstractAdapter $storage = null
    ) {
        $namespace = $this->getNamespace($namespace);
        $storage = $storage ?: $this->storage();
        $storage->getOptions()->setNamespace($namespace);

        return $this;
    }

    /**
     * Get canonized namespace by prepending Pi Engine identifier
     *
     * @param string $namespace
     * @return string
     */
    public function getNamespace($namespace = '')
    {
        return $namespace ? Pi::config('identifier')
            . $this->nsDelimiter . $namespace : Pi::config('identifier');
    }

    /**
     * Clear cache by namespace to current cache storage adapter
     *
     * @param string                                      $namespace
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storage
     * @params AbstractAdapter|null $storage
     *
     * @return $this
     */
    public function clearByNamespace(
        $namespace = '',
        AbstractAdapter $storage = null
    ) {
        $namespace = $this->getNamespace($namespace);
        $storage = $storage ?: $this->storage();
        if (method_exists($storage, 'clearByNamespace')) {
            $storage->clearByNamespace($namespace);
        }

        return $this;
    }

    /**
     * Canonize cache key
     *
     * @param string $key           Raw key
     * @param string $cacheLevel    Cache level
     * @return string
     */
    public function canonizeKey($key, $cacheLevel = '')
    {
        switch ($cacheLevel) {
            case 'user':
                $prefix = Pi::service('user')->getUser()->id ?: '';
                break;
            case 'role':
                $prefix = Pi::service('user')->getUser()->role ?: '';
                break;
            case 'locale':
                $prefix = Pi::service('i18n')->locale;
                break;
            default:
                $prefix = '';
                break;
        }
        if ($prefix) {
            $key = $key ? $prefix . $this->nsDelimiter . $key : $prefix;
        }
        $key = str_replace(array(':', '-', '.', '/'), '_', $key);

        return $key;
    }

    /**
     * Set item with namespace
     *
     * @param  string                                     $key
     * @param  mixed                                      $value
     * @param  string|array                               $options
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storage
     * @params AbstractAdapter|null $storage
     *
     * @return Cache
     */
    public function setItem(
        $key,
        $value,
        $options = array(),
        AbstractAdapter $storage = null
    ) {
        $storage = $storage ?: $this->storage();
        $storageOptions = $storage->getOptions();

        $namespace      = null;
        $ttl            = null;
        $namespaceOld   = null;
        $ttlOld         = null;
        if (is_string($options)) {
            $namespace = $options;
            $options = array();
        } else {
            if (isset($options['ttl'])) {
                $ttl = $options['ttl'];
            }
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            }
        }
        if (null !== $namespace) {
            $namespaceOld = $storageOptions->namespace;
            $storageOptions->namespace = $this->getNamespace($namespace);
        }
        if (null !== $ttl) {
            $ttlOld = $storageOptions->ttl;
            $storageOptions->ttl = $ttl;
        }

        $storage->setItem($key, $value);
        if (null !== $namespaceOld) {
            $storageOptions->namespace = $namespaceOld;
        }
        if (null !== $ttlOld) {
            $storageOptions->ttl = $ttlOld;
        }

        return $this;
    }

    /**
     * Get item with namespace
     *
     * @param  string                                     $key
     * @param  string|array                               $options
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storage
     * @params AbstractAdapter|null $storage
     *
     * @return mixed
     */
    public function getItem(
        $key,
        $options = array(),
        AbstractAdapter $storage = null
    ) {
        $storage = $storage ?: $this->storage();
        $storageOptions = $storage->getOptions();

        $namespace      = null;
        $ttl            = null;
        $namespaceOld   = null;
        $ttlOld         = null;
        if (is_string($options)) {
            $namespace = $options;
        } else {
            if (isset($options['ttl'])) {
                $ttl = $options['ttl'];
            }
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            }
        }
        if (null !== $namespace) {
            $namespaceOld = $storageOptions->namespace;
            $storageOptions->namespace = $this->getNamespace($namespace);
        }
        if (null !== $ttl) {
            $ttlOld = $storageOptions->ttl;
            $storageOptions->ttl = $ttl;
        }

        $data = $storage->getItem($key);
        if (null !== $namespaceOld) {
            $storageOptions->namespace = $namespaceOld;
        }
        if (null !== $ttlOld) {
            $storageOptions->ttl = $ttlOld;
        }

        return $data;
    }

    /**
     * Remove item with namespace
     *
     * @param  string                                     $key
     * @param  string|array                               $options
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storage
     * @params AbstractAdapter|null $storage
     *
     * @return $this
     */
    public function removeItem(
        $key,
        $options = array(),
        AbstractAdapter $storage = null
    ) {
        $storage = $storage ?: $this->storage();
        $storageOptions = $storage->getOptions();

        $namespace      = null;
        $namespaceOld   = null;
        if (is_array($options)) {
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            }
        } else {
            $namespace = $options;
        }
        if ($namespace) {
            $namespaceOld = $storageOptions->namespace;
            $storageOptions->namespace = $this->getNamespace($namespace);
        }
        $data = $storage->removeItem($key);
        if (null !== $namespaceOld) {
            $storageOptions->namespace = $namespaceOld;
        }

        return $data;
    }

    /**
     * Flush all cached contents
     *
     * Be careful
     *
     * @param string $type
     * @param string $item
     *
     * @return void
     */
    public function flush($type = '', $item = '')
    {
        $type = $type ?: 'all';

        if ('apc' == $type || 'all' == $type) {
            if (function_exists('apc_clear_cache')) {
                apc_clear_cache();
            }
        }
        if ('comment' == $type || 'all' == $type) {
            $this->clearByNamespace('comment');
        }
        if ('file' == $type || 'all' == $type) {
            $path = Pi::path('cache');
            Pi::service('file')->flush($path);
            Pi::service('file')->touch($path . '/index.html');
            /*
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $object) {
                $filename = $object->getFilename();
                if ($object->isFile() && 'index.html' !== $filename) {
                    unlink($object->getPathname());
                } elseif ($object->isDir() && '.' != $filename[0]) {
                    rmdir($object->getPathname());
                }
            }
            */
        }
        if ('application' == $type || 'all' == $type) {
            $this->clearByNamespace();
        }
        if ('module' == $type || 'all' == $type) {
            $modules = Pi::service('module')->meta();
            foreach (array_keys($modules) as $module) {
                $this->clearByNamespace($module);
            }
        }
        if ('page' == $type || 'all' == $type) {
            Pi::service('render_cache')->flushCache($item ?: null);
        }
        if ('persis' == $type || 'all' == $type) {
            Pi::persist()->flush();
        }
        if ('registry' == $type || 'all' == $type) {
            if (!empty($item)) {
                Pi::registry($item)->flush();
            } else {
                Pi::service('registry')->flush();
            }
        }
        if ('stat' == $type || 'all' == $type) {
            clearstatcache(true);
        }
    }
}
