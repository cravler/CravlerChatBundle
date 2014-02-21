CravlerChatBundle
======================

## Installation

### Step 1: update your vendors by running

``` bash
$ php composer.phar require cravler/chat-bundle:@dev
```

### Step2: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...

        new Cravler\ChatBundle\CravlerChatBundle(),
    );
}
```

### Step3: Routing

``` yaml
// app/config/routing.yml

cravler_chat:
    resource: "@CravlerChatBundle/Resources/config/routing.xml"
    prefix:   /chat
```

## License

This bundle is under the MIT license. See the complete license in the bundle:

```
LICENSE
```