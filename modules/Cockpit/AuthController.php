<?php

namespace Cockpit;

class AuthController extends \LimeExtra\Controller {

    protected $layout = 'cockpit:views/layouts/app.php';
    protected $user;


    /** Controller access group */
    protected $accessGroup;

    /** Controller access resource */
    protected $accessResource;

    public function __construct($app) {

        $user = $app->module('cockpit')->getUser();

        if (!$user) {
            $app->reroute('/auth/login');
            $app->stop();
        }

        parent::__construct($app);

        $this->user    = $app["user"] = $user;
        $this->storage = $app->storage;

        $controller = strtolower(str_replace('\\', '.', get_class($this)));

        // Resolve access group and resource (ie. cockpit, settings)
        list ($accessGroup, ,$accessResource) = explode('\\', strtolower(get_class($this)));

        // Set when not declared
        if (!$this->accesssGroup) {
            $this->accessGroup = $accessGroup;
        }

        if (!$this->accessResource) {
            $this->accessResource = $accessResource;
        }

        $app->trigger("app.{$controller}.init", [$this]);

    }

    /**
     * Check if current user has access to current resource
     * @param array [$actions]
     * @return boolean
     */
    protected function hasAccess($actions = []) {
        return $this->module('cockpit')->isSuperAdmin() || $this->module('cockpit')->hasAccess(
            $this->accessGroup,
            $this->accessResource,
            $actions
        );
    }

}
