<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 18.12.13
 */

namespace opus\classifier\base;


use classifier\entry\Classifier as ClassifierEntry;
use classifier\entry\Value;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

/**
 * Class Classifier
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package classifier\base
 */
class Classifier extends Component
{
    /**
     * Holds classifier objects by ID
     *
     * @var array
     */
    protected $mapById;

    /**
     * Holds classifier objects and values
     *
     * @var array[]
     */
    protected $mapByCode;

    /**
     * Holds classifier value objects by ID
     *
     * @var array
     */
    protected $valueMapById = array();

    /**
     * Holds classifier value objects and values
     *
     * @var array
     */
    protected $valueMapByCode = array();

    /**
     * Holds localized classifier value objects and values
     *
     * @var array
     */
    protected $localizedValueMap;

    /**
     * Returns a classifier by its ID or CODE
     *
     * @param string|int $identifier Number (ID) or String (code)
     * @throws \yii\base\InvalidParamException
     * @return \classifier\entry\Classifier
     */
    public function get($identifier)
    {
        $classifier = null;
        if (is_numeric($identifier) && isset($this->mapById[$identifier]))
        {
            $classifier = $this->mapById[$identifier];
        }
        elseif (isset($this->mapByCode[$identifier]))
        {
            $classifier = $this->mapByCode[$identifier]['classifier'];
        }

        if (null === $classifier)
        {
            throw new InvalidParamException(sprintf('Accessed unknown classifier "%s", no matches found using CODE nor ID', $identifier));
        }

        return $this->normalizeClassifier($classifier);
    }

    /**
     * Returns a classifier value by its ID or CODES (parent and self)
     * Usage:
     * -   getValue(3)
     * -   getValue('PARENT_CODE', 'VALUE_CODE')
     *
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidCallException
     * @return \classifier\entry\Value
     */
    public function getValue()
    {
        $args = func_get_args();
        $value = null;
        if (count($args) === 1)
        {
            if (!isset($this->valueMapById[$args[0]])) {
                throw new InvalidParamException(sprintf('Accessed unknown classifier value ID "%s"', $args[0]));
            }
            $value = $this->valueMapById[$args[0]];
        }
        elseif (count($args) === 2)
        {
            if (!$this->get($args[0])) {
                throw new InvalidParamException(sprintf('Accessed unknown classifier with code "%s"', $args[0]));
            }
            if (!isset($this->mapByCode[$args[0]]['values'][$args[1]])) {
                throw new InvalidParamException(sprintf('Accessed unknown classifier value %s::%s', $args[0], $args[1]));
            }
            $value = $this->mapByCode[$args[0]]['values'][$args[1]];
        }
        else
        {
            throw new InvalidCallException('Wrong number of parameters');
        }

        if (null === $value)
        {
            throw new InvalidParamException('Could not find classifier value');
        }

        return $this->normalizeClassifierValue($value);
    }

    /**
     * Returns a list of classifier values by classifier ID or CODE
     *
     * @param mixed $identifier
     * @param bool $simpleList
     * @return array
     */
    public function getList($identifier, $simpleList = false)
    {
        $classifier = $this->get($identifier);

        $values = $this->mapByCode[$classifier->code]['values'];

        if (true === $simpleList)
        {
            $list = ArrayHelper::map($values, 'id', 'name');
        }
        else
        {
            $list = array_map([$this, 'normalizeClassifierValue'], $values);
        }

        return $list;
    }

    /**
     * @param array $classifier
     * @return \classifier\entry\Classifier
     */
    protected function normalizeClassifier(array $classifier)
    {
        return new ClassifierEntry($classifier);
    }

    /**
     * @param array $value
     * @return Value
     */
    protected function normalizeClassifierValue(array $value)
    {
        $valueI18n = $this->localizeClassifierValue($value);
        return new Value($valueI18n);
    }

    /**
     * @param array $value
     * @return array
     */
    protected function localizeClassifierValue(array $value)
    {
        return $value;
    }
} 
