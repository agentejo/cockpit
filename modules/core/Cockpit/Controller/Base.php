<?php

namespace Cockpit\Controller;

class Base extends \Cockpit\Controller {

    public function dashboard() {

        $stream = [];

        return $this->render('cockpit:views/base/dashboard.php', compact('stream'));
    }

    public function settings() {

        return $this->render('cockpit:views/base/settings.php');
    }

}