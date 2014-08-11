<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 11.08.2014
 */

namespace opus\classifier;

use Symfony\Component\Yaml\Yaml;
use yii\db\ActiveRecord;

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
     * @throws \yii\db\Exception
     */
    public function import($pathAlias)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $filePath = \Yii::getAlias($pathAlias);
            $conf = Yaml::parse($filePath);

            foreach ($conf as $classifierCode => $classifierConf) {
                $model = $this->importClassifier(
                    $classifierCode,
                    $classifierConf
                );
                $valueSequence = 0;
                foreach ($classifierConf['values'] as $valueCode => $valueConf) {
                    $valueConf['sequence'] = ++$valueSequence;
                    $valueConf['code'] = $valueCode;
                    $this->importValue($model, $valueConf);
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
     */
    private function importClassifier($classifierCode, array $classifierConf)
    {
        $modelClass = $this->classifier->classMap['Classifier'];
        /** @var ActiveRecord $model */
        /** @var ActiveRecord $modelClass */
        $model = $modelClass::findOne(['code' => $classifierCode]);

        if ($model === null) {
            $model = new $modelClass;
            $model->setAttribute($this->classifier->attributeMap['code'], $classifierCode);
        }
        $model->name = $classifierConf['name'];
        $model->is_system_classifier = empty($classifierConf['system']) ? 0 : 1;
        if (!empty($classifierConf['description'])) {
            $model->description = $classifierConf['description'];
        }

        $this->saveModel($model);
        return $model;
    }



    /**
     * @param ActiveRecord $model
     */
    private function saveModel(ActiveRecord $model)
    {
        if (!$model->save()) {
            throw new \RuntimeException('Could not save model');
        }
    }

    /**
     * @param ActiveRecord $classifier
     * @param array $valueConf
     */
    private function importValue(ActiveRecord $classifier, array $valueConf)
    {
        $modelClass = $this->classifier->classMap['ClassifierValue'];
        /** @var ActiveRecord $model */
        $params = [
            $this->classifier->attributeMap['classifier_id'] =>
                $classifier->getAttribute($this->classifier->attributeMap['id']),
            $this->classifier->attributeMap['code'] =>
                $valueConf['code'],
        ];
        /** @var ActiveRecord $modelClass */
        $model = $modelClass::findOne($params);

        if ($model === null) {
            $model = new $modelClass;
            $model->setAttributes($params);
        }
        // numeric indices
        $model->name = $valueConf[0];
        $model->attributes = isset($valueConf[1]) ? $valueConf[1] : null;
        $model->order_no = $valueConf['sequence'];

        $this->saveModel($model);
    }
}
