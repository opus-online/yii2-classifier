Classifier component for Yii2
===============
Provides simple access to classifiers using Active Record. 

Installation
------------
The esiest way to obtain the code is using Composer: just modify your `composer.json` to add a custom repository (linking to this project) and require the libary.

```json
{
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/opus-online/yii2-classifier"
		}
	],
	"require": {
		"opus-online/yii2-classifier": "*"
	}
}
```

Configuring
-----------
Just add the main class as a component to your Yii2 project configuration. Most of the time, this should do the trick
```php
'classifier' => [
    'class' => '\opus\classifier\Classifier',
    'modelPrefix' => '\common\models\Rr',
    'useI18n' => true, // only if you want to use translated values
],
```
With this configuration, 3 models are used to access classifier data (you can also change the model names with `classMap` parameter):
* `RrClassifier`
* `RrClassifierValue`
* `RrClassifierValueI18n`

The model classes can be generated using [Gii Model Generator](https://github.com/opus-online/yii2-giimodel) after (modifying and importing) the SQL file in `data` directory.  

Basic usage
-----
```php
// retrieve a classifier value by ID or by CODE
$label = \Yii::$app->classifier->getValue(3)->name;
$id = \Yii::$app->classifier->getValue('CLASSIFIER_CODE', 'VALUE_CODE')->id;

// retrieve a list of values by classifier ID or CODE
$listOfObjects = \Yii::$app->classifier->getList(14);
$simpleList = \Yii::$app->classifier->getList('MY_CODE', true);
```
