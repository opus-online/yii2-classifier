<?php
/**
 * Classifier component for Yii2 projects.
 * Please see the project page for information: https://github.com/opus-online/yii2-classifier
 */

namespace opus\classifier;

use yii\base\Application;
use yii\base\InvalidConfigException;
use yii\BaseYii;
use yii\caching\DummyCache;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class Classifier
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\yii2\classifier
 */
class Classifier extends base\Classifier
{
    /**
     * @var string
     */
    public $classifierTable = 'ym_util_classifier';
    /**
     * @var string
     */
    public $classifierValueTable = 'ym_util_classifier_value';
    /**
     * @var string
     */
    public $classifierValueI18nTable = 'ym_util_classifier_value_i18n';

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
    public $cacheId = 'cache';
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
        $this->language = $this->app->language;

        \Yii::configure($this, $config);
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
        $cacheKey = sprintf('%s-%s', $this->getComponentId(), $this->language);

        if (($cache = $cacheHandler->get($cacheKey))) {
            BaseYii::configure($this, $cache);
        }

        parent::init();
    }

    protected function loadValues()
    {
        // load classifiers
        $classifiers = $this->loadFromTable($this->classifierTable);

        $this->mapById = ArrayHelper::index($classifiers, 'id');
        $this->mapByCode = ArrayHelper::index($classifiers, 'code');

        // load classifier values
        $classifierValues = $this->loadFromTable($this->classifierValueTable, ['order_no' => SORT_ASC]);
        foreach ($classifierValues as &$value) {
            $value['classifier_code'] = $this->mapById[$value['classifier_id']]['code'];
            $this->valueMapByClassifierId[$value['classifier_id']][$value['code']] = $value;
        }
        $this->valueMapById = ArrayHelper::index($classifierValues, 'id');

        // load localized values
        $this->localizedValueMap = [];

        foreach ($this->loadFromTable($this->classifierValueI18nTable) as $item) {
            $name = sprintf('%s_%s', $item['value_id'], $item['language_code']);
            $this->localizedValueMap[$name] = $item['value'];
        }

        if ($this->cachingDuration > 0) {
            $cacheValue = [
                'mapById' => $this->mapById,
                'mapByCode' => $this->mapByCode,
                'valueMapById' => $this->valueMapById,
                'valueMapByClassifierId' => $this->valueMapByClassifierId,
                'localizedValueMap' => $this->localizedValueMap
            ];

            $cacheHandler = $this->getCacheHandler();
            $cacheKey = sprintf('%s-%s', $this->getComponentId(), $this->language);

            $cacheHandler->set($cacheKey, $cacheValue, $this->cachingDuration);
        }
    }

    /**
     * Provides default localization functionality. This must be done runtime, because app language can change
     *
     * @param array $value
     * @return array
     */
    protected function localizeClassifierValue(array $value)
    {
        $name = sprintf('%s_%s', $value['id'], $this->language);

        if (isset($this->localizedValueMap) && isset($this->localizedValueMap[$name])) {
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
        $this->app->log->logger->log($message, $level, $this->getComponentId());
    }

    /**
     * Returns the cache handler for this component
     *
     * @return \yii\caching\Cache
     */
    protected function getCacheHandler()
    {
        if (($handler = \Yii::$app->get($this->cacheId))) {
            return $handler;
        }
        return new DummyCache();
    }

    /**
     * Loads and returns all elements from a model
     *
     * @param $tableName
     * @param array $order Optional order
     * @throws InvalidConfigException
     * @return array
     */
    private function loadFromTable($tableName, $order = [])
    {
        /** @var Query $query */
        $query = \Yii::createObject('yii\db\Query');
        return $query->from($tableName)->orderBy($order)->all();
    }
}
