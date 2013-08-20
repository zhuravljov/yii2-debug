yii2-debug
==========

Отладочная панель для Yii 1.1 портированная из Yii 2.

Использование
-------------

Необходимо скопировать исходники в `/protected/extensions` и дополнить конфиг своего проекта следующими настройками:

```php
return array(
    'preload' => array(
        'debug',
    ),
    'components' => array(
        'debug' => array(
            'class' => 'ext.yii2-debug.Yii2Debug',
        ),
        'db' => array(
            'enableProfiling' => true,
            'enableParamLogging' => true,
        ),
    ),
);
```
