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
    /**
     * @var string
     */
    public $tablePrefix = 'tbl_';

    /**
     * Initializes classifier tables (imports db dumps with a given prefix)
     *
     * @throws \yii\db\Exception
     */
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

    /**
     * Loads classifier definition and updates tables
     * @param $definitionAlias
     * @throws \Exception
     */
    public function actionUpdate($definitionAlias)
    {
        $classifier = \Yii::$app->classifier;
        $importer = new Importer($classifier);
        $importer->import($definitionAlias);
    }
} 
