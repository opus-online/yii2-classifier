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

Configuration
-----------
Just add the main class as a component to your Yii2 project configuration
```php
'classifier' => [
    'class' => '\opus\classifier\Classifier',
],
```

And create the necessary tables using the command

```bash
php yii migrate --migrationPath=@vendor/opus-online/yii2-classifier/migrations
```

With default configuration, 3 tables are created (you can change the table names in the configuration):
* `ym_util_classifier`
* `ym_util_classifier_value`
* `ym_util_classifier_value_i18n`

Definition
----------
Define your classifiers in a yaml file like this:
```yaml
MY_CLASSIFIER:
  name: Label for the classifier
  system: 1 # (1/0, system variables should not be changed by users)
  values:
    MY_VALUE_1: [Value label, OptionalCustomAttributes]
    MY_VALUE_2: [Value 2]
  description: Some optional description
GENDER:
  name: Gender
  values:
    MALE: [Male]
    FEMALE: [Female]
    OTHER: [Other]
```

And import them into the database using the command
```
php yii classifier/update @alias/to/classifiers.yml
```

Usage
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
1.1.1
* Fixed PHP Notice bug when using caching
* Fixed PHP Notice bug when using classifiers without values
* Fixed classifier value ordering bug

1.1
* Added Yaml importer
* Added PSR4 namespaces
* Removed models, added direct SQL access
* Added proper migrations
