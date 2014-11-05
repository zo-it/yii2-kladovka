yii2-kladovka
=============

The collection of some classes for Yii 2.

```php
Yii::$classMap['yii\helpers\Html'] = '@yii/kladovka/helpers/Html.php';
```

```php
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
    'generators' => [
        'model2' => 'yii\kladovka\generators\model2\Generator',
        'search' => 'yii\kladovka\generators\search\Generator'
    ]
];
```
