# Cockpit

![Cockpit](http://getcockpit.com/assets/images/teaser.png)

The CMS for developers. Add content management functionality to any site - plug &amp; play CMS.
Manage content like collections and regions which you can reuse anywhere on your website.


* Homepage: [http://getcockpit.com](http://getcockpit.com)
* Twitter: [@getcockpit](http://twitter.com/getcockpit)


### Requirements

* PHP >= 5.4
* PDO + SQLite

### Installation

1. Download Cockpit and put the cockpit folder in the root of your web project
2. Make sure that the __/cockpit/storage__ folder and all its subfolders are writable
3. Go to __/cockpit/install__ via Browser
4. You're ready to use Cockpit :-)

### Usage

**Embed Cockpit**

Embedding Cockpit is really easy. Just include the following snippet anywhere you want to use Cockpit:

    <?php

        // make cockpit api available
        require('path2cockpit/bootstrap.php');

**Regions**

Render regions api:

    <div><?php region("adress") ?></div>
    <div><?=get_region("adress") ?></div>

**Collections**

Loop over collection data:

    <?php foreach(collection("posts")->find(["active"=>1]) as $post): ?>
        <div class="post">
            <h3><?=$post["title"];?></h3>
            <p>
                <?=$post["content"];?>
            </p>
        </div>
    <?php endforeach; ?>


## Copyright and license

Copyright 2013 [Agentejo](http://www.agentejo.com) under the [MIT license](https://raw.github.com/aheinze/cockpit/master/LICENSE).
