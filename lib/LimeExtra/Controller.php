<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra;

/**
 * Class Controller
 * @package LimeExtra
 */
class Controller extends \Lime\AppAware {

    protected $layout = false;

    /**
     * @param $app
     */
    public function __construct($app) {
        parent::__construct($app);

        $this->before();
    }

    /**
     * @return string
     */
    public function index() {
        return 'Please implement the index action';
    }

    /**
     *
     */
    protected function before() { }

    /**
     * @param $view
     * @param array $params
     * @return mixed
     */
    protected function render($view, $params = array()) {

        $view .= $this->layout ? " with ".$this->layout:"";

        return $this->app->view($view, $params);
    }

    /**
     * @param $key
     * @param null $default
     * @return Mixed
     */
    protected function param($key, $default=null) {
        return $this->app->request->param($key, $default);
    }

    /**
     * @param $module
     * @return null
     */
    protected function module($module) {
        return $this->app->module($module);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function helper($name) {
        return $this->app->helper($name);
    }

    /**
     *
     */
    protected function stop($data = false, $status = null) {
        $this->app->stop($data, $status);
    }
}