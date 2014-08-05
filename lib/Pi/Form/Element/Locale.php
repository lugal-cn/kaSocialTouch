<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Locale select element
 *
 * Supports auto-detection of locale
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Locale extends Select
{
    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions['auto'] = __('Auto-detection');
            $iterator = new \DirectoryIterator(
                Pi::service('i18n')->getPath('', '')
            );
            Pi::service('i18n')->load('language');
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                $directory = $fileinfo->getFilename();
                if (!preg_match('/^[a-z]{2}([\-\_][a-z]+)?$/', $directory)) {
                    continue;
                }
                $label = __($directory) . ' (' . $directory . ')';
                /*
                if (class_exists('\\Locale')) {
                    $label = \Locale::getDisplayName(
                        $directory,
                        Pi::service('i18n')->locale
                    );
                }
                */
                $this->valueOptions[$directory] = $label;
            }
        }

        return $this->valueOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = __('Language');
        }

        return parent::getLabel();
    }
}
