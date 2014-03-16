# Cockpit

The CMS for developers. Add content management functionality to any site - plug &amp; play CMS.
Manage content like collections, regions, forms and galleries which you can reuse anywhere on your website.


![Cockpit](http://getcockpit.com/lib/assets/images/teaser.png)


* Homepage: [http://getcockpit.com](http://getcockpit.com)
* Twitter: [@getcockpit](http://twitter.com/getcockpit)


### Requirements

* PHP >= 5.4
* PDO + SQLite
* GD extension

make also sure that 
    
    $_SERVER['DOCUMENT_ROOT'] + $_SERVER["PATH_INFO"]

exists and is set correctly


### Installation

1. Download Cockpit and put the cockpit folder in the root of your web project or via composer <code>composer create-project aheinze/cockpit cockpit --stability="dev"</code>
2. Make sure that the __/cockpit/storage__ folder and all its subfolders are writable
3. Go to __/cockpit/install__ via Browser
4. You're ready to use Cockpit :-)

### Usage

**Embed Cockpit**

Embedding Cockpit is really easy. Just include the following snippet anywhere you want to use Cockpit:

```php
// make cockpit api available
require('path2cockpit/bootstrap.php');
```

**Regions**

Render regions api:

```php
<div><?php region("address") ?></div>
<div><?=get_region("address") ?></div>
```

**Collections**

Loop over collection data:

```php
<?php foreach(collection("posts")->find(["active"=>1]) as $post): ?>
    <div class="post">
        <h3><?=$post["title"];?></h3>
        <p>
            <?=$post["content"];?>
        </p>
    </div>
<?php endforeach; ?>
```

### Documentation

Please visit http://getcockpit.com/docs - any contributions are welcome: https://github.com/aheinze/cockpit-docs


### Language files

Please visit and contribute to https://github.com/aheinze/cockpit-i18n

### Support

Google group: [CockpitCMS](https://groups.google.com/d/forum/cockpitcms)


### Copyright and license

Copyright 2013 [Agentejo](http://www.agentejo.com) under the [MIT license](https://raw.github.com/aheinze/cockpit/master/LICENSE).
