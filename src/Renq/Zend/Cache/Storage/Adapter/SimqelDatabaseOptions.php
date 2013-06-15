<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Renq\Zend\Cache\Storage\Adapter;

use Traversable;
use Zend\Cache\Exception;
use Zend\Cache\Storage\Adapter\AdapterOptions;

/**
 * These are options specific to the Simqel adapter
 */
class SimqelDatabaseOptions extends AdapterOptions
{

    /**
     * Directory to store cache files
     *
     * @var null|string The cache directory
     *                  or NULL for the systems temporary directory
     */
    protected $dsn = null;

    /**
     * Constructor
     *
     * @param  array|Traversable|null $options
     * @return FilesystemOptions
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    /**
     * Set cache dir
     *
     * @param  string $cacheDir
     * @return FilesystemOptions
     * @throws Exception\InvalidArgumentException
     */
    public function setDsn($dsn)
    {
        $this->triggerOptionEvent('dsn', $dsn);
        $this->dsn = $dsn;
        return $this;
    }

    /**
     * Get cache dir
     *
     * @return null|string
     */
    public function getDsn()
    {
        return $this->dsn;
    }
}
