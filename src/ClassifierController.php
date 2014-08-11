<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 11.08.2014
 */

namespace opus\classifier;


use common\models\Classifier;
use Symfony\Component\Yaml\Yaml;
use yii\console\Controller;

/**
 * Class ClassifierController
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\classifier
 */
class ClassifierController extends Controller
{
    public $tablePrefix = 'tbl_';

    public function actionInit()
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'schema.sql';


        $prompt = "Import schemat at \"$path\" using prefix \"{$this->tablePrefix}\"?";
        $options = ['default' => true];

        if ($this->prompt($prompt, $options)) {
            $sql = file_get_contents($path);
            $sql = str_replace('{{prefix}}', $this->tablePrefix, $sql);

            \Yii::$app->db->createCommand($sql)->execute();
            echo "Done\n";
        }
    }

    public function actionUpdate($definitionAlias)
    {

        $classifier = \Yii::$app->classifier;

        $importer = new Importer($classifier);

        $importer->import($definitionAlias);


    }
} 
