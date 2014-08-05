<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

$config = array(
    'list_limit' => array(
        'title'         => _t('List page comment limit'),
        'description'   => _t('Number of comments on list page.'),
        'value'         => 20,
        'filter'        => 'int',
    ),

    'display_operation' => array(
        'title'         => _t('Display operation'),
        'description'   => _t('Level of operations on post list pages.'),
        'value'         => 'author',
    ),

);

return $config;