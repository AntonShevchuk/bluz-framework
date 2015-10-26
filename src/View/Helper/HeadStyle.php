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
namespace Bluz\View\Helper;

use Bluz\Application\Application;
use Bluz\Proxy\Layout;

return
    /**
     * Set or generate <style> code for <head>
     *
     * @param  string $style
     * @param  string $media
     * @return string|null
     */
    function ($style = null, $media = 'all') {
        /**
         * @var Layout $this
         */
        if (Application::getInstance()->hasLayout()) {
            return Layout::headStyle($style, $media);
        } else {
            // it's just alias to style() call
            return $this->style($style);
        }
    };
