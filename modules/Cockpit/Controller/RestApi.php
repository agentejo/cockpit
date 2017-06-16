<?php
namespace Cockpit\Controller;

class RestApi extends \LimeExtra\Controller {

    public function authUser() {

        $response = ['error' => 'Authentication failed'];
        $data     = [ 'user' => $this->param('user'), 'password' => $this->param('password') ];

        if (!$data['user'] || !$data['password']) {
            return $response;
        }

        $user = $this->module('cockpit')->authenticate($data);

        if ($user) {
            $response = $user;
        }

        return $response;
    }

    public function saveUser() {

        $data = $this->param("user", false);
        $user = $this->module('cockpit')->getUser();

        if (!$data) {
            return false;
        }

        if ($user) {

            if (!isset($data["_id"]) && !$this->module('cockpit')->isSuperAdmin()) {
                return false;
            }

            if (!$this->module('cockpit')->isSuperAdmin() && $data["_id"] != $user["_id"] ) {
                return false;
            }
        }


        // new user needs a password
        if (!isset($data["_id"])) {

            // new user needs a password
            if (!isset($data["password"])) {
                return false;
            }

            // new user needs a username
            if (!isset($data["user"])) {
                return false;
            }

            $data = array_merge($account = [
                "user"     => "admin",
                "name"     => "",
                "email"    => "",
                "active"   => true,
                "group"    => "user",
                "i18n"     => "en"
            ], $data);
        }

        if (isset($data["password"])) {

            if (strlen($data["password"])){
                $data["password"] = $this->app->hash($data["password"]);
            } else {
                unset($data["password"]);
            }
        }

        $this->app->storage->save("cockpit/accounts", $data);

        if (isset($data["password"])) {
            unset($data["password"]);
        }

        return json_encode($data);
    }

    public function listUsers() {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            // Todo: user specific checks
        }

        $options = ["sort" => ["user" => 1]];

        if ($filter = $this->param('filter')) {

            $options['filter'] = $filter;

            if (is_string($filter)) {

                $options['filter'] = [
                    '$or' => [
                        ['name' => ['$regex' => $filter]],
                        ['user' => ['$regex' => $filter]],
                        ['email' => ['$regex' => $filter]],
                    ]
                ];
            }
        }

        $accounts = $this->storage->find("cockpit/accounts", $options)->toArray();

        foreach ($accounts as &$account) {
            unset($account["password"]);
        }

        return $accounts;
    }

    public function image() {
        
        $options = [
            'src' => $this->param('src', false),
            'mode' => $this->param('m', 'thumbnail'),
            'width' => intval($this->param('w', null)),
            'height' => intval($this->param('h', null)),
            'quality' => intval($this->param('q', 100)),
            'rebuild' => intval($this->param('r', false)),
            'base64' => intval($this->param('b64', false)),
            'output' => intval($this->param('o', false)),
            'domain' => intval($this->param('d', false)),
        ];

        foreach([
            'blur', 'brighten', 
            'colorize', 'contrast', 
            'darken', 'desaturate', 
            'edge detect', 'emboss', 
            'flip', 'invert', 'opacity', 'pixelate', 'sepia', 'sharpen', 'sketch'
        ] as $f) {
            if ($this->param($f)) $options[$f] = $this->param($f);
        }

        return $this->module('cockpit')->thumbnail($options);
    }

}
