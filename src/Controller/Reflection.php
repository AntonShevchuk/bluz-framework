<?php
/**
 * Bluz Framework Component
 *
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */
namespace Bluz\Controller;

use Bluz\Common\Exception\ComponentException;
use Bluz\Common\Options;
use Bluz\Proxy\Request;

/**
 * Reflection
 *
 * @package  Bluz\Controller
 * @author   Anton Shevchuk
 */
class Reflection
{
    use Options;

    /**
     * @var string full path to file
     */
    protected $file;

    /**
     * @var integer cache TTL
     */
    protected $cache = 0;

    /**
     * @var array list of Accept
     */
    protected $accept = array();
    
    /**
     * @var array list of Acl
     */
    protected $acl = array();

    /**
     * @var array list of HTTP methods
     */
    protected $method = array();

    /**
     * @var array described params
     */
    protected $params = array();

    /**
     * @var string privilege
     */
    protected $privilege;

    /**
     * @var array routers
     */
    protected $route = array();

    /**
     * @var array default values of params
     */
    protected $values = array();

    /**
     * Constructor of Reflection
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Set state required for working with var_export (used inside PHP File cache)
     *
     * @param  $array
     * @return Reflection
     */
    public static function __set_state($array)
    {
        $instance = new Reflection($array['file']);
        foreach ($array as $key => $value) {
            $instance->{$key} = $value;
        }
        return $instance;
    }

    /**
     * Process to get reflection from file
     *
     * @return void
     * @throws ComponentException
     */
    public function process()
    {
        /** @var \Closure|object $closure */
        $closure = include $this->file;

        if (!is_callable($closure)) {
            throw new ComponentException("There is no callable structure in file `{$this->file}`");
        }

        $reflection = new \ReflectionFunction($closure);

        // check and normalize params by doc comment
        $docComment = $reflection->getDocComment();

        // get all options by one regular expression
        if (preg_match_all('/\s*\*\s*\@([a-z0-9-_]+)\s+(.*).*\s+/i', $docComment, $matches)) {
            foreach ($matches[1] as $i => $key) {
                $this->setOption($key, $matches[2][$i]);
            }
        }

        // init routes
        $this->initRoute();

        // parameters available for Closure only
        if ($reflection instanceof \ReflectionFunction) {
            // get params and convert it to simple array
            $reflectionParams = $reflection->getParameters();
            // setup params and optional params
            foreach ($reflectionParams as $param) {
                $name = $param->getName();
                // if some function params is missed in description
                if (!isset($this->params[$name])) {
                    $this->params[$name] = null;
                }
                if ($param->isOptional()) {
                    $this->values[$name] = $param->getDefaultValue();
                }
            }
        }
    }

    /**
     * Process request params
     *
     *  - type conversion
     *  - set default value
     *
     * @param  array $requestParams
     * @return array
     */
    public function params($requestParams)
    {
        // apply type and default value for request params
        $params = array();
        foreach ($this->params as $param => $type) {
            if (isset($requestParams[$param])) {
                switch ($type) {
                    case 'bool':
                    case 'boolean':
                        $params[] = (bool)$requestParams[$param];
                        break;
                    case 'int':
                    case 'integer':
                        $params[] = (int)$requestParams[$param];
                        break;
                    case 'float':
                        $params[] = (float)$requestParams[$param];
                        break;
                    case 'string':
                        $params[] = (string)$requestParams[$param];
                        break;
                    case 'array':
                        $params[] = (array)$requestParams[$param];
                        break;
                    default:
                        $params[] = $requestParams[$param];
                        break;
                }
            } elseif (isset($this->values[$param])) {
                $params[] = $this->values[$param];
            } else {
                $params[] = null;
            }
        }
        return $params;
    }

    /**
     * Get path to file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get Cache TTL
     *
     * @return integer
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set Cache TTL
     *
     * @param  string $ttl
     * @return void
     */
    public function setCache($ttl)
    {
        $this->cache = $this->prepareCache($ttl);
    }

    /**
     * Prepare Cache
     *
     * @param  string $cache
     * @return integer
     */
    protected function prepareCache($cache)
    {
        $num = (int)$cache;
        $time = substr($cache, strpos($cache, ' '));
        switch ($time) {
            case 'day':
            case 'days':
                return (int)$num * 60 * 60 *24;
            case 'hour':
            case 'hours':
                return (int)$num * 60 * 60;
            case 'min':
            default:
                return (int)$num * 60;
        }
    }

    /**
     * Get accepted type
     *
     * @return array|null
     */
    public function getAccept()
    {
        return sizeof($this->accept)?$this->accept:null;
    }

    /**
     * Set accepted types
     *
     * @param  string $accept
     * @return void
     */
    public function setAccept($accept)
    {
        // allow accept map
        $acceptMap = [
            'ANY'  => Request::TYPE_ANY,
            'HTML' => Request::TYPE_HTML,
            'JSON' => Request::TYPE_JSON
        ];

        $accept = strtoupper($accept);

        if (isset($acceptMap[$accept])) {
            $this->accept[] = $acceptMap[$accept];
        }
    }
    
    /**
     * Get Acl privileges
     * 
     * @return array|null
     */
    public function getAcl()
    {
        return sizeof($this->acl)?$this->acl:null;
    }

    /**
     * Set Acl privileges
     *
     * @param  string $acl
     * @return void
     */
    public function setAcl($acl)
    {
        $this->acl[] = $acl;
    }

    /**
     * Get HTTP Method
     *
     * @return array|null
     */
    public function getMethod()
    {
        return sizeof($this->method)?$this->method:null;
    }

    /**
     * Set HTTP Method
     *
     * @param  string $method
     * @return void
     */
    public function setMethod($method)
    {
        $this->method[] = strtoupper($method);
    }

    /**
     * Get all params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set param types
     *
     * @param  string $param
     * @return void
     */
    public function setParam($param)
    {
        // prepare params data
        // setup param types
        if (strpos($param, '$') === false) {
            return;
        }

        list($type, $key) = preg_split('/[ $]+/', $param);

        $this->params[$key] = trim($type);
    }

    /**
     * Get Privilege fo ACL
     *
     * @return string
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * Set Privilege fo ACL allow only one privilege
     *
     * @param  string $privilege
     * @return void
     */
    public function setPrivilege($privilege)
    {
        $this->privilege = $privilege;
    }

    /**
     * Get Route
     *
     * @return array|null
     */
    public function getRoute()
    {
        return sizeof($this->route)?$this->route:null;
    }

    /**
     * Set Route
     *
     * @param  string $route
     * @return void
     */
    public function setRoute($route)
    {
        $this->route[$route] = null;
    }

    /**
     * Init Route
     *
     * @return void
     */
    protected function initRoute()
    {
        foreach ($this->route as $route => &$pattern) {
            $pattern = $this->prepareRoutePattern($route);
        }
    }

    /**
     * Prepare Route pattern
     *
     * @param  string $route
     * @return string
     */
    protected function prepareRoutePattern($route)
    {
        $pattern = str_replace('/', '\/', $route);

        foreach ($this->getParams() as $param => $type) {
            switch ($type) {
                case 'int':
                case 'integer':
                    $pattern = str_replace("{\$" . $param . "}", "(?P<$param>[0-9]+)", $pattern);
                    break;
                case 'float':
                    $pattern = str_replace("{\$" . $param . "}", "(?P<$param>[0-9.,]+)", $pattern);
                    break;
                case 'string':
                case 'module':
                case 'controller':
                    $pattern = str_replace(
                        "{\$" . $param . "}",
                        "(?P<$param>[a-zA-Z0-9-_.]+)",
                        $pattern
                    );
                    break;
            }
        }
        return '/^' . $pattern . '/i';
    }
}
