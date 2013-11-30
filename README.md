# Cockpit

The CMS for developers. Add content management functionality to any site - plug &amp; play CMS.
Manage content like collections and regions which you can reuse anywhere on your website.


* Homepage: [http://getcockpit.com](http://getcockpit.com)
* Twitter: [@getcockpit](http://twitter.com/getcockpit)


### Requirements

* PHP >= 5.4
* PDO + SQLite

### Installation

1. Make sure that the __/storage__ folder and all its subfolders are writable
2. Go to __/install__ via Browser
3. You're ready to use Cockpit :-)

### Usage

**Require Cockpit**

    <?php

        // make cockpit api available
        require('cockpit/bootstrap.php');

**Regions**

    <div><?php region("adress") ?></div>

**Collections**

    <?php foreach(collection("posts")->find(["active"=>1]) as $post): ?>
        <div class="post">
            <h3><?=$post["title"];?></h3>
            <p>
                <?=$post["content"];?>
            </p>
        </div>
    <?php endforeach; ?>