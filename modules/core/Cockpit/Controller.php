<?php

namespace Cockpit;

class Controller extends \LimeExtra\Controller {

    protected $layout = 'cockpit:views/layouts/app.php';
    protected $user;

    public function __construct($app) {

        $user = $app->module("auth")->getUser();

        if(!$user){
            $app->reroute('/auth/login');
            $app->stop();
        }

        parent::__construct($app);

        $this->user   = $app["user"] = $user;
        $this->db     = $app->db;
        $this->memory = $app->memory;

        $controller = strtolower(str_replace('\\', '.', get_class($this)));

        $app->trigger("app.{$controller}.init", [$this]);

    }

}