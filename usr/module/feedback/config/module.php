<?php
/**
 * Ferrch (http://www.ferrch.com)
 *
 * @link            http://www.ferrch.com for the Ferrch Team.
 * @copyright       Copyright (c) Ferrch http://www.ferrch.com
 */

/**
 * Feedback module meta
 *
 * @author Lugal <luguoning@ferrch.com>
 */

return array(
    'meta'  => array(
        'title'         => _a('Feedback Demo'),
        'description'   => _a('A module for feedback'),
        'version'      => '1.0.0-beta.1',
        'license'      => 'New BSD',
        'logo'         => 'image/ferrch_logo.png',
        'clonable'      => false,
    ),
    'author'    => array(
        'name'      => 'Lugal',
        'email'      => 'luguoning@ferrch.com',
        'website'    => 'http://www.ferrch.com',
        'credits'     => 'Zend Framework Team; Pi Engine Team; FERRCH Team.'
    ),
    'maintenance'   => array(
        'resource' => array(
            'database'  => array(
                'sqlfile'   => 'sql/mysql.sql',
                'schema'    => array(
                    'user'          => 'table',
                ),
            ),
        	'config'        => 'config.php',
            //'navigation'    => 'navigation.php',
        ),
    ),
);