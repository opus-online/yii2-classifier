<?php
/**
 * Classifier component for Yii2 projects.
 * Please see the project page for information: https://github.com/opus-online/yii2-classifier
 */

namespace opus\classifier;

use yii\base\Application;
use yii\base\InvalidConfigException;
use yii\caching\DummyCache;
use yii\db\ActiveRecord;


/**
 * Class Classifier
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\yii2\classifier
 */
class Classifier extends base\Classifier
{
    /**
     * @var array Use this to override default classifier model names
     */
    public $classMap = [
        'Classifier' => 'Classifier',
        'ClassifierValue' => 'ClassifierValue',
        'ClassifierValueI18n' => 'ClassifierValueI18n',
    ];
    /**
     * @var array Use this to override field names if you don't like the default ones
     */
    public $attributeMap = [
        // Classifier and ClassifierValue
        'id' => 'id',
        'code' => 'code',
        // ClassifierValue
        'classifier_id' => 'classifier_id',
        // ClassifierValueI18n
        'classifier_code' => 'classifier_code',
    ];
    /**
     * Set this to a prefix/namespace if you are otherwise ok with the default model class names. This will be prepended
     * to all classes in $classMap
     *
     * @var string
     */
    public $modelPrefix;
    /**
     * @var bool Whether to use internationalization features
     */
    public $useI18n = true;
    /**
     * Sets the cache duration. 0 Means caching is disabled
     *
     * @var integer
     */
    public $cachingDuration = 0;
    /**
     * Sets the cache duration
     *
     * @var integer
     */
    public $cacheID = 'cache';
    /**
     * Used for logging and caching
     *
     * @var string
     */
    public $myNamespace = 'vendors.opus.classifier';
    /**
     * Language used for retrieving localized values
     *
     * @var string
     */
    public $language;
    /**
     * @var Application
     */
    public $app;

    /**
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     * @internal param array $params
     */
    public function __construct($config)
    {
        $this->app = \Yii::$app;

        $this->language = \Yii::$app->language;
        \Yii::configure($this, $config);

        if (null !== $this->modelPrefix) {
            if (isset($config['classMap'])) {
                throw new InvalidConfigException('Sorry, you cannot use \'modelPrefix\' and \'classMap\' together');
            }
            foreach ($this->classMap as $class => $mapTo) {
                $this->classMap[$class] = $this->modelPrefix . $mapTo;
            }
        }

        $this->init();
    }

    /**
     * Initializes the component
     *
     * @return void
     */
    public function init()
    {
        $cacheHandler = $this->getCacheHandler();
        $cacheKey = sprintf('%s-%s', $this->myNamespace, \Yii::$app->language);

        if (($cache = $cacheHandler->get($cacheKey))) {
            list($this->mapById, $this->mapByCode, $this->valueMapById, $this->valueMapByCode, $this->localizedValueMap) = $cache;
        } else {
            $this->validateModel('Classifier');
            $this->validateModel('ClassifierValue');

            $this->useI18n && $this->validateModel('ClassifierValueI18n', false);

            $this->mapById = $this->valueMapById = $this->valueMapByCode = [];

            foreach ($this->loadModelValues($this->classMap['Classifier']) as $classifierModel) {
                $classifierAttrs = $classifierModel->getAttributes();

                $this->mapById[$classifierModel->id] = $classifierAttrs;
                $this->mapByCode[$classifierModel->code] = array(
                    'classifier' => $classifierAttrs,
                    'values' => [],
                );
            }

            $this->useI18n && $this->loadLocalizedValues();

            /** @var $valueModel \yii\db\ActiveRecord */
            foreach ($this->loadModelValues($this->classMap['ClassifierValue']) as $valueModel) {
                $classifierId = $valueModel->getAttribute($this->attributeMap['classifier_id']);
                $classifierCode = $this->mapById[$classifierId][$this->attributeMap['code']];

                $valueAttrs = $valueModel->getAttributes();
                $valueAttrs['classifier_code'] = $classifierCode;

                $valueId = $valueModel->getAttribute($this->attributeMap['id']);

                $this->valueMapById[$valueId] = $valueAttrs;

                $combinedCode = $this->mapById[$classifierId]['code'] . '_' . $valueModel->code;
                $this->valueMapByCode[$combinedCode] = $valueAttrs;

                $this->mapByCode[$classifierCode]['values'][$valueModel->code] = $valueAttrs;
            }

            if ($this->cachingDuration > 0) {
                $cacheValue = array(
                    $this->mapById,
                    $this->mapByCode,
                    $this->valueMapById,
                    $this->valueMapByCode,
                    $this->localizedValueMap
                );
                $cacheHandler->set($cacheKey, $cacheValue, $this->cachingDuration);
            }
        }

        parent::init();
    }

    /**
     * Provides default localization functionality. This must be done runtime, because app language can change
     *
     * @param array $value
     * @return array
     */
    protected function localizeClassifierValue(array $value)
    {
        $keyValues = [
            $value[$this->attributeMap['classifier_code']],
            $value[$this->attributeMap['code']],
            $this->language
        ];
        $name = implode('_', $keyValues);

        if (is_array($this->localizedValueMap) && isset($this->localizedValueMap[$name])) {
            $value['name'] = $this->localizedValueMap[$name];
        }
        return $value;
    }

    /**
     * Logs a message under the component's namespace
     *
     * @param string $message
     * @param string $level CLogger::LEVEL_* constant
     */
    protected function log($message, $level)
    {
        $this->app->log->log($message, $level, $this->myNamespace);
    }

    /**
     * Override this to implement another type of localization model
     */
    protected function loadLocalizedValues()
    {
        $this->localizedValueMap = [];

        foreach ($this->loadModelValues($this->classMap['ClassifierValueI18n']) as $item) {
            $name = strtoupper(
                sprintf('%s_%s_%s', $item->classifier_code, $item->classifier_value_code, $item->language_code)
            );
            $this->localizedValueMap[$name] = $item->value;
        }
    }

    /**
     * Returns the cache handler for this component
     *
     * @return \yii\caching\Cache
     */
    protected function getCacheHandler()
    {
        if (($handler = \Yii::$app->{$this->cacheID})) {
            return $handler;
        }
        return new DummyCache();
    }

    /**
     * Loads and returns all elements from a model
     *
     * @param $modelClassName
     * @return ActiveRecord[]
     */
    protected function loadModelValues($modelClassName)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $modelClassName::find()->all();
    }

    /**
     * Validates model class name
     *
     * @param string $model
     * @param bool $checkFields
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateModel($model, $checkFields = true)
    {
        if (!isset($model)) {
            throw new InvalidConfigException('Model class name not set in configuration');
        }

        if (!isset($this->classMap[$model])) {
            throw new InvalidConfigException(sprintf('Model "%s" not defined in classMap', $model));
        }

        $className = $this->classMap[$model];

        if (!class_exists($className)) {
            throw new InvalidConfigException(sprintf('Model class "%s" not found', $className));
        }

        $parentModel = '\yii\db\ActiveRecord';
        if (!is_subclass_of($className, $parentModel)) {
            throw new InvalidConfigException("{$className} has to be an instance of {$parentModel}");
        }

        /** @var $modelObject \yii\db\ActiveRecord */

        if ($checkFields === true) {
            $modelObject = new $className;

            if (!$modelObject->hasAttribute($this->attributeMap['id'])) {
                throw new InvalidConfigException("$model does not have an attribute called '{$this->attributeMap['id']}'");
            }

            if (!$modelObject->hasAttribute($this->attributeMap['code'])) {
                throw new InvalidConfigException("$model does not have an attribute called '{$this->attributeMap['code']}'");
            }
        }
    }
}
