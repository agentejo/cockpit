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
     * @param \Lime\App $app
     */
    public function __construct($app) {
        parent::__construct($app);

        $this->before();
    }

    /**
     * Index action
     * @return string
     */
    public function index() {
        return 'Please implement the index action';
    }

    /**
     * Before hook
     */
    protected function before() { }

    /**
     * Render view
     * @param $view
     * @param array $params
     * @return mixed
     */
    protected function render($view, $params = array()) {

        $view .= $this->layout ? " with ".$this->layout:"";

        return $this->app->view($view, $params);
    }

    /**
     * Get request variable
     * @param string $key
     * @param mixed $default
     * @return Mixed
     */
    protected function param($key, $default=null) {
        return $this->app->request->param($key, $default);
    }

    /**
     * Get app module
     * @param string $module
     * @return \Lime\Module|null
     */
    protected function module($module) {
        return $this->app->module($module);
    }

    /**
     * Get app helper
     * @param string $name
     * @return \Lime\Helper
     */
    protected function helper($name) {
        return $this->app->helper($name);
    }

    /**
     * Stop app
     * @param bool|int $data - Response body or HTTP status
     * @param int $status - HTTP status
     */
    protected function stop($data = false, $status = null) {
        $this->app->stop($data, $status);
    }
}