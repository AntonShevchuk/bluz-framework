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
namespace Bluz\Http;

use Bluz\Request\RequestException;

/**
 * HttpFileUpload
 *
 * @package  Bluz\Http
 * @author   Anton Shevchuk
 */
class FileUpload
{
    /*
        name=data
        data
        name => value
        type => value

        name=data[]
        data
        name => 0 => value
                1 => value
        type => 0 => value
                1 => value

        name=data[a]
        data
        name => a => value
        type => a => value

        name=data[a][b]
        data
        name => a => b => value
        type => a => b => value

        name=data[a][b][]
        data
        name => a => b => 0 => value
                          1 => value
        type => a => b => 0 => value
                          1 => value
    */

    /**
     * @var array list of Files
     */
    protected $files = array();

    /**
     * Constructor
     *
     * @param  array $array The array of $_FILES
     * @throws RequestException
     */
    public function __construct($array = null)
    {
        // check max file size error
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) &&
            empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0 ) {
            $displayMaxSize = ini_get('post_max_size');

            $error = 'Posted file is too large. '. $_SERVER["CONTENT_LENGTH"].
                ' bytes exceeds the maximum size of '. $displayMaxSize;
            // mute error message by user notice
            @trigger_error($error, E_USER_NOTICE);
            throw new RequestException($error);
        }

        $rawFiles = $array ? : $_FILES;
        foreach ($rawFiles as $key => $file) {
            $this->processFileArray($key, $file);
        }
    }

    /**
     * Process file information from array
     *
     * @param  string $key
     * @param  array  $fileInfo
     * @return array
     */
    protected function processFileArray($key, $fileInfo)
    {
        if (is_array($fileInfo['name'])) {
            foreach ($fileInfo['name'] as $subKey => $name) {
                if (is_numeric($subKey)) {
                    $this->createFile($fileInfo, $key);
                    break;
                } elseif (is_array($fileInfo['name'][$subKey])) {
                    foreach ($fileInfo['name'][$subKey] as $subSubKey => $subName) {
                        $this->createFile($fileInfo, $key, $subKey, $subSubKey);
                    }
                } else {
                    $this->createFile($fileInfo, $key, $subKey);
                }
            }
        } else {
            $this->createFile($fileInfo, $key);
        }
    }

    /**
     * Create File
     *
     * @param array    $fileInfo
     * @param string   $fileKey
     * @param string[] $keys
     * @return File instance
     */
    public function createFile($fileInfo, $fileKey, ...$keys)
    {
        $result = array();
        foreach ($fileInfo as $key => $value) {
            $parsedArr = $this->parseArray($value, $keys);
            if (!is_array($parsedArr)) {
                $result[0][$key] = $parsedArr;
            } else {
                foreach ($parsedArr as $k => $v) {
                    $result[$k][$key] = $v;
                }
            }
        }

        if (count($keys)) {
            $fileKey .= '[' . implode("][", $keys) . ']';
        }

        foreach ($result as $file) {
            $this->files[$fileKey][] = new File($file);
        }
    }

    /**
     * Parse array
     *
     * @param  array|string $paramArr
     * @param  string[]     $argList
     * @return string|array
     */
    public function parseArray($paramArr, $argList)
    {
        if (!is_array($paramArr)) {
            return $paramArr;
        }

        $subKey = current($argList);
        if ($subKey === false) {
            return $paramArr;
        }

        array_shift($argList);
        return $this->parseArray($paramArr[$subKey], $argList);
    }

    /**
     * Get file
     *
     * @param  string $name
     * @return File
     */
    public function getFile($name)
    {
        if (isset($this->files[$name])) {
            return current($this->files[$name]);
        } else {
            return null;
        }
    }

    /**
     * Get files
     *
     * @param  string $name
     * @return array
     */
    public function getFiles($name)
    {
        if (isset($this->files[$name])) {
            return $this->files[$name];
        } else {
            return array();
        }
    }
}
