<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 18.12.13
 */

namespace opus\classifier\entry;

use opus\classifier\base\Entry;

/**
 * Class Classifier. Override this to have your own classifier entry model (add custom methods etc)
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package classifier\entry
 */
class Classifier extends Entry
{
    /**
     * @var string
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
    public $description;

}
