<?php

namespace LimeExtra\Controller;

class Auth extends \LimeExtra\Controller {

	public function __construct($app) {

		if(!isset($_SESSION["app.auth"])){

            $app->trigger('auth.authenticate');
            $app->stop();
		}

		parent::__construct($app);
	}
}