yii2-debug
=================

Debug panel for Yii 1.1 (ported from Yii 2).

[![Latest Stable Version](https://poser.pugx.org/zhuravljov/yii2-debug/version.svg)](https://packagist.org/packages/zhuravljov/yii2-debug)
[![Total Downloads](https://poser.pugx.org/zhuravljov/yii2-debug/downloads.png)](https://packagist.org/packages/zhuravljov/yii2-debug)

Installation
-------------

This extension is available at packagist.org and can be installed via composer by following command:

`composer require --dev zhuravljov/yii2-debug`.

If you want to install this extension manually just copy sources to `/protected/extensions` directory.

To enable toolbar in your application add following lines to config:

```php
return array(
    'preload' => array(
        'debug',
    ),
    'components' => array(
        'debug' => array(
            'class' => 'vendor.zhuravljov.yii2-debug.Yii2Debug', // composer installation
            //'class' => 'ext.yii2-debug.Yii2Debug', // manual installation
        ),
        'db' => array(
            'enableProfiling' => true,
            'enableParamLogging' => true,
        ),
    ),
);
```

Configuration
---------

You can customize debug panel behavior with this options:

- `enabled` - enable/disable debug panel.
- `allowedIPs` - list of IPs that are allowed to access debug toolbar. Default `array('127.0.0.1', '::1')`.
- `accessExpression` - additional php expression for access evaluation.
- `logPath` - directory storing the debugger data files. This can be specified using a path alias. Default `/runtime/debug`.
- `historySize` - maximum number of debug data files to keep. If there are more files generated, the oldest ones will be removed.
- `highlightCode` - highlight code. Highlight SQL queries and PHP variables. This parameter can be set for each panel individually.
- `moduleId ` - module ID for viewing stored debug logs. Default `debug`.
- `showConfig` - show brief application configuration page. Default `false`.
- `hiddenConfigOptions` - list of unsecure component options to hide (like login, passwords, secret keys).
  Default is to hide `username` and `password` of `db` component.
- `internalUrls` - use nice routes rules in debug module.
- `panels` - list of debug panels.

Each attached panel can be configured individually, for example:

```php
'debug' => array(
    'class' => 'ext.yii2-debug.Yii2Debug',
    'panels' => array(
        'db' => array(
            // Disable code highlighting.
            'highlightCode' => false,
            // Disable substitution of placeholders with values in SQL queries.
            'insertParamValues' => false,
        ),
    ),
),
```

Each panel have callback option `filterData`.
You can define custom function for filtering input data before writing it in to debug log.
It's useful when you need to hide something secret or just delete data from logs.
Be careful with data structure manipulation. It can lead to log parsing errors.

Example:

```php
'debug' => array(
    'class' => 'ext.yii2-debug.Yii2Debug',
    'panels' => array(
        'db' => array(
            'filterData' => function($data){
                // Your code here
                return $data;
            }
        ),
    ),
),
```

Creating own panels
-------------------------------

To create own debug panel you can extend class `Yii2DebugPanel`, for example:

```php
class MyTestPanel extends Yii2DebugPanel
{
    /**
     * The name of panel printed in debugger
     */
    public function getName()
    {
        return 'Name';
    }

    /**
     * Return summary html with results of execution in current request.
     * Data is available through $this->data
     */
    public function getSummary()
    {
        return '';
    }

    /**
     * Return detailed html report with results of execution in current request.
     * Data is available through $this->data
     */
    public function getDetail()
    {
        return '';
    }

    /**
     * Return data required for storing in logs.
     */
    public function save()
    {
        return array();
    }
}
```

And attach this panel in config:

```php
'panels' => array(
    'test' => array(
        'class' => 'path.to.panel.MyTestPanel',
        // ...
    ),
),
```

Variables dumping
---------------

With static method `Yii2Debug::dump()` you can dump any data and examine it later in debug log.

Miscellaneous
----------------

### Status Code

If you using PHP < 5.4, debug panel can't detect redirects by himself.
You can use following code as workaround:

```php
'panels' => array(
    'request' => array(
        'filterData' => function($data){
            if (empty($data['statusCode'])) {
                if (isset($data['responseHeaders']['Location'])) {
                    $data['statusCode'] = 302;
                } else {
                    $data['statusCode'] = 200;
                }
            }
            return $data;
        },
    ),
),
```

Such code just set 302 code if `Location` header is present.
Codes like 4xx and 5xx can be detected in debug panel by himself.
In PHP 5.4 and higher debug panel uses native php function `http_response_code()` for detecting http response code,
and there is no need to use this workaround.
