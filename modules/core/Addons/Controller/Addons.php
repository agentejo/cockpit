<?php

namespace Addons\Controller;

class Addons extends \Cockpit\Controller {

	public function index(){
		
        $addons = [];

        foreach (new \DirectoryIterator($this->app->path('modules:addons')) as $addon) {

            if($addon->isFile() || $addon->isDot()) continue;

            $name = $addon->getFilename();

            $info = [
                "name"        => $name,
                "version"     => null,
                "description" => null,
                "homepage"    => null,
                "check_url"   => null   
            ];

            if ($meta = $this->app->path("modules:addons/{$name}/module.json")) {

                $meta = json_decode(file_get_contents($meta), true);

                if(!is_null($meta)) {
                    $info = array_merge($info, $meta);
                }
            }

            $info["path"] = "modules:addons/{$name}";

            $addons[] = $info;
        }

        return $this->render("addons:views/index.php", compact('addons'));
	}
}