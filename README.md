Classifier component for Yii2
===============
Provides simple access to classifiers using Active Record. 

Installation
------------
The esiest way to obtain the code is using Composer: just modify your `composer.json` to add a custom repository (linking to this project) and require the libary.

```json
{
	"require": {
		"opus-online/yii2-classifier": "dev-master"
	}
}
```

Configuring
-----------
Just add the main class as a component to your Yii2 project configuration. Most of the time, this should do the trick
```php
'classifier' => [
    'class' => '\opus\classifier\Classifier',
],
```
With this configuration, 3 tables are used to access classifier data (you can also change the table names in the configuration):
* `ym_util_classifier`
* `ym_util_classifier_value`
* `ym_util_classifier_value_i18n`

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


Changelog
---------
next release [dev-master]
* Added Yaml importer
* Added PSR4 namespaces
* Removed models, added direct SQL access
* Added proper migrations
