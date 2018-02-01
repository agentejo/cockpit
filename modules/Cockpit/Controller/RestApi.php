<?php
namespace Cockpit\Controller;

class RestApi extends \LimeExtra\Controller {

    protected function before() {
        $this->app->response->mime = 'json';
    }

    public function authUser() {

        $data = [ 'user' => $this->param('user'), 'password' => $this->param('password') ];

        if (!$data['user'] || !$data['password']) {
            return $this->stop('{"error": "Missing user or password"}', 412);
        }

        $user = $this->module('cockpit')->authenticate($data);

        if (!$user) {
            return $this->stop('{"error": "Authentication failed"}', 401);
        }

        return $user;
    }

    public function saveUser() {

        $data = $this->param("user", false);
        $user = $this->module('cockpit')->getUser();

        if (!$data) {
            return false;
        }

        if ($user) {

            if (!isset($data["_id"]) && !$this->module('cockpit')->isSuperAdmin()) {
                return $this->stop(401);
            }

            if (!$this->module('cockpit')->isSuperAdmin() && $data["_id"] != $user["_id"] ) {
                return $this->stop(401);
            }
        }

        // new user needs a password
        if (!isset($data["_id"])) {

            // new user needs a password
            if (!isset($data["password"])) {
                return $this->stop('{"error": "User password required"}', 412);
            }

            // new user needs a username
            if (!isset($data["user"])) {
                return $this->stop('{"error": "User password required"}', 412);
            }

            $data = array_merge($account = [
                "user"     => "admin",
                "name"     => "",
                "email"    => "",
                "active"   => true,
                "group"    => "user",
                "i18n"     => "en"
            ], $data);

            if (isset($data['api_key'])) {
                $data['api_key'] = uniqid('account-').uniqid();
            }

            // check for duplicate users
            if ($user = $this->app->storage->findOne("cockpit/accounts", ["user" => $data["user"]])) {
                return $this->stop('{"error": "User already exists"}', 412);
            }
        }

        if (isset($data["password"])) {

            if (strlen($data["password"])){
                $data["password"] = $this->app->hash($data["password"]);
            } else {
                unset($data["password"]);
            }
        }

        $data["_modified"] = time();

        if (!isset($data['_id'])) {
            $data["_created"] = $data["_modified"];
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
            'fp' => $this->param('fp', null),
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

    public function assets() {

        $options = [
            'sort' => ['created' => -1]
        ];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($fields = $this->param('fields', null)) $options['fields'] = $fields;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        $assets = $this->storage->find("cockpit/assets", $options);
        $total  = (!$skip && !$limit) ? count($assets) : $this->storage->count("cockpit/assets", $filter);

        $this->app->trigger('cockpit.assets.list', [&$assets]);

        return [
            'assets' => $assets->toArray(),
            'total' => $total
        ];
    }

}
