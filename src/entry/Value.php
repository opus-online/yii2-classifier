<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 18.12.13
 */

namespace opus\classifier\entry;

use opus\classifier\base\Entry;

/**
 * Class Value. Override this to have your own classifier value entry model (add custom methods etc).
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package classifier\entry
 */
class Value extends Entry
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $attributes;
    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $order_no;
} 
