<?php

use opus\classifier\Classifier;
use yii\base\InvalidConfigException;
use yii\db\Schema;
/**
 * @inheritdoc
 * @SuppressWarnings(ShortMethodName)
 * @SuppressWarnings(CamelCaseClassName)
 */
class m140819_081955_base extends \yii\db\Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return Classifier
     */
    protected function getClassifier()
    {
        $classifier = Yii::$app->get('classifier');
        if (!$classifier instanceof \opus\classifier\Classifier) {
            throw new InvalidConfigException('You should configure "classifier" component to use database before executing this migration.');
        }
        return $classifier;
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $classifier = $this->getClassifier();

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($classifier->classifierTable, [
            'id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL AUTO_INCREMENT',
            'code' => Schema::TYPE_STRING . '(128) NOT NULL',
            'name' => Schema::TYPE_STRING . '(128)',
            'is_system_classifier' => Schema::TYPE_INTEGER,
            'description' => Schema::TYPE_STRING,
            'UNIQUE KEY `code` (`code`)',
            'PRIMARY KEY (id)',
        ], $tableOptions);

        $this->createTable($classifier->classifierValueTable, [
            'id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL AUTO_INCREMENT',
            'classifier_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'code' => Schema::TYPE_STRING . '(255) NOT NULL',
            'name' => Schema::TYPE_STRING . '(255)',
            'attributes' => Schema::TYPE_STRING . '(255)',
            'order_no' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (id)',
            'INDEX `classifier_id` (`classifier_id`)',
            'UNIQUE KEY `classifier_id_value_code` (`classifier_id`, `code`)',
            "CONSTRAINT `FK_classifier_value_classifier` FOREIGN KEY "
                . "(`classifier_id`) REFERENCES `{$classifier->classifierTable}` "
                . "(`id`) ON DELETE CASCADE ON UPDATE CASCADE"
        ], $tableOptions);

        $this->createTable($classifier->classifierValueI18nTable, [
            'id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL AUTO_INCREMENT',
            'value_id' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
            'language_code' => Schema::TYPE_STRING . '(5) NOT NULL',
            'value' => Schema::TYPE_STRING . '(128) NOT NULL',
            'PRIMARY KEY (id)',
            "CONSTRAINT `FK_classifier_value_i18n_classifier_value` FOREIGN KEY "
            . "(`value_id`) REFERENCES `{$classifier->classifierValueTable}` "
            . "(`id`) ON DELETE CASCADE ON UPDATE CASCADE"
        ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $classifier = $this->getClassifier();

        $this->dropTable($classifier->classifierValueI18nTable);
        $this->dropTable($classifier->classifierValueTable);
        $this->dropTable($classifier->classifierTable);
        return false;
    }
}
