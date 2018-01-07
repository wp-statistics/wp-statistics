<?php
/**
 * Copyright (c) 2015 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 */

namespace Wurfl;

/**
 * WURFL PHP API Constants
 */
class WurflConstants
{
    const GENERIC             = 'generic';
    const GENERIC_XHTML       = 'generic_xhtml';
    const GENERIC_WEB_BROWSER = 'generic_web_browser';
    const GENERIC_WEB_CRAWLER = 'generic_web_crawler';
    const GENERIC_MOBILE      = 'generic_mobile';

    const MEMCACHE     = 'memcache';
    const APC          = 'apc';
    const FILE         = 'file';
    const NULL_CACHE   = 'null';
    const EACCELERATOR = 'eaccelerator';
    const SQLITE       = 'sqlite';
    const MYSQL        = 'mysql';

    const NO_MATCH      = null;
    const RIS_DELIMITER = '---';

    const PREFERRED_MARKUP_HTML = 'html_web';

    const API_VERSION   = '1.7.1.1';
    const API_NAMESPACE = 'wurfl_1711';
}
