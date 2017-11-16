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

    protected static $apps = [];

    protected $registry = [];
    protected $routes   = [];
    protected $paths    = [];
    protected $events   = [];
    protected $blocks   = [];

    protected $exit     = false;

    /** @var Response|null  */
    public $response    = null;

    public $helpers;
    public $layout      = false;

    /* global view variables */
    public $viewvars    = [];

    /* status codes */
    public static $statusCodes = [
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
    ];

    /* mime types */
    public static $mimeTypes = [
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
    ];

    /**
    * Constructor
    * @param Array $settings initial registry settings
    */
    public function __construct ($settings = []) {

        $self = $this;

        $this->registry = array_merge([
            'debug'        => true,
            'app.name'     => 'LimeApp',
            'session.name' => 'limeappsession',
            'autoload'     => new \ArrayObject([]),
            'sec-key'      => 'xxxxx-SiteSecKeyPleaseChangeMe-xxxxx',
            'route'        => isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/",
            'charset'      => 'UTF-8',
            'helpers'      => [],
            'base_url'     => implode("/", array_slice(explode("/", $_SERVER['SCRIPT_NAME']), 0, -1)),
            'base_route'   => implode("/", array_slice(explode("/", $_SERVER['SCRIPT_NAME']), 0, -1)),
            'base_host'    => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname('n'),
            'base_port'    => isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80,
            'docs_root'    => null,
            'site_url'     => null
        ], $settings);

        // app modules container
        $this->registry["modules"] = new \ArrayObject([]);

        if (!isset($this["site_url"])) {
            $this["site_url"] = $this->getSiteUrl(false);
        }

        if (!isset($this["docs_root"])) {
            $this["docs_root"] = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : dirname($_SERVER['SCRIPT_FILENAME']));
        }

        // make sure base + route url doesn't end with a slash;
        $this->registry["base_url"]   = rtrim($this->registry["base_url"], '/');
        $this->registry["base_route"] = rtrim($this->registry["base_route"], '/');

        // default global viewvars
        $this->viewvars["app"]        = $this;
        $this->viewvars["base_url"]   = $this["base_url"];
        $this->viewvars["base_route"] = $this["base_route"];
        $this->viewvars["docs_root"]  = $this["docs_root"];

        self::$apps[$this["app.name"]] = $this;

        // default helpers
        $this->helpers = new \ArrayObject(array_merge(['session' => 'Lime\\Session', 'cache' => 'Lime\\Cache'], $this->registry["helpers"]));

        // register simple autoloader
        spl_autoload_register(function ($class) use($self){

            foreach ($self->retrieve("autoload", []) as $dir) {

                $class_file = $dir.'/'.str_replace('\\', '/', $class).'.php';

                if (file_exists($class_file)){
                    include_once($class_file);
                    return;
                }
            }
        });


        // check for php://input and merge with $_REQUEST
        if (
            (isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"],'application/json')!==false) ||
            (isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"],'application/json')!==false) // PHP build in Webserver !?
        ) {
            if ($json = json_decode(@file_get_contents('php://input'), true)) {
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
    * @param  \Closure $callable
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
    public function stop($data = false, $status = null){

        $this->exit = true;

        if (is_string($data) && $data) {
           $this->response->body = $data;
        }

        if (is_numeric($data) && $data) {

            $this->response->status = $data;

            if (isset(self::$statusCodes[$data])) {

                if ($this->response->mime == 'json') {
                    $this->response->body = json_encode(["error" => self::$statusCodes[$data]]);
                } else {
                    $this->response->body = self::$statusCodes[$data];
                }
            }
        }

        if ($status) {
           $this->response->status = $status;
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

        if (strpos($path, ':')===false) {

            /*
            if ($this->registry['base_port'] != '80') {
                $url .= $this->registry['site_url'];
            }
            */

            $url .= $this->registry["base_url"].'/'.ltrim($path, '/');

        } else {
            $url = $this->pathToUrl($path);
        }

        return $url;
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

        $url = '';

        /*
        if ($this->registry['base_port'] != '80') {
            $url .= $this->registry['site_url'];
        }
        */

        $url .= $this->registry["base_route"];

        return $url.'/'.ltrim($path, '/');
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
            if (substr($path,0,1)!='/'){
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
    * @param Mixed $value  Value
    */
    public function set($key, $value) {

        $keys = explode('/',$key);

        if (count($keys)>5) return false;

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

        $args = func_get_args();

        switch(count($args)){

            case 1:

                $file  = $args[0];

                if ($this->isAbsolutePath($file) && file_exists($file)) {
                    return $file;
                }

                $parts = explode(':', $file, 2);

                if (count($parts)==2){
                    if (!isset($this->paths[$parts[0]])) return false;

                    foreach($this->paths[$parts[0]] as &$path){
                        if (file_exists($path.$parts[1])){
                            return $path.$parts[1];
                        }
                    }
                }

                return false;

            case 2:

                if (!isset($this->paths[$args[0]])) {
                    $this->paths[$args[0]] = [];
                }
                array_unshift($this->paths[$args[0]], rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $args[1]), '/').'/');

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

        return isset($this->paths[$namespace]) ? $this->paths[$namespace] : [];
    }

    /**
     * @param $path
     * @return bool|string
     */
    public function pathToUrl($path) {

        $url = false;

        if ($file = $this->path($path)) {

            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $root = str_replace(DIRECTORY_SEPARATOR, '/', $this['docs_root']);

            $url = '/'.ltrim(str_replace($root, '', $file), '/');
            $url = implode('/', array_map('rawurlencode', explode('/', $url)));
        }

        /*
        if ($this->registry['base_port'] != "80") {
            $url = $this->registry['site_url'].$url;
        }
        */

        return $url;
    }

    /**
    * Cache helper method
    * @return Mixed
    */
    public function cache(){

        $args = func_get_args();

        switch(count($args)){
        case 1:

            return $this("cache")->read($args[0]);
        case 2:
            return $this("cache")->write($args[0], $args[1]);
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

        if (!isset($this->events[$event])) $this->events[$event] = [];

        // make $this available in closures
        if (is_object($callback) && $callback instanceof \Closure) {
            $callback = $callback->bindTo($this, $this);
        }

        $this->events[$event][] = ["fn" => $callback, "prio" => $priority];

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

        if (!count($this->events[$event])){
            return $this;
        }

        $queue = new \SplPriorityQueue();

        foreach($this->events[$event] as $index => $action){
            $queue->insert($index, $action["prio"]);
        }

        $queue->top();

        while($queue->valid()){
            $index = $queue->current();
            if (is_callable($this->events[$event][$index]["fn"])){
                if (call_user_func_array($this->events[$event][$index]["fn"], $params) === false) {
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

        $_____slots = array_merge($this->viewvars, $_____slots);
        $____layout = $this->layout;

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

        if (!isset($this->blocks[$name])){
            $this->blocks[$name] = [];
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

        $options = array_merge([
            "print" => true
        ], $options);

        $block = implode("\n", $this->blocks[$name]);

        if ($options["print"]){
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

        if (is_null($charset)){
            $charset = $this["charset"];
        }

        return htmlspecialchars($string, ENT_QUOTES, $charset);
    }

    /**
    * Get request variables
    * @param  String $index
    * @param  Mixed $default
    * @param  Array $source
    * @return Mixed
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

        $list = [];

        foreach((array)$href as $style) {

            $type = 'text/css';
            $rel  = 'stylesheet';
            $src  = $style;

            if (is_array($style)) {
                extract($style);
            }

            $ispath = strpos($src, ':') !== false && !preg_match('#^(|http\:|https\:)//#', $src);
            $list[] = '<link href="'.($ispath ? $this->pathToUrl($src):$src).($version ? "?ver={$version}":"").'" type="'.$type.'" rel="'.$rel.'">';
        }

        return implode("\n", $list);
    }

    /**
    * Get script inc. markup
    * @param  String $src
    * @return String
    */
    public function script($src, $version=false){

        $list = [];

        foreach((array)$src as $script) {

            $type = 'text/javascript';
            $src  = $script;
            $load = '';

            if (is_array($script)) {
                extract($script);
            }

            $ispath = strpos($src, ':') !== false && !preg_match('#^(|http\:|https\:)//#', $src);
            $list[] = '<script src="'.($ispath ? $this->pathToUrl($src):$src).($version ? "?ver={$version}":"").'" type="'.$type.'" '.$load.'></script>';
        }

        return implode("\n", $list);
    }

    public function assets($src, $version=false){

        $list = [];

        foreach((array)$src as $asset) {

            $src = $asset;

            if (is_array($asset)) {
                extract($asset);
            }

            if (@substr($src, -3) == ".js") {
                $list[] = $this->script($asset, $version);
            }

            if (@substr($src, -4) == ".css") {
                $list[] = $this->style($asset, $version);
            }
        }

        return implode("\n", $list);
    }

    /**
    * Bind GET request to route
    * @param  String  $path
    * @param  \Closure  $callback
    * @param  Boolean $condition
    * @return void
    */
    public function get($path, $callback, $condition = true){
        if (!$this->req_is("get")) return;
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
        if (!$this->req_is("post")) return;
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

            $parts      = explode('/', trim(preg_replace("#$clean#","",$self["route"],1),'/'));
            $action     = isset($parts[0]) ? $parts[0]:"index";
            $params     = count($parts)>1 ? array_slice($parts, 1):[];

            return $self->invoke($class,$action, $params);
        });

        $this->bind('/'.$clean, function() use($self, $class) {

            return $self->invoke($class,'index', []);
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

            $parts      = explode('/', trim(preg_replace("#$clean#","",$self["route"],1),'/'));
            $class      = $namespace.'\\'.$parts[0];
            $action     = isset($parts[1]) ? $parts[1]:"index";
            $params     = count($parts)>2 ? array_slice($parts, 2):[];

            return $self->invoke($class,$action, $params);
        });

        $this->bind('/'.strtolower($namespace), function() use($self, $namespace) {

            $class = $namespace."\\".array_pop(explode('\\', $namespace));

            return $self->invoke($class,'index', []);
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
        if (is_object($callback) && $callback instanceof \Closure) {
            $callback = $callback->bindTo($this, $this);
        }

        // autou-register for /route/* also /route
        if (substr($path, -2) == '/*' && !isset($this->routes[substr($path, 0, -2)])) {
            $this->bind(substr($path, 0, -2), $callback, $condition);
        }

        $this->routes[$path] = $callback;
    }

    /**
    * Run Application
    * @param  String $route Route to parse
    * @return void
    */
    public function run($route = null) {

        $self = $this;

        if ($route) {
            $this["route"] = $route;
        }

        register_shutdown_function(function() use($self){

            if ($self->isExit()){
                return;
            }

            $error = error_get_last();

            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])){

                ob_end_clean();

                $self->response->status = "500";
                $self->response->body   = $self["debug"] ? json_encode($error, JSON_PRETTY_PRINT):'Internal Error.';

            } elseif (!$self->response->body && !is_string($self->response->body) && !is_array($self->response->body)) {
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
                    if (substr($route,0,1)=='#' && substr($route,-1)=='#'){

                        if (preg_match($route, $path, $matches)){
                            $params[':captures'] = array_slice($matches, 1);
                            $found = $this->render_route($route, $params);
                            break;
                        }
                    }

                    /* e.g. /admin/*  */
                    if (strpos($route, '*') !== false){

                        $pattern = '#^'.str_replace('\*', '(.*)', preg_quote($route, '#')).'#';

                        if (preg_match($pattern, $path, $matches)){

                            $params[':splat'] = array_slice($matches, 1);
                            $found = $this->render_route($route, $params);
                            break;
                        }
                    }

                    /* e.g. /admin/:id  */
                    if (strpos($route, ':') !== false){

                        $parts_p = explode('/', $path);
                        $parts_r = explode('/', $route);

                        if (count($parts_p) == count($parts_r)){

                            $matched = true;

                            foreach($parts_r as $index => $part){
                                if (':' === substr($part,0,1)) {
                                    $params[substr($part,1)] = $parts_p[$index];
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

            if (is_callable($this->routes[$route])){
                $ret = call_user_func($this->routes[$route], $params);
            }

            if ( !is_null($ret) ){
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

        return method_exists($controller, $action) ? call_user_func_array([$controller,$action], $params):false;
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

            $mobileDevices = [
                "midp","240x320","blackberry","netfront","nokia","panasonic","portalmmm","sharp","sie-","sonyericsson",
                "symbian","windows ce","benq","mda","mot-","opera mini","philips","pocket pc","sagem","samsung",
                "sda","sgh-","vodafone","xda","iphone", "ipod","android"
            ];

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

        return null;
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
    public function getSiteUrl($withpath = false) {

        $url = ($this->req_is('ssl') ? 'https':'http').'://';

        if (!in_array($this->registry['base_port'], ['80', '443'])) {
            $url .= $this->registry['base_host'].":".$this->registry['base_port'];
        } else {
            $url .= $this->registry['base_host'];
        }

        if ($withpath) {
            $url .= implode("/", array_slice(explode("/", $_SERVER['SCRIPT_NAME']), 0, -1));
        }

        return rtrim($url, '/');
    }

    /**
    * Create Hash
    * @return String
    */
    public function hash($text, $algo = PASSWORD_BCRYPT) {
        return password_hash($text, $algo);
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

    public function isAbsolutePath($path) {
        return '/' == $path[0] || '\\' == $path[0] || (3 < strlen($path) && ctype_alpha($path[0]) && $path[1] == ':' && ('\\' == $path[2] || '/' == $path[2]));
    }

    public function module($name) {
        return $this->registry["modules"]->offsetExists($name) && $this->registry["modules"][$name] ? $this->registry["modules"][$name] : null;
    }

    public function registerModule($name, $dir) {

        $name = strtolower($name);

        if (!isset($this->registry["modules"][$name])) {

            $module = new Module($this);

            $module->_dir      = $dir;
            $module->_bootfile = "{$dir}/bootstrap.php";

            $this->path($name, $dir);

            $this->registry["modules"][$name] = $module;

            $this->bootModule($module);
        }

        return $this->registry["modules"][$name];
    }

    public function loadModules($dirs, $autoload = true, $prefix = false) {

        $modules = [];
        $dirs    = (array)$dirs;

        foreach ($dirs as &$dir) {

            if (file_exists($dir)){

                $pfx = is_bool($prefix) ? strtolower(basename($dir)) : $prefix;

                // load modules
                foreach (new \DirectoryIterator($dir) as $module) {

                    if ($module->isFile() || $module->isDot()) continue;

                    $name = $prefix ? "{$pfx}-".$module->getBasename() : $module->getBasename();

                    $this->registerModule($name, $module->getRealPath());

                    $modules[] = strtolower($module);
                }

                if ($autoload) $this["autoload"]->append($dir);

            }
        }

        return $modules;
    }

    protected function bootModule($module) {

        $app = $this;

        require($module->_bootfile);
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
} // End site


class Response {
    public $body    = "";
    public $status  = 200;
    public $mime    = "html";
    public $gzip    = false;
    public $nocache = false;
    public $etag    = false;
    public $headers = [];

    public function __construct() {

    }

    public function flush() {

        if (!headers_sent($filename, $linenum)) {

            if ($this->nocache){
                header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                header('Pragma: no-cache');
            }

            if ($this->etag){
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

    /** @var App */
    public $app;

    public function __construct($app) {
        $this->app = $app;

        $this->initialize();
    }

    public function initialize() {}

    public function __call($name, $arguments) {

        if (is_callable([$this->app, $name])) {
            return call_user_func_array([$this->app, $name], $arguments);
        }
        return $this;
    }

    public function __invoke($helper) {

        return $this->app->helper($helper);
    }

    // accces to services
    public function __get($name) {
        return $this->app[$name];
    }

}

class Module extends AppAware {

    protected $apis = array();

    public function extend($api) {

        foreach($api as $name => $value) {

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

        if(isset($this->apis[$name]) && is_callable($this->apis[$name])) {
            return call_user_func_array($this->apis[$name], $arguments);
        }

        if(isset($this->apis['__call']) && is_callable($this->apis['__call'])) {
            return call_user_func_array($this->apis['__call'], [$name, $arguments]);
        }

        return null;
    }
}


class Helper extends AppAware { }


class Session extends Helper {

    protected $initialized = false;
    public $name;

    public function init($sessionname=null){

        if ($this->initialized) return;

        if (!strlen(session_id())) {
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

class Cache extends Helper {

    public $prefix = null;
    protected $cachePath = null;


    public function initialize(){
        $this->cachePath = rtrim(sys_get_temp_dir(),"/\\")."/";
        $this->prefix    = $this->app['app.name'];
    }

    public function setCachePath($path){
        if ($path) {
            $this->cachePath = rtrim($this->app->path($path), "/\\")."/";
        }
    }

    public function getCachePath(){

        return $this->cachePath;
    }

    public function write($key, $value, $duration = -1){

        $expire = ($duration==-1) ? -1:(time() + (is_string($duration) ? strtotime($duration):$duration));

        $safe_var = [
            'expire' => $expire,
            'value' => serialize($value)
        ];

        file_put_contents($this->cachePath.md5($this->prefix.'-'.$key).".cache" , serialize($safe_var));
    }

    public function read($key, $default=null){
        $var = @file_get_contents($this->cachePath.md5($this->prefix.'-'.$key).".cache");

        if ($var==='') {
            return $default;
        } else {

            $time = time();
            $var  = unserialize($var);

            if (($var['expire'] < $time) && $var['expire']!=-1) {
                $this->delete($key);
                return is_callable($default) ? call_user_func($default):$default;
            }

            return unserialize($var['value']);
        }
    }

    public function delete($key){

        $file = $this->cachePath.md5($this->prefix.'-'.$key).".cache";

        if (file_exists($file)) {
            @unlink($file);
        }

    }

    public function clear(){

        $iterator = new \RecursiveDirectoryIterator($this->cachePath);

        foreach($iterator as $file) {
            if ($file->isFile() && substr($file, -6)==".cache") {
                @unlink($this->cachePath.$file->getFilename());
            }
        }
    }
}

// helper functions

function fetch_from_array(&$array, $index=null, $default = null) {

    if (is_null($index)) {

        return $array;

    } elseif (isset($array[$index])) {

        return $array[$index];

    } elseif (strpos($index, '/')) {

        $keys = explode('/', $index);

        switch(count($keys)){

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

    return is_callable($default) ? call_user_func($default) : $default;
}
