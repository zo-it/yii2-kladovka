Kladovka
========
The collection of some classes for Yii 2.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist ivan-chkv/yii2-kladovka "*"
```

or add

```
"ivan-chkv/yii2-kladovka": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by:

```php
<?php

use ivanchkv\kladovka\net\Curl;

$curl = Curl::init([
    'url' => 'http://realty.yandex.ru/gate/geoselector/get',
    'postFields' => [
        'crc' => $crc,
        'params[geoId]' => $geoId
    ],
    'cookie' => [
        'yandexuid' => $yandexuid
    ],
    'referer' => 'http://realty.yandex.ru/',
    'userAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0',
    'httpHeader' => [
        'Accept' => 'application/json',
        'X-Requested-With' => 'XMLHttpRequest'
    ],
    'connectTimeout' => 3,
    'timeout' => 5,
    'maxRetries' => 100,
    'beforeExecute' => function ($curl) {
        $proxy = Proxy::findRandom();
        $curl->proxyUrl($proxy->url)->proxyUser($proxy->user)->proxyPassword($proxy->password);
        return true;
    },
    'afterExecute' => function ($curl) {
        $result = $curl->getResult();
        if (preg_match('~Unexpected error AskerError~i', $result)) {
            return false; // try again
        }
        return true;
    }
]);
$result = $curl->execute();

?>
```
