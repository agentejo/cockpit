<?php

namespace Addons\Controller;

class Addons extends \Cockpit\Controller {

	public function index(){
		
        $addons = [];

        foreach (new \DirectoryIterator($this->app->path('modules:addons')) as $addon) {

            if($addon->isFile() || $addon->isDot()) continue;

            $addons[] = $addon->getFilename();
        }


        return $this->render("addons:views/index.php", compact('addons'));
	}
}