<?php

namespace Cockpit\Controller;

class Base extends \Cockpit\AuthController {

    public function dashboard() {

        $settings = $this->app->storage->getKey('cockpit/options', 'dashboard.widgets.'.$this->user["_id"], []);

        $widgets  = new \ArrayObject([]);

        $this->app->trigger('admin.dashboard.widgets', [$widgets]);

        $areas = [
            'main' => new \SplPriorityQueue(),
            'aside-left' => new \SplPriorityQueue(),
            'aside-right' => new \SplPriorityQueue()
        ];

        foreach($widgets as &$widget) {

            $name = $widget['name'];
            $area = isset($widget['area']) && in_array($widget['area'], ['main', 'aside-left', 'aside-right']) ? $widget['area'] : 'main';

            $area = \Lime\fetch_from_array($settings, "{$name}/area", $area);
            $prio = \Lime\fetch_from_array($settings, "{$name}/prio", 0);

            $areas[$area]->insert($widget, -1 * $prio);
        }

        return $this->render('cockpit:views/base/dashboard.php', compact('areas', 'widgets'));
    }

    public function savedashboard() {

        $widgets = $this->app->param('widgets', []);

        $this->app->storage->setKey('cockpit/options', 'dashboard.widgets.'.$this->user["_id"], $widgets);

        return $widgets;
    }

    public function savemenu() {

        $order = $this->app->param('order', []);

        $this->app->storage->setKey('cockpit/options', 'app.menu.order.'.$this->user["_id"], $order);

        return $order;
    }

    public function search() {

        $query = $this->app->param("search", false);
        $list  = new \ArrayObject([]);

        if ($query) {
            $this->app->trigger("cockpit.search", [$query, $list]);
        }

        return json_encode($list->getArrayCopy());
    }

    public function call($module, $method) {

        $args = (array)$this->param('args', []);
        $acl  = $this->param('acl', null);

        if (!$acl) {
            return false;
        }

        if (!$this->module('cockpit')->hasaccess($module, $acl)) {
            return false;
        }

        $return = call_user_func_array([$this->app->module($module), $method], $args);

        return '{"result":'.json_encode($return).'}';
    }
}
