<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 11.08.2014
 */

namespace opus\classifier;


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
