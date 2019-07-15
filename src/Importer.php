<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 11.08.2014
 */

namespace opus\classifier;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Command;
use yii\db\Exception;

/**
 * Imports classifiers from a definition file
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\classifier
 */
class Importer
{

    /**
     * @var Classifier
     */
    private $classifier;

    /**
     * @param Classifier $classifier
     */
    public function __construct(Classifier $classifier)
    {
        $this->classifier = $classifier;
    }

    /**
     * @param string $pathAlias
     * @throws \Exception
     * @throws Exception
     */
    public function import($pathAlias)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $filePath = Yii::getAlias($pathAlias);
            $conf = Yaml::parse($filePath);

            foreach ($conf as $classifierCode => $classifierConf) {
                $this->importClassifier(
                    $classifierCode,
                    $classifierConf
                );
                $valueSequence = 0;
                foreach ($classifierConf['values'] as $valueCode => $valueConf) {
                    $valueConf['sequence'] = ++$valueSequence;
                    $valueConf['code'] = $valueCode;
                    $this->importValue($classifierCode, $valueConf);
                }
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * @param string $classifierCode
     * @param array $classifierConf
     * @return ActiveRecord
     * @throws Exception
     */
    private function importClassifier($classifierCode, array $classifierConf)
    {
        $tableName = $this->classifier->classifierTable;

        $sql = "
            INSERT INTO `{$tableName}` (`code`, `name`, `is_system_classifier`, `description`)
            VALUES (:code, :name, :system, :description)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`),
                `is_system_classifier`= VALUES(`is_system_classifier`),
                `description` = VALUES(`description`)
        ";

        $params = [
            ':code' => $classifierCode,
            ':name' => $classifierConf['name'],
            ':system' => empty($classifierConf['system']) ? 0 : 1,
            ':description' => empty($classifierConf['description']) ? null : $classifierConf['description'],
        ];

        $this->createCommand($sql, $params)->execute();

    }


    /**
     * @param string $classifierCode
     * @param array $valueConf
     */
    private function importValue($classifierCode, array $valueConf)
    {
        $classifierTable = $this->classifier->classifierTable;
        $sql = "SELECT `id` FROM `{$classifierTable}` WHERE `code` = :code";
        $classifierId = $this->createCommand($sql, [':code' => $classifierCode])
            ->queryScalar();

        if (null === $classifierId) {
            throw new RuntimeException('Could not match classifier ID');
        }

        $tableName = $this->classifier->classifierValueTable;

        $sql = "
            INSERT INTO `{$tableName}` (`classifier_id`, `code`, `name`, `attributes`, `order_no`)
            VALUES (:classifierId, :code, :name, :attributes, :orderNo)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`),
                `attributes`= VALUES(`attributes`),
                `order_no` = VALUES(`order_no`)
        ";

        $params = [
            ':classifierId' => $classifierId,
            ':code' => $valueConf['code'],
            ':orderNo' => $valueConf['sequence'],
            ':name' => $valueConf[0],
            ':attributes' => isset($valueConf[1]) ? $valueConf[1] : null,
        ];

        $this->createCommand($sql, $params)->execute();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return Command
     */
    private function createCommand($sql, $params)
    {
        return Yii::$app->db->createCommand($sql, $params);
    }
}
