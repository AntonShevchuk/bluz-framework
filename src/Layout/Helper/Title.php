<?php
/**
 * Bluz Framework Component
 *
 * @copyright Bluz PHP Team
 * @link      https://github.com/bluzphp/framework
 */

declare(strict_types=1);

namespace Bluz\Layout\Helper;

use Bluz\Layout\Layout;
use Bluz\Proxy\Registry;

/**
 * Set or generate <title> code for <head>
 *
 * @param  string $title
 *
 * @return string
 */
return
    function ($title = null) {
        // it's stack for <title> tag
        if (is_null($title)) {
            return Registry::get('layout:title');
        }

        Registry::set('layout:title', $title);
        return $title;
    };
