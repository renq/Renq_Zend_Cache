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

    protected $dsn = null;

    protected $prefix = '';

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

    public function setDsn($dsn)
    {
        $this->triggerOptionEvent('dsn', $dsn);
        $this->dsn = $dsn;
        return $this;
    }

    public function getDsn()
    {
    	return $this->dsn;
    }

    public function setTablePrefix($prefix)
    {
    	$this->triggerOptionEvent('prefix', $prefix);
    	$this->prefix = $prefix;
    	return $this;
    }

    public function getTablePrefix()
    {
        return $this->prefix;
    }
}
