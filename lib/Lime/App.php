<?php
/*
 * Lime.
 *
 * Copyright (c) 2014 Artur Heinze
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Lime;

include(__DIR__.'/Request.php');
include(__DIR__.'/Response.php');


class App implements \ArrayAccess {

    protected static $apps = [];

    protected $registry = [];
    protected $routes   = [];
    protected $paths    = [];
    protected $events   = [];
    protected $blocks   = [];

    protected $exit     = false;

    /** @var Response|null  */
    public $response    = null;

    /** @var Request|null  */
    public $request    = null;

    public $helpers;
    public $layout      = false;

    /* global view variables */
    public $viewvars    = [];

    /**
    * Constructor
    * @param Array $settings initial registry settings
    */
    public function __construct ($settings = []) {

        $self = $this;
        $base_url = implode('/', \array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));

        $this->registry = \array_merge([
            'debug'        => true,
            'app.name'     => 'LimeApp',
            'session.name' => 'limeappsession',
            'autoload'     => new \ArrayObject([]),
            'sec-key'      => 'xxxxx-SiteSecKeyPleaseChangeMe-xxxxx',
            'route'        => $_SERVER['PATH_INFO'] ?? '/',
            'charset'      => 'UTF-8',
            'helpers'      => [],
            'base_url'     => $base_url,
            'base_route'   => $base_url,
            'base_host'    => $_SERVER['SERVER_NAME'] ?? \php_uname('n'),
            'base_port'    => $_SERVER['SERVER_PORT'] ?? 80,
            'docs_root'    => null,
            'site_url'     => null
        ], $settings);

        // app modules container
        $this->registry['modules'] = new \ArrayObject([]);

        // try to guess site url
        if (!isset($this['site_url']) && \PHP_SAPI !== 'cli') {

            $url = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https':'http').'://';

            if (!\in_array($this->registry['base_port'], ['80', '443'])) {
                $url .= $this->registry['base_host'].':'.$this->registry['base_port'];
            } else {
                $url .= $this->registry['base_host'];
            }

            $this->registry['site_url'] = \trim($url, '/');
        }

        if (!isset($this['docs_root'])) {
            $this->registry['docs_root'] = \str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? \realpath($_SERVER['DOCUMENT_ROOT']) : \dirname($_SERVER['SCRIPT_FILENAME']));
        }

        // make sure base + route url doesn't end with a slash;
        $this->registry['base_url']   = \rtrim($this->registry['base_url'], '/');
        $this->registry['base_route'] = \rtrim($this->registry['base_route'], '/');

        // default global viewvars
        $this->viewvars['app']        = $this;
        $this->viewvars['base_url']   = $this->registry['base_url'];
        $this->viewvars['base_route'] = $this->registry['base_route'];
        $this->viewvars['docs_root']  = $this->registry['docs_root'];

        self::$apps[$this['app.name']] = $this;

        // default helpers
        $this->helpers = new \ArrayObject(\array_merge(['session' => 'Lime\\Helper\\Session', 'cache' => 'Lime\\Helper\\Cache'], $this->registry['helpers']));

        // register simple autoloader
        spl_autoload_register(function ($class) use($self){

            foreach ($self->retrieve('autoload', []) as $dir) {

                $class_file = $dir.'/'.\str_replace('\\', '/', $class).'.php';

                if (\file_exists($class_file)){
                    include_once($class_file);
                    return;
                }
            }
        });

        if (PHP_SAPI !== 'cli') {
            $this->request = $this->getRequestfromGlobals();
        }
    }

    /**
    * Get App instance
    * @param  String $name Lime app name
    * @return Object       Lime app object
    */
    public static function instance($name) {
        return self::$apps[$name];
    }

    /**
    * Returns a closure that stores the result of the given closure
    * @param  String  $name
    * @param  \Closure $callable
    * @return Object
    */
    public function service($name, $callable) {

        $this->registry[$name] = function($c) use($callable) {
            static $object;

            if (null === $object) {
                $object = $callable($c);
            }

            return $object;
        };

        return $this;
    }

    /**
    * stop application (exit)
    */
    public function stop($data = false, $status = null){

        $this->exit = true;

        if (!isset($this->response)) {
            
            if (\is_array($data) || \is_object($data)) {
                $data = \json_encode($data);
            }

            if ($data) {
                echo $data;
            }
            
            exit;
        }

        if ($status) {
           $this->response->status = $status;
        }

        if ($data) {
            $this->response->body = $data;
        }

        if (\is_numeric($data) && isset(Response::$statusCodes[$data])) {

            $this->response->status = $data;

            if ($this->response->mime == 'json') {
                $this->response->body = \json_encode(['error' => Response::$statusCodes[$data]]);
            } else {
                $this->response->body = Response::$statusCodes[$data];
            }
        }

        if ($data || $status) {
            echo $this->response->flush();
        }

        exit;
    }

    /**
    * Is application stopped?
    * @return boolean
    */
    public function isExit() {
        return $this->exit;
    }

    /**
    * Returns link based on the base url of the app
    * @param  String $path e.g. /js/myscript.js
    * @return String       Link
    */
    public function baseUrl($path) {

        $url = '';

        if (\strpos($path, ':')===false) {

            /*
            if ($this->registry['base_port'] != '80') {
                $url .= $this->registry['site_url'];
            }
            */

            $url .= $this->registry['base_url'].'/'.\ltrim($path, '/');

        } else {
            $url = $this->pathToUrl($path);
        }

        return $url;
    }

    public function base($path) {

        $args = \func_get_args();

        echo (\count($args)==1) ? $this->baseUrl($args[0]) : $this->baseUrl(\call_user_func_array('sprintf', $args));
    }

    /**
    * Returns link based on the route url of the app
    * @param  String $path e.g. /pages/home
    * @return String       Link
    */
    public function routeUrl($path) {

        $url = '';

        /*
        if ($this->registry['base_port'] != '80') {
            $url .= $this->registry['site_url'];
        }
        */

        $url .= $this->registry['base_route'];

        return $url.'/'.\ltrim($path, '/');
    }

    public function route() {

        $args = \func_get_args();

        echo (\count($args)==1) ? $this->routeUrl($args[0]) : $this->routeUrl(\call_user_func_array('sprintf', $args));

    }

    /**
    * Redirect to path.
    * @param  String $path Path redirect to.
    * @return void
    */
    public function reroute($path) {

        if (\strpos($path,'://') === false) {
            if (\substr($path,0,1)!='/'){
                $path = '/'.$path;
            }
            $path = $this->routeUrl($path);
        }

        \header('Location: '.$path);
        $this->stop();
    }

    /**
    * Put a value in the Lime registry
    * @param String $key  Key name
    * @param Mixed $value  Value
    */
    public function set($key, $value) {

        $keys = \explode('/',$key);

        if (\count($keys)>5) return false;

        switch (\count($keys)){

          case 1:
            $this->registry[$keys[0]] = $value;
            break;

          case 2:
            $this->registry[$keys[0]][$keys[1]] = $value;
            break;

          case 3:
            $this->registry[$keys[0]][$keys[1]][$keys[2]] = $value;
            break;

          case 4:
            $this->registry[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
            break;

          case 5:
            $this->registry[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
            break;
        }

        return $this;
    }

    /**
    * Get a value from the Lime registry
    * @param  String $key
    * @param  Mixed $default
    * @return Mixed
    */
    public function retrieve($key, $default=null) {
        return fetch_from_array($this->registry, $key, $default);
    }


    /**
    * Path helper method
    * @return Mixed
    */
    public function path(){

        $args = \func_get_args();

        switch (\count($args)){

            case 1:

                $file  = $args[0];

                if ($this->isAbsolutePath($file) && \file_exists($file)) {
                    return $file;
                }

                $parts = \explode(':', $file, 2);

                if (count($parts)==2){
                    if (!isset($this->paths[$parts[0]])) return false;

                    foreach ($this->paths[$parts[0]] as &$path){
                        if (\file_exists($path.$parts[1])){
                            return $path.$parts[1];
                        }
                    }
                }

                return false;

            case 2:

                if (!isset($this->paths[$args[0]])) {
                    $this->paths[$args[0]] = [];
                }
                \array_unshift($this->paths[$args[0]], \rtrim(\str_replace(DIRECTORY_SEPARATOR, '/', $args[1]), '/').'/');

                return $this;
        }

        return null;
    }

    /**
     * @param $namespace
     * @return array
     */
    public function paths($namespace = null) {

        if (!$namespace) {
            return $this->paths;
        }

        return $this->paths[$namespace] ?? [];
    }

    /**
     * @param $path
     * @return bool|string
     */
    public function pathToUrl($path, $full = false) {

        $url = false;

        if ($file = $this->path($path)) {

            $file = \str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $root = \str_replace(DIRECTORY_SEPARATOR, '/', $this['docs_root']);

            $url = '/'.\ltrim(\str_replace($root, '', $file), '/');
            $url = \implode('/', \array_map('rawurlencode', explode('/', $url)));

            if ($full) {
                $site_url = str_replace(parse_url($this->registry['site_url'], \PHP_URL_PATH), '', $this->registry['site_url']);
                $url = \rtrim($site_url, '/').$url;
            }
        }

        return $url;
    }

    /**
    * Cache helper method
    * @return Mixed
    */
    public function cache(){

        $args = \func_get_args();

        switch(\count($args)){
        case 1:
            return $this->helper('cache')->read($args[0]);
        case 2:
            return $this->helper('cache')->write($args[0], $args[1]);
        }

        return null;
    }

    /**
    * Bind an event to closure
    * @param  String  $event
    * @param  \Closure $callback
    * @param  Integer $priority
    * @return void
    */
    public function on($event, $callback, $priority = 0){

        if (\is_array($event)) {

            foreach ($event as &$evt) {
                $this->on($evt, $callback, $priority);
            }
            return $this;
        }

        if (!isset($this->events[$event])) $this->events[$event] = [];

        // make $this available in closures
        if (\is_object($callback) && $callback instanceof \Closure) {
            $callback = $callback->bindTo($this, $this);
        }

        $this->events[$event][] = ['fn' => $callback, 'prio' => $priority];

        return $this;
    }

    /**
    * Trigger event.
    * @param  String $event
    * @param  Array  $params
    * @return Boolean
    */
    public function trigger($event,$params=[]){

        if (!isset($this->events[$event])){
            return $this;
        }

        if (!\count($this->events[$event])){
            return $this;
        }

        $queue = new \SplPriorityQueue();

        foreach ($this->events[$event] as $index => $action){
            $queue->insert($index, $action['prio']);
        }

        $queue->top();

        while ($queue->valid()){
            $index = $queue->current();
            if (\is_callable($this->events[$event][$index]['fn'])){
                if (\call_user_func_array($this->events[$event][$index]['fn'], $params) === false) {
                    break; // stop Propagation
                }
            }
            $queue->next();
        }

        return $this;
    }

    /**
    * Render view.
    * @param  String $____template Path to view
    * @param  Array  $_____slots   Passed variables
    * @return String               Rendered view
    */
    public function render($____template, $_____slots = []) {

        $this->trigger('app.render.view', [&$____template, &$_____slots]);

        if (\is_string($____template) && $____template) {
            $this->trigger("app.render.view/{$____template}", [&$____template, &$slots]);
        }

        $_____slots = \array_merge($this->viewvars, $_____slots);
        $____layout = $this->layout;

        if (\strpos($____template, ' with ') !== false ) {
            list($____template, $____layout) = \explode(' with ', $____template, 2);
        }

        if (\strpos($____template, ':') !== false && $____file = $this->path($____template)) {
            $____template = $____file;
        }

        $extend = function($from) use(&$____layout) {
            $____layout = $from;
        };

        \extract((array)$_____slots);

        \ob_start();
        include $____template;
        $output = \ob_get_clean();

        if ($____layout) {

            if (\strpos($____layout, ':') !== false && $____file = $this->path($____layout)) {
                $____layout = $____file;
            }

            $content_for_layout = $output;

            \ob_start();
            include $____layout;
            $output = \ob_get_clean();

        }

        return $output;
    }

    /**
    * Start block
    * @param  String $name
    * @return Null
    */
    public function start($name) {

        if (!isset($this->blocks[$name])){
            $this->blocks[$name] = [];
        }

        \ob_start();
    }

    /**
    * End block
    * @param  String $name
    * @return Null
    */
    public function end($name) {

        $out = \ob_get_clean();

        if (isset($this->blocks[$name])){
            $this->blocks[$name][] = $out;
        }
    }

    /**
    * Get block content
    * @param  String $name
    * @param  array  $options
    * @return String
    */
    public function block($name, $options=[]) {

        if (!isset($this->blocks[$name])) return null;

        $options = \array_merge([
            'print' => true
        ], $options);

        $block = \implode("\n", $this->blocks[$name]);

        if ($options['print']){
            echo $block;
        }

        return $block;
    }

    /**
    * Escape string.
    * @param  String $string
    * @param  String $charset
    * @return String
    */
    public function escape($string, $charset=null) {

        if (\is_null($charset)){
            $charset = $this['charset'];
        }

        return \htmlspecialchars($string, \ENT_QUOTES, $charset);
    }

    /**
    * Get style inc. markup
    * @param  String $href
    * @return String
    */
    public function style($href, $version=false) {

        $output = '';

        $type = 'text/css';
        $rel  = 'stylesheet';
        $src = $href;

        if (\is_array($href)) {
            extract($href, \EXTR_OVERWRITE);
        }

        $ispath = \strpos($src, ':') !== false && !\preg_match('#^(|http\:|https\:)//#', $src);
        $output = '<link href="'.($ispath ? $this->pathToUrl($src):$src).($version ? "?ver={$version}":"").'" type="'.$type.'" rel="'.$rel.'">';

        return $output;
    }

    /**
    * Get script inc. markup
    * @param  String $src
    * @return String
    */
    public function script($src, $version=false){

        $output = '';

        $type = 'text/javascript';
        $load = '';

        if (\is_array($src)) {
            extract($src, \EXTR_OVERWRITE);
        }

        $ispath = \strpos($src, ':') !== false && !\preg_match('#^(|http\:|https\:)//#', $src);
        $output = '<script src="'.($ispath ? $this->pathToUrl($src):$src).($version ? "?ver={$version}":"").'" type="'.$type.'" '.$load.'></script>';

        return $output;
    }

    public function assets($src, $version=false){

        $list = [];

        foreach ((array)$src as $asset) {

            $src = $asset;

            if (\is_array($asset)) {
                extract($asset, \EXTR_OVERWRITE);
            }

            if (@\substr($src, -3) == '.js') {
                $list[] = $this->script($asset, $version);
            }

            if (@\substr($src, -4) == '.css') {
                $list[] = $this->style($asset, $version);
            }
        }

        return \implode("\n", $list);
    }

    /**
    * Bind GET request to route
    * @param  String  $path
    * @param  \Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function get($path, $callback, $condition = true){
        if (!$this->request->is('get')) return;
        $this->bind($path, $callback, $condition);
    }

    /**
    * Bind POST request to route
    * @param  String  $path
    * @param  \Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function post($path, $callback, $condition = true){
        if (!$this->request->is('post')) return;
        $this->bind($path, $callback, $condition);
    }

    /**
    * Bind Class to routes
    * @param  String $class
    * @return void
    */
    public function bindClass($class, $alias = false) {

        $self  = $this;
        $clean = $alias ? $alias : \trim(\strtolower(\str_replace("\\", "/", $class)), "\\");

        $this->bind('/'.$clean.'/*', function() use($self, $class, $clean) {

            $parts      = \explode('/', \trim(\preg_replace("#$clean#","",$self["route"],1),'/'));
            $action     = isset($parts[0]) ? $parts[0]:"index";
            $params     = \count($parts)>1 ? \array_slice($parts, 1):[];

            return $self->invoke($class,$action, $params);
        });

        $this->bind('/'.$clean, function() use($self, $class) {
            return $self->invoke($class, 'index', []);
        });
    }

    /**
    * Bind namespace to routes
    * @param  String $namespace
    * @return void
    */
    public function bindNamespace($namespace, $alias) {

        $self  = $this;
        $clean = $alias ? $alias : \trim(\strtolower(\str_replace("\\", "/", $namespace)), "\\");

        $this->bind('/'.$clean.'/*', function() use($self, $namespace, $clean) {

            $parts      = \explode('/', trim(preg_replace("#$clean#","",$self["route"],1),'/'));
            $class      = $namespace.'\\'.$parts[0];
            $action     = isset($parts[1]) ? $parts[1]:"index";
            $params     = \count($parts)>2 ? \array_slice($parts, 2):[];

            return $self->invoke($class,$action, $params);
        });

        $this->bind('/'.\strtolower($namespace), function() use($self, $namespace) {

            $class = $namespace."\\".\array_pop(\explode('\\', $namespace));

            return $self->invoke($class, 'index', []);
        });
    }

    /**
    * Bind request to route
    * @param  String  $path
    * @param  \Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function bind($path, $callback, $condition = true) {

        if (!$condition) return;

        if (!isset($this->routes[$path])) {
            $this->routes[$path] = [];
        }

        // make $this available in closures
        if (\is_object($callback) && $callback instanceof \Closure) {
            $callback = $callback->bindTo($this, $this);
        }

        // autou-register for /route/* also /route
        if (\substr($path, -2) == '/*' && !isset($this->routes[\substr($path, 0, -2)])) {
            $this->bind(\substr($path, 0, -2), $callback, $condition);
        }

        $this->routes[$path] = $callback;
    }

    /**
    * Run Application
    * @param  String $route Route to parse
    * @return void
    */
    public function run($route = null, $request = null, $flush = true) {

        $self = $this;

        if ($route) {
            $this->registry['route'] = $route;
        }

        if ($request) {
            $this->request = $request;
        }

        if (!isset($this->request)) {
            $this->request = $this->getRequestfromGlobals();
        }

        \register_shutdown_function(function() use($self){
            \session_write_close();
            $self->trigger('shutdown');
        });

        $this->request->route = $this->registry['route'];

        $this->response = new Response();
        $this->trigger('before');
        $this->response->body = $this->dispatch($this->registry['route']);

        if ($this->response->body === false) {
            $this->response->status = 404;
        }

        $this->trigger('after');

        if ($flush) {
            $this->response->flush();
        }

        return $this->response;
    }

    /**
    * Dispatch route
    * @param  String $path
    * @return Mixed
    */
    public function dispatch($path) {

            $found  = false;
            $params = [];

            if (isset($this->routes[$path])) {

                $found = $this->render_route($path, $params);

            } else {

                foreach ($this->routes as $route => $callback) {

                    $params = [];

                    /* e.g. #\.html$#  */
                    if (\substr($route,0,1)=='#' && \substr($route,-1)=='#'){

                        if (\preg_match($route, $path, $matches)){
                            $params[':captures'] = \array_slice($matches, 1);
                            $found = $this->render_route($route, $params);
                            break;
                        }
                    }

                    /* e.g. /admin/*  */
                    if (\strpos($route, '*') !== false){

                        $pattern = '#^'.\str_replace('\*', '(.*)', \preg_quote($route, '#')).'#';

                        if (\preg_match($pattern, $path, $matches)){

                            $params[':splat'] = \array_slice($matches, 1);
                            $found = $this->render_route($route, $params);
                            break;
                        }
                    }

                    /* e.g. /admin/:id  */
                    if (strpos($route, ':') !== false){

                        $parts_p = \explode('/', $path);
                        $parts_r = \explode('/', $route);

                        if (\count($parts_p) == \count($parts_r)){

                            $matched = true;

                            foreach ($parts_r as $index => $part){
                                if (':' === \substr($part,0,1)) {
                                    $params[\substr($part,1)] = $parts_p[$index];
                                    continue;
                                }

                                if ($parts_p[$index] != $parts_r[$index]) {
                                    $matched = false;
                                    break;
                                }
                            }

                            if ($matched){
                                $found = $this->render_route($route, $params);
                                break;
                            }
                        }
                    }
                }
            }

            return $found;
    }

    /**
    * Render dispatched route
    * @param  [type] $route
    * @param  array  $params
    * @return String
    */
    protected function render_route($route, $params = []) {

        $output = false;

        if (isset($this->routes[$route])) {

            $ret = null;

            if (\is_callable($this->routes[$route])){
                $ret = \call_user_func($this->routes[$route], $params);
            }

            if (!is_null($ret)){
                return $ret;
            }
        }

        return $output;
    }


    /**
    * Invoke Class as controller
    * @param  String $class
    * @param  String $action
    * @param  Array  $params
    * @return Mixed
    */
    public function invoke($class, $action="index", $params=[]) {

        $controller = new $class($this);

        return \method_exists($controller, $action) && \is_callable([$controller, $action])
                ? \call_user_func_array([$controller,$action], $params)
                : false;
    }

    /**
    * Get request variables
    * @param  String $index
    * @param  Mixed $default
    * @param  Array $source
    * @return Mixed
    */
    public function param($index=null, $default = null, $source = null) {
        return isset($this->request) ? $this->request->param($index, $default, $source) : $default;
    }

    /**
    * Request helper function
    * @param  String $type
    * @return Boolean
    */
    public function req_is($type){
        return isset($this->request) ? $this->request->is($type) : false;
    }

    /**
    * Get client ip.
    * @return String
    */
    public function getClientIp(){
        return isset($this->request) ? $this->request->getClientIp() : '';
    }

    /**
    * Get client language
    * @return String
    */
    public function getClientLang($default="en") {
        return isset($this->request) ? $this->request->getClientLang($default) : $default;
    }

    /**
    * Get site url
    * @return String
    */
    public function getSiteUrl($withpath = false) {
        return isset($this->request) ? $this->request->getSiteUrl($withpath) : '';
    }

    /**
    * Create Hash
    * @return String
    */
    public function hash($text, $algo = PASSWORD_BCRYPT) {
        return \password_hash($text, $algo);
    }

    /**
     * RC4 encryption
     * @param  [type]  $data          [description]
     * @param  [type]  $pwd           [description]
     * @param  boolean $base64encoded [description]
     * @return [type]                 [description]
     */
    public function encode($data, $pwd, $base64encoded = false) {

        $key = [''];
        $box = [''];
        $cipher = '';

        $pwd_length = \strlen($pwd);
        $data_length = \strlen($data);

        for ($i = 0; $i < 256; $i++) {
            $key[$i] = \ord($pwd[$i % $pwd_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $data_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $k = $box[(($box[$a] + $box[$j]) % 256)];
            $cipher .= \chr(\ord($data[$i]) ^ $k);
        }
        return $base64encoded ? \base64_encode($cipher):$cipher;
    }

    /**
     * Decode RC4 encrypted text
     * @param  [type] $data [description]
     * @param  [type] $pwd  [description]
     * @return [type]       [description]
     */
    public function decode($data, $pwd) {
        return $this->encode($data, $pwd);
    }

    public function helper($helper) {
        if (isset($this->helpers[$helper]) && !\is_object($this->helpers[$helper])) {
            $this->helpers[$helper] = new $this->helpers[$helper]($this);
        }

        return $this->helpers[$helper];
    }

    public function isAbsolutePath($path) {
        return '/' == $path[0] || '\\' == $path[0] || (3 < \strlen($path) && \ctype_alpha($path[0]) && $path[1] == ':' && ('\\' == $path[2] || '/' == $path[2]));
    }

    public function module($name) {
        return $this->registry['modules']->offsetExists($name) && $this->registry['modules'][$name] ? $this->registry['modules'][$name] : null;
    }

    public function registerModule($name, $dir) {

        $name = \strtolower($name);

        if (!isset($this->registry['modules'][$name])) {

            $module = new Module($this);

            $module->_dir      = $dir;
            $module->_bootfile = "{$dir}/bootstrap.php";

            $this->path($name, $dir);
            $this->registry['modules'][$name] = $module;
            $this->bootModule($module);
        }

        return $this->registry['modules'][$name];
    }

    public function loadModules($dirs, $autoload = true, $prefix = false) {

        $modules  = [];
        $dirs     = (array)$dirs;
        $disabled = $this->registry['modules.disabled'] ?? null;

        foreach ($dirs as &$dir) {

            if (\file_exists($dir)){

                $pfx = \is_bool($prefix) ? \strtolower(basename($dir)) : $prefix;

                // load modules
                foreach (new \DirectoryIterator($dir) as $module) {

                    if ($module->isFile() || $module->isDot()) continue;

                    $name = $prefix ? "{$pfx}-".$module->getBasename() : $module->getBasename();

                    if ($disabled && \in_array($name, $disabled)) continue;

                    $this->registerModule($name, $module->getRealPath());

                    $modules[] = \strtolower($module);
                }

                if ($autoload) $this['autoload']->append($dir);
            }
        }

        return $modules;
    }

    protected function bootModule($module) {

        if (is_file($module->_bootfile)) {
            $app = $this;
            require($module->_bootfile);
        }
    }

    // accces to services
    public function __get($name) {
        return $this[$name];
    }

    // Array Access implementation

    public function offsetSet($key, $value) {
        $this->registry[$key] = $value;
    }

    public function offsetGet($key) {

        $value = $this->retrieve($key, null);

        if (!is_null($value)) {
            return ($value instanceof \Closure) ? $value($this) : $value;
        }

        return $value;
    }

    public function offsetExists($key) {
        return isset($this->registry[$key]);
    }

    public function offsetUnset($key) {
        unset($this->registry[$key]);
    }

    // Invoke call
    public function __invoke($helper) {

        return $this->helper($helper);
    }

    protected function getRequestfromGlobals() {

        return Request::fromGlobalRequest([
            'site_url'   => $this->registry['site_url'],
            'base_url'   => $this->registry['base_url'],
            'base_route' => $this->registry['base_route']
        ]);
    }

} // End App

// Helpers

class AppAware {

    /** @var App */
    public $app;

    public function __construct($app) {
        $this->app = $app;

        $this->initialize();
    }

    public function initialize() {}

    public function __call($name, $arguments) {

        if (\is_callable([$this->app, $name])) {
            return \call_user_func_array([$this->app, $name], $arguments);
        }
        return $this;
    }

    public function __invoke($helper) {

        return $this->app->helper($helper);
    }

    // acccess to services
    public function __get($name) {
        return $this->app[$name];
    }

}

class Module extends AppAware {

    protected $apis = [];

    public function extend($api) {

        foreach ($api as $name => $value) {

            if ($value instanceof \Closure) {
                $value = $value->bindTo($this, $this);
            }

            $this->apis[$name] = $value;
        }
    }

    public function __set($name , $value) {
        $this->extend(array($name => $value));
    }
    public function __get($name) {
        return isset($this->apis[$name]) ? $this->apis[$name] :null;
    }
    public function __isset($name) {
        return isset($this->apis[$name]);
    }
    public function __unset($name) {
        unset($this->apis[$name]);
    }
    public function __call($name, $arguments) {

        if (isset($this->apis[$name]) && \is_callable($this->apis[$name])) {
            return \call_user_func_array($this->apis[$name], $arguments);
        }

        if (isset($this->apis['__call']) && \is_callable($this->apis['__call'])) {
            return \call_user_func_array($this->apis['__call'], [$name, $arguments]);
        }

        return null;
    }
}


class Helper extends AppAware { }


include(__DIR__.'/Helper/Session.php');
include(__DIR__.'/Helper/Cache.php');

// helper functions

function fetch_from_array(&$array, $index = null, $default = null) {

    if (is_null($index)) {

        return $array;

    } elseif (isset($array[$index])) {

        return $array[$index];

    } elseif (\strpos($index, '/')) {

        $keys = \explode('/', $index);

        switch (\count($keys)){

            case 1:
                if (isset($array[$keys[0]])){
                    return $array[$keys[0]];
                }
                break;

            case 2:
                if (isset($array[$keys[0]][$keys[1]])){
                    return $array[$keys[0]][$keys[1]];
                }
                break;

            case 3:
                if (isset($array[$keys[0]][$keys[1]][$keys[2]])){
                    return $array[$keys[0]][$keys[1]][$keys[2]];
                }
                break;

            case 4:
                if (isset($array[$keys[0]][$keys[1]][$keys[2]][$keys[3]])){
                    return $array[$keys[0]][$keys[1]][$keys[2]][$keys[3]];
                }
                break;
        }
    }

    return \is_callable($default) ? \call_user_func($default) : $default;
}
