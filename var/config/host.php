<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine host specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

/**
 * Host definition file
 *
 * Paths/URLs to system folders
 *
 * - URIs without a leading slash are considered relative
 *      to the current Pi Engine host location
 * - URIs with a leading slash are considered semi-relative
 *      requires proper rewriting rules in server conf
 */
return array(
    // URIs to resources
    // If URI is a relative one then www root URI will be prepended
    'uri'       => array(
        // WWW root URI
        'www'       => 'http://pi.local',
        // URI to access uploads directory
        'upload'    => 'http://pi.local/upload',
        // URI to access static files directory
        'static'    => 'http://pi.local/static',
    ),

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path'      => array(
        // Sharable paths
        // WWW root path, dependent sub folders: `script`, `public`
        'www'       => '/opt/local/share/nginx/html/pi/www',
        // Library directory
        'lib'       => '/opt/local/share/nginx/html/pi/lib',
        // User extension directory
        'usr'       => '/opt/local/share/nginx/html/pi/usr',
        // Application module directory
        'module'    => '/opt/local/share/nginx/html/pi/usr/module',
        // Theme directory
        'theme'     => '/opt/local/share/nginx/html/pi/usr/theme',
        // Path to static files directory
        'static'    => '/opt/local/share/nginx/html/pi/www/static',
        // Path to vendor library directory: default as `lib/vendor`
        'vendor'    => '/opt/local/share/nginx/html/pi/lib/vendor',
        // Path to module custom directory: default as `usr/custom`
        'custom'    => '/opt/local/share/nginx/html/pi/usr/custom',

        // Application specific paths
        // Path to uploads directory
        'upload'    => '/opt/local/share/nginx/html/pi/www/upload',
        // User data directory
        'var'       => '/opt/local/share/nginx/html/pi/var',

        // Sub-paths of var
        // Path to global collective configuration directory
        'config'    => '/opt/local/share/nginx/html/pi/var/config',
        // Path to cache files directory
        'cache'     => '/opt/local/share/nginx/html/pi/var/cache',
        // Path to logs directory
        'log'       => '/opt/local/share/nginx/html/pi/var/log',
    ),

    // Paths dependent on upper paths
    'directory' => array(
        'asset'     => array(
            'parent'    => 'www',
            'folder'    => 'asset',
        ),
    ),
);
