<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Model;

use Pi\Application\Model\Model;

/**
 * Comment type model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Type extends Model
{
    /**
     * Columns to be encoded
     *
     * @var array
     */
    protected $encodeColumns = array(
        'params'  => true,
    );
}
