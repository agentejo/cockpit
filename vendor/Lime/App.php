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


class App implements \ArrayAccess {

    protected static $apps = array();

    protected $registry = array();
    protected $routes   = array();
    protected $paths    = array();
    protected $events   = array();
    protected $blocks   = array();

    protected $exit     = false;

    public $response    = null;

    public $helpers;
    public $layout      = false;

    /* status codes */
    public static $statusCodes = array(
    // Informational 1xx
    100 => 'Continue',
    101 => 'Switching Protocols',
    // Successful 2xx
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    // Redirection 3xx
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    // Client Error 4xx
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Request Range Not Satisfiable',
    417 => 'Expectation Failed',
    // Server Error 5xx
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported'
    );

    /* mime types */
    public static $mimeTypes = array(
        'asc'   => 'text/plain',
        'au'    => 'audio/basic',
        'avi'   => 'video/x-msvideo',
        'bin'   => 'application/octet-stream',
        'class' => 'application/octet-stream',
        'css'   => 'text/css',
        'csv' => 'application/vnd.ms-excel',
        'doc'   => 'application/msword',
        'dll'   => 'application/octet-stream',
        'dvi'   => 'application/x-dvi',
        'exe'   => 'application/octet-stream',
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'json'  => 'application/json',
        'js'    => 'application/x-javascript',
        'txt'   => 'text/plain',
        'bmp'   => 'image/bmp',
        'rss'   => 'application/rss+xml',
        'atom'  => 'application/atom+xml',
        'gif'   => 'image/gif',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'jpe'   => 'image/jpeg',
        'png'   => 'image/png',
        'ico'   => 'image/vnd.microsoft.icon',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mpe'   => 'video/mpeg',
        'qt'    => 'video/quicktime',
        'mov'   => 'video/quicktime',
        'wmv'   => 'video/x-ms-wmv',
        'mp2'   => 'audio/mpeg',
        'mp3'   => 'audio/mpeg',
        'rm'    => 'audio/x-pn-realaudio',
        'ram'   => 'audio/x-pn-realaudio',
        'rpm'   => 'audio/x-pn-realaudio-plugin',
        'ra'    => 'audio/x-realaudio',
        'wav'   => 'audio/x-wav',
        'zip'   => 'application/zip',
        'pdf'   => 'application/pdf',
        'xls'   => 'application/vnd.ms-excel',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc'  => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'spl'   => 'application/x-futuresplash',
        'gtar'  => 'application/x-gtar',
        'gzip'  => 'application/x-gzip',
        'swf'   => 'application/x-shockwave-flash',
        'tar'   => 'application/x-tar',
        'xhtml' => 'application/xhtml+xml',
        'snd'   => 'audio/basic',
        'midi'  => 'audio/midi',
        'mid'   => 'audio/midi',
        'm3u'   => 'audio/x-mpegurl',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'rtf'   => 'text/rtf',
        'wml'   => 'text/vnd.wap.wml',
        'wmls'  => 'text/vnd.wap.wmlscript',
        'xsl'   => 'text/xml',
        'xml'   => 'text/xml'
    );

    /**
    * Constructor
    * @param Array $settings initial registry settings
    */
    public function __construct ($settings = array()) {

        $self = $this;

        $this->registry = array_merge(array(
            'debug'        => true,
            'app.name'     => '',
            'session.name' => null,
            'autoload'     => new \ArrayObject(array()),
            'sec-key'      => 'xxxxx-SiteSecKeyPleaseChangeMe-xxxxx',
            'route'        => isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/",
            'charset'      => 'UTF-8',
            'helpers'      => array(),
            'base_url'     => implode("/", array_slice(explode("/", $_SERVER['SCRIPT_NAME']), 0, -1)),
            'base_route'   => implode("/", array_slice(explode("/", $_SERVER['SCRIPT_NAME']), 0, -1)),
            'docs_root'    => isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT']:''
        ), $settings);

        if(!isset($this["name"])){
            $this["name"] = uniqid();
        }

        if(!isset($this["session.name"])){
            $this["session.name"] = $this["name"];
        }

        self::$apps[$this["name"]] = $this;

        // default helpers
        $this->helpers = new \ArrayObject(array_merge(array("session" => 'Lime\\Session'), $this->registry["helpers"]));

        // register simple autoloader
        spl_autoload_register(function ($class) use($self){

            foreach ($self->retrieve("autoload", array()) as $dir) {

                $class_file = $dir.'/'.str_replace('\\', '/', $class).'.php';

                if(file_exists($class_file)){
                    include_once($class_file);
                    return;
                }
            }
        });


        // check for php://input and merge with $_REQUEST
        if(
            (isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"],'application/json')!==false) ||
            (isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"],'application/json')!==false) // PHP build in Webserver !?
        ) {
            if($json = json_decode(@file_get_contents('php://input'), true)) {
                $_REQUEST = array_merge($_REQUEST, $json);
            }
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
    * @param  Closure $callable
    * @return Object
    */
    public function service($name, $callable) {
        $this[$name] = function ($c) use ($callable) {
            static $object;

            if (null === $object) {
                $object = $callable($c);
            }

            return $object;
        };
    }

    /**
    * stop application (exit)
    */
    public function stop($data = false){
        $this->exit = true;

        if($data!==false) {
            echo $data;
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

        return strpos($path, ':')===false ? $this->registry["base_url"].'/'.ltrim($path, '/') : $this->pathToUrl($path);
    }

    public function base($path) {

        $args = func_get_args();

        echo (count($args)==1) ? $this->baseUrl($args[0]) : $this->baseUrl(call_user_func_array('sprintf', $args));
    }

    /**
    * Returns link based on the route url of the app
    * @param  String $path e.g. /pages/home
    * @return String       Link
    */
    public function routeUrl($path) {

        return $this->registry["base_route"].'/'.ltrim($path, '/');
    }

    public function route() {

        $args = func_get_args();

        echo (count($args)==1) ? $this->routeUrl($args[0]) : $this->routeUrl(call_user_func_array('sprintf', $args));

    }

    /**
    * Redirect to path.
    * @param  String $path Path redirect to.
    * @return void
    */
    public function reroute($path) {

        if (strpos($path,'://') === false) {
            if(substr($path,0,1)!='/'){
                $path = '/'.$path;
            }
            $path = $this->routeUrl($path);
        }

        header('Location: '.$path);
        $this->stop();
    }

    /**
    * Put a value in the Lime registry
    * @param String $key  Key name
    * @param Misc $value  Value
    */
    public function set($key, $value) {

        $keys = explode('/',$key);

        if(count($keys)>5) return false;

        switch(count($keys)){

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

        return true;
    }

    /**
    * Get a value from the Lime registry
    * @param  String $key
    * @param  Misc $default
    * @return Misc
    */
    public function retrieve($key, $default=null) {
        return fetch_from_array($this->registry, $key, $default);
    }


    /**
    * Path helper method
    * @return Misc
    */
    public function path(){

        $args = func_get_args();

        switch(count($args)){
        case 1:
            $file  = $args[0];

            if($this->isAbsolutePath($file) && file_exists($file)) {
                return $file;
            }

            $parts = explode(':', $file, 2);

            if(count($parts)==2){
                if(!isset($this->paths[$parts[0]])) return false;

                foreach($this->paths[$parts[0]] as &$path){
                    if(file_exists($path.$parts[1])){
                        return $path.$parts[1];
                    }
                }
            }

            return false;
        case 2:
            if(!isset($this->paths[$args[0]])) {
                $this->paths[$args[0]] = array();
            }
            array_unshift($this->paths[$args[0]], rtrim(str_replace(DIRECTORY_SEPARATOR,'/',$args[1]), '/').'/');
            break;
        }
    }

    /**
     * [pathToUrl description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function pathToUrl($path) {

        $url = false;

        if ($file = $this->path($path)) {

            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $root = str_replace(DIRECTORY_SEPARATOR, '/', $this['docs_root']);

            $url = '/'.ltrim(str_replace($root, '', $file), '/');
        }

        return $url;
    }

    /**
    * Cache helper method
    * @return Misc
    */
    public function cache(){

        $args = func_get_args();

        switch(count($args)){
        case 1:

            return $this("cache")->read($args[0]);
        case 2:
            return $this("cache")->write($args[0], $args[1]);
            break;
        }
    }

    /**
    * [onlyif description]
    * @param  [type] $condition [description]
    * @return [type]            [description]
    */
    public function onlyif($condition) {

        static $dummy;

        if(!$dummy) $dummy = new DummyObject();
        if(is_callable($condition)) $condition = call_user_func($condition);

        return $condition ? $this:$dummy;
    }

    /**
    * Bind an event to closure
    * @param  String  $event
    * @param  Closure $callback
    * @param  Integer $priority
    * @return void
    */
    public function on($event,$callback, $priority = 0){

        if(!isset($this->events[$event])) $this->events[$event] = array();

        $this->events[$event][] = array("fn" => $callback, "prio" => $priority);
    }

    /**
    * Trigger event.
    * @param  String $event
    * @param  Array  $params
    * @return Boolean
    */
    public function trigger($event,$params=array()){

        if(!isset($this->events[$event])){
            return $this;
        }

        if(!count($this->events[$event])){
            return $this;
        }

        $queue = new \SplPriorityQueue();

        foreach($this->events[$event] as $index => $action){
            $queue->insert($index, $action["prio"]);
        }

        $queue->top();

        while($queue->valid()){
            $index = $queue->current();
            if(is_callable($this->events[$event][$index]["fn"])){
                if(call_user_func_array($this->events[$event][$index]["fn"], $params) === false) {
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
    public function render($____template, $_____slots = array()) {

        $_____slots["app"] = $this;
        $____layout        = $this->layout;

        if (strpos($____template, ' with ') !== false ) {
            list($____template, $____layout) = explode(' with ', $____template, 2);
        }

        if (strpos($____template, ':') !== false && $____file = $this->path($____template)) {
            $____template = $____file;
        }

        $extend = function($from) use(&$____layout) {
            $____layout = $from;
        };

        extract((array)$_____slots);

        ob_start();
        include $____template;
        $output = ob_get_clean();

        if ($____layout) {

            if (strpos($____layout, ':') !== false && $____file = $this->path($____layout)) {
                $____layout = $____file;
            }

            $content_for_layout = $output;

            ob_start();
            include $____layout;
            $output = ob_get_clean();

        }

        return $output;
    }

    /**
    * Start block
    * @param  String $name
    * @return Null
    */
    public function start($name) {

        if(!isset($this->blocks[$name])){
            $this->blocks[$name] = array();
        }

        ob_start();
    }

    /**
    * End block
    * @param  String $name
    * @return Null
    */
    public function end($name) {

        $out = ob_get_clean();

        if(isset($this->blocks[$name])){
            $this->blocks[$name][] = $out;
        }

    }

    /**
    * Get block content
    * @param  String $name
    * @param  array  $options
    * @return String
    */
    public function block($name, $options=array()) {

        if(!isset($this->blocks[$name])) return null;

        $options = array_merge(array(
            "print" => true
        ), $options);

        $block = implode("\n", $this->blocks[$name]);

        if($options["print"]){
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

        if(is_null($charset)){
            $charset = $this["charset"];
        }

        return htmlspecialchars($string, ENT_QUOTES, $charset);
    }

    /**
    * Get request variables
    * @param  String $index
    * @param  Misc $default
    * @param  Array $source
    * @return Misc
    */
    public function param($index=null, $default = null, $source = null) {

        $src = $source ? $source : $_REQUEST;
        return fetch_from_array($src, $index, $default);
    }

    /**
    * Get style inc. markup
    * @param  String $href
    * @return String
    */
    public function style($href, $version=false) {

        $list = array();

        foreach((array)$href as $style) {

            $ispath = strpos($style, ':') !== false && !preg_match('#^(|http\:|https\:)//#', $style);
            $list[] = '<link href="'.($ispath ? $this->pathToUrl($style):$style).($version ? "?ver={$version}":"").'" type="text/css" rel="stylesheet">';
        }

        return implode("\n", $list);
    }

    /**
    * Get script inc. markup
    * @param  String $src
    * @return String
    */
    public function script($src, $version=false){

        $list = array();

        foreach((array)$src as $script) {
            $ispath = strpos($script, ':') !== false && !preg_match('#^(|http\:|https\:)//#', $script);
            $list[] = '<script src="'.($ispath ? $this->pathToUrl($script):$script).($version ? "?ver={$version}":"").'" type="text/javascript"></script>';
        }

        return implode("\n", $list);
    }

    public function assets($src, $version=false){

        $list = array();

        foreach((array)$src as $script) {

            if(@substr($script, -3) == ".js") {
                $list[] = $this->script($script, $version);
            }

            if(@substr($script, -4) == ".css") {
                $list[] = $this->style($script, $version);
            }
        }

        return implode("\n", $list);
    }

    /**
    * Bind GET request to route
    * @param  String  $path
    * @param  Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function get($path, $callback, $condition = true){
        if(!$this->req_is("get")) return;
        $this->bind($path, $callback, $condition);
    }

    /**
    * Bind POST request to route
    * @param  String  $path
    * @param  Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function post($path, $callback, $condition = true){
        if(!$this->req_is("post")) return;
        $this->bind($path, $callback, $condition);
    }

    /**
    * Bind Class to routes
    * @param  String $class
    * @return void
    */
    public function bindClass($class, $alias = false) {

        $self  = $this;
        $clean = $alias ? $alias : trim(strtolower(str_replace("\\", "/", $class)), "\\");

        $this->bind('/'.$clean.'/*', function() use($self, $class, $clean) {

            $parts      = explode('/', trim(str_replace($clean,"",$self["route"]),'/'));
            $action     = isset($parts[0]) ? $parts[0]:"index";
            $params     = count($parts)>1 ? array_slice($parts, 1):array();

            return $self->invoke($class,$action, $params);
        });

        $this->bind('/'.$clean, function() use($self, $class) {

            return $self->invoke($class,'index', array());
        });
    }

    /**
    * Bind namespace to routes
    * @param  String $namespace
    * @return void
    */
    public function bindNamespace($namespace, $alias) {

        $self  = $this;
        $clean = $alias ? $alias : trim(strtolower(str_replace("\\", "/", $namespace)), "\\");

        $this->bind('/'.$clean.'/*', function() use($self, $namespace, $clean) {

            $parts      = explode('/', trim(str_replace($clean,"",$self["route"]),'/'));
            $class      = $namespace.'\\'.$parts[0];
            $action     = isset($parts[1]) ? $parts[1]:"index";
            $params     = count($parts)>2 ? array_slice($parts, 2):array();

            return $self->invoke($class,$action, $params);
        });

        $this->bind('/'.strtolower($namespace), function() use($self, $namespace) {

            $class = $namespace."\\".array_pop(explode('\\', $namespace));

            return $self->invoke($class,'index', array());
        });
    }

    /**
    * Bind request to route
    * @param  String  $path
    * @param  Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function bind($path, $callback, $condition = true) {

        if (!$condition) return;

        if (!isset($this->routes[$path])) {
            $this->routes[$path] = array();
        }

        $this->routes[$path] = $callback;
    }

    /**
    * Run Application
    * @param  String $route Route to parse
    * @return void
    */
    public function run() {

        $self = $this;

        register_shutdown_function(function() use($self){

            if($self->isExit()){
                return;
            }

            $error = error_get_last();

            if ($error && in_array($error['type'], array(E_ERROR,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR))){

                ob_end_clean();

                $self->response->status = "500";
                $self->response->body   = $self["debug"] ? json_encode($error):'Internal Error.';

            } elseif (!$self->response->body) {
                $self->response->status = "404";
                $self->response->body   = "Path not found.";
            }

            $self->trigger("after");

            echo $self->response->flush();

            $self->trigger("shutdown");
        });

        $this->response = new Response();

        $this->trigger("before");

        $this->response->body = $this->dispatch($this["route"]);

        if ($this->response->gzip && !ob_start("ob_gzhandler")) ob_start();
    }

    /**
    * Dispatch route
    * @param  String $path
    * @return Misc
    */
    public function dispatch($path) {

            $found  = false;
            $params = array();

            if (isset($this->routes[$path])) {

                $found = $this->render_route($path, $params);

            } else {

                foreach ($this->routes as $route => $callback) {

                    $params = array();

                    /* e.g. #\.html$#  */
                    if(substr($route,0,1)=='#' && substr($route,-1)=='#'){

                        if(preg_match($route,$path, $matches)){
                            $params[':captures'] = array_slice($matches, 1);
                            $found = $this->render_route($route, $params);
                            break;
                        }
                    }

                    /* e.g. /admin/*  */
                    if(strpos($route, '*') !== false){

                        $pattern = '#^'.str_replace('\*', '(.*?)', preg_quote($route, '#')).'#';

                        if(preg_match($pattern, $path, $matches)){

                            $params[':splat'] = array_slice($matches, 1);
                            $found = $this->render_route($route, $params);
                            break;
                        }
                    }

                    /* e.g. /admin/:id  */
                    if(strpos($route, ':') !== false){

                        $parts_p = explode('/', $path);
                        $parts_r = explode('/', $route);

                        if(count($parts_p) == count($parts_r)){

                            $matched = true;

                            foreach($parts_r as $index => $part){
                                if(':' === substr($part,0,1)) {
                                    $params[substr($part,1)] = $parts_p[$index];
                                    continue;
                                }

                                if($parts_p[$index] != $parts_r[$index]) {
                                    $matched = false;
                                    break;
                                }
                            }

                            if($matched){
                                $found = $this->render_route($route, $params);;
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
    protected function render_route($route, $params = array()) {

        $output = false;

        if(isset($this->routes[$route])) {

            if(is_callable($this->routes[$route])){
                $ret = call_user_func($this->routes[$route], $params);
            }

            if( !is_null($ret) ){
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
    * @return Misc
    */
    public function invoke($class, $action="index", $params=array()) {

        $controller = new $class($this);

        return method_exists($controller, $action) ? call_user_func_array(array($controller,$action), $params):false;
    }

    /**
    * Request helper function
    * @param  String $type
    * @return Boolean
    */
    public function req_is($type){

        switch(strtolower($type)){
            case 'ajax':
            return (
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))       ||
                (isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"],'application/json')!==false)           ||
                (isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"],'application/json')!==false)
            );
            break;

            case 'mobile':

            $mobileDevices = array(
                "midp","240x320","blackberry","netfront","nokia","panasonic","portalmmm","sharp","sie-","sonyericsson",
                "symbian","windows ce","benq","mda","mot-","opera mini","philips","pocket pc","sagem","samsung",
                "sda","sgh-","vodafone","xda","iphone", "ipod","android"
            );

            return preg_match('/(' . implode('|', $mobileDevices). ')/i',strtolower($_SERVER['HTTP_USER_AGENT']));
            break;

            case 'post':
            return (strtolower($_SERVER['REQUEST_METHOD']) == 'post');
            break;

            case 'get':
            return (strtolower($_SERVER['REQUEST_METHOD']) == 'get');
            break;

            case 'put':
            return (strtolower($_SERVER['REQUEST_METHOD']) == 'put');
            break;

            case 'delete':
            return (strtolower($_SERVER['REQUEST_METHOD']) == 'delete');
            break;

            case 'ssl':
            return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            break;
        }

        return false;
    }

    /**
    * Get client ip.
    * @return String
    */
    public function getClientIp(){

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])){
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR'])){
            // The remote IP address
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
    * Get client language
    * @return String
    */
    public function getClientLang($default="en") {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return $default;
        }
        return strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    }

    /**
    * Get site url
    * @return String
    */
    public function getSiteUrl() {
        $url = ($this->req_is("ssl") ? 'https':'http')."://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        } else {
            $url .= $_SERVER["SERVER_NAME"];
        }

        $url .= implode("/", array_slice(explode("/", $_SERVER['SCRIPT_NAME']), 0, -1));

        return rtrim($url,'/');
    }

    /**
    * Create Hash
    * @return String
    */
    public function hash($text) {

        return md5($this->encode($text, $this["sec-key"], true));
    }

    /**
     * RC4 encryption
     * @param  [type]  $data          [description]
     * @param  [type]  $pwd           [description]
     * @param  boolean $base64encoded [description]
     * @return [type]                 [description]
     */
    public function encode($data, $pwd, $base64encoded = false) {

        $key = array('');
        $box = array('');
        $cipher = '';

        $pwd_length = strlen($pwd);
        $data_length = strlen($data);

        for ($i = 0; $i < 256; $i++) {
            $key[$i] = ord($pwd[$i % $pwd_length]);
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
            $cipher .= chr(ord($data[$i]) ^ $k);
        }
        return $base64encoded ? base64_encode($cipher):$cipher;
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
        if (isset($this->helpers[$helper]) && !is_object($this->helpers[$helper])) {
            $this->helpers[$helper] = new $this->helpers[$helper]($this);
        }

        return $this->helpers[$helper];
    }

    public function isAbsolutePath($file) {

        if ($file[0] == '/' || $file[0] == '\\' || (strlen($file) > 3 && ctype_alpha($file[0]) && $file[1] == ':' && ($file[2] == '\\' || $file[2] == '/')) || null !== parse_url($file, PHP_URL_SCHEME)) {
            return true;
        }

        return false;
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

        $value = $this->retrieve($key, 'key-not-found');

        if ($value!=='key-not-found') {
            return $value instanceof \Closure ? $value($this) : $value;
        }

        throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $key));
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
} // End site


class DummyObject {
    public function __set($name , $value) { }
    public function __get($name) { }
    public function __isset($name) { }
    public function __unset($name) { }
    public function __call($name, $arguments) { return $this; }
    public static function __callStatic($name, $arguments) { }
}

class Response {
    public $body    = "";
    public $status  = 200;
    public $mime    = "html";
    public $gzip    = false;
    public $nocache = false;
    public $etag    = false;
    public $headers = array();

    public function __construct() {

    }

    public function flush() {

        if (!headers_sent($filename, $linenum)) {

            if($this->nocache){
                header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                header('Pragma: no-cache');
            }

            if($this->etag){
                header('ETag: "'.md5($this->body).'"');
            }

            header('HTTP/1.0 '.$this->status.' '.App::$statusCodes[$this->status]);
            header('Content-type: '.App::$mimeTypes[$this->mime]);

            foreach ($this->headers as $h) {
                header($h);
            }

            echo is_array($this->body) ? json_encode($this->body) : $this->body;
        }
    }
}


// Helpers

class AppAware {

    public $app;

    public function __construct($app) {
        $this->app = $app;

        $this->initialize();
    }

    public function initialize() {}

    public function __call($name, $arguments) {

        if (is_callable(array($this->app, $name))) {
            return call_user_func_array(array($this->app, $name), $arguments);
        }
        return $this;
    }

    public function __invoke($helper) {

        $app = $this->app;

        return $app($helper);
    }

}


class Helper extends AppAware { }


class Session extends Helper {

    protected $initialized = false;
    public $name;

    public function init($sessionname=null){

        if($this->initialized) return;

        if(!strlen(session_id())) {
            $this->name = $sessionname ? $sessionname : $this->app["session.name"];

            session_name($this->name);
            session_start();
        } else {
            $this->name = session_name();
        }

        $this->initialized = true;
    }

    public function write($key, $value){
        $_SESSION[$key] = $value;
    }

    public function read($key, $default=null){
        return fetch_from_array($_SESSION, $key, $default);
    }

    public function delete($key){
        unset($_SESSION[$key]);
    }

    public function destroy(){
        session_destroy();
    }
}



// helper function
function fetch_from_array(&$array, $index=null, $default = null) {

    if (is_null($index)) {

    return $array;

    } elseif(isset($array[$index])) {

    return $array[$index];

    } elseif(strpos($index, '/')){

    $keys = explode('/', $index);

    switch(count($keys)){

        case 1:
            if(isset($array[$keys[0]])){
                return $array[$keys[0]];
            }
            break;

        case 2:
            if(isset($array[$keys[0]][$keys[1]])){
                return $array[$keys[0]][$keys[1]];
            }
            break;

        case 3:
            if(isset($array[$keys[0]][$keys[1]][$keys[2]])){
                return $array[$keys[0]][$keys[1]][$keys[2]];
            }
            break;

        case 4:
            if(isset($array[$keys[0]][$keys[1]][$keys[2]][$keys[3]])){
                return $array[$keys[0]][$keys[1]][$keys[2]][$keys[3]];
            }
            break;
    }
    }

    return $default;
}
