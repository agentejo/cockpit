<?php

namespace Cockpit;

class Controller extends \LimeExtra\Controller\Auth {

    protected $layout = 'cockpit:views/layouts/app.php';
    protected $user;

    public function __construct($app) {

        parent::__construct($app);

        $this->user = $app["user"] = $this("session")->read("app.auth");
        $this->data = $app->data;

        $controller = strtolower(str_replace('\\', '.', get_class($this)));

        $app->trigger("app.{$controller}.init", array($this));

    }

}