<?php

namespace Cockpit\Controller;

class Groups extends \Cockpit\AuthController {

    public function index() {

        if (!$this->module('cockpit')->hasaccess('cockpit', 'groups')) {
            //return $this->helper('admin')->denyRequest();
        }

        $current  = $this->user["_id"];
        $groups   = $this->module('cockpit')->getGroups();

        return $this->render('cockpit:views/groups/index.php', compact('current', 'groups'));
    }

    public function group($gid=null) {

        if (!$gid) {
            $gid = $this->group["_id"];
        }

        $group = $this->app->storage->findOne("cockpit/groups", ["_id" => $gid]);

        if (!$group) {
            return false;
        }

        $fields    = $this->app->retrieve('config/groups/fields', null);
        //$groups    = $this->module('cockpit')->getGroups();

        return $this->render('cockpit:views/groups/group.php', compact('group', 'gid', 'fields'));
    }

    public function create() {

        $collections = $this->module('collections')->collections();
        $group   = ["group"=>"", "admin" => false, "backend" => true, "finder" => true];

        //$languages = $this->getLanguages();
        //$groups    = $this->module('cockpit')->getGroups();

        //return $this->render('cockpit:views/groups/groups.php', compact('account', 'uid', 'languages', 'groups'));

        return $this->render('cockpit:views/groups/group.php', compact('group', 'collections'));
    }

    public function save() {

        if ($data = $this->param("group", false)) {

            $data["_modified"] = time();

            if (!isset($data['_id'])) {
                $data["_created"] = $data["_modified"];
            }

            $this->app->storage->save("cockpit/groups", $data);

            /*
            if ($data["_id"] == $this->user["_id"]) {
                $this->module("cockpit")->setUser($data);
            }
            */

            return json_encode($data);
        }

        return false;

    }

    public function remove() {

        if ($data = $this->param("group", false)) {

            // user can't delete himself
            if ($data["_id"] != $this->user["_id"]) {

                $this->app->storage->remove("cockpit/groups", ["_id" => $data["_id"]]);

                return '{"success":true}';
            }
        }

        return false;
    }

    public function find() {

      /*
      $options = array_merge([
          'sort' => ['user' => 1]
      ], $this->param('options', []));
      */

      $options =  $this->param('options', []);

      /*
      if (isset($options['filter'])) {

         if (is_string($options['filter'])) {
             // TODO .... rest of c&p ... think this just may be removed
            $options['filter'] = [
                '$or' => [
                    ['name' => ['$regex' => $options['filter']]],
                    ['user' => ['$regex' => $options['filter']]],
                    ['email' => ['$regex' => $options['filter']]],
                ]
            ];
         }
      }
      */

      $groups = $this->storage->find("cockpit/groups", $options)->toArray(); // get groups from db
      $count = (!isset($options['skip']) && !isset($options['limit'])) ? count($groups) : $this->storage->count("cockpit/groups", isset($options['filter']) ? $options['filter'] : []);
      $pages = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
      $page = 1;

      if ($pages > 1 && isset($options['skip'])) {
         $page = ceil($options['skip'] / $options['limit']) + 1;
      }

      return compact('groups', 'count', 'pages', 'page');
   }

}
