imbo-http-hooks
===============

Fire HTTP requests when Imbo events occur

Usage
=====

* Require `rexxars/imbo-http-hooks` in your Imbo `composer.json`
* Run `composer install` to install and set up autoloading
* In your Imbo configuration file, under `eventListeners`, initialize the listener. Example:

```php
<?php
return [
    'eventListeners' => [
        'httpHooks' => [
            'listener' => 'Rexxars\Imbo\HttpHookListener',
            'params' => [
                // Event name => URLs
                'images.post' => [
                    'http://some.url/new-image.php',
                    'http://some.url/analyze-image.php'
                ],
                'metadata.post' => [
                    'http://some.url/metadata-updated.php',
                ]
            ]
        ]
    ]
];
```

The URLs you provide to the listener will then receive a HTTP POST request every time the provided event is triggered. The POST body will contain information in the following format:

```
    'event' => 'images.post',
    'url' => 'http://some.imbo.install/users/someuser/images',
    'imageIdentifier' => 'some image identifier',
    'publicKey' => 'someuser',
```

With that information and a Imbo client, you can easily fetch the image, metadata or do other actions based on the information received.

License
=======

MIT-licensed. See LICENSE.
