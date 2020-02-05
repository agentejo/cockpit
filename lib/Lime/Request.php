<?php

namespace Lime;

class Request {

    public $request = [];
    public $post = [];
    public $query = [];
    public $files = [];
    public $cookies = [];
    public $headers = [];
    
    public $server = [];
    public $body = [];
    
    public $site_url = '';
    public $base_url = '';
    public $base_route = '';
    public $route = '/';

    public static function fromGlobalRequest($config = []) {

        $config = array_merge([
            'site_url'   => '',
            'base_url'   => '/',
            'base_route' => '',
            'route' => '/',
            'request' => $_REQUEST,
            'post' => $_POST,
            'cookies' => $_COOKIE,
            'query' => $_GET,
            'files' => $_FILES,
            'server' => $_SERVER,
            'headers' => function_exists('getallheaders') ? \getallheaders() : self::getAllHeaders($_SERVER)
        ], $config);

        // check for php://input and merge with $_REQUEST
        if (
            (isset($_SERVER['CONTENT_TYPE']) && \stripos($_SERVER['CONTENT_TYPE'],'application/json')!==false) ||
            (isset($_SERVER['HTTP_CONTENT_TYPE']) && \stripos($_SERVER['HTTP_CONTENT_TYPE'],'application/json')!==false) // PHP build in Webserver !?
        ) {
            if ($json = json_decode(@\file_get_contents('php://input'), true)) {
                $config['body'] = $json;
                $config['request'] = \array_merge($config['request'], $json);
            }
        }

        $request = new self($config);

        return $request;
    }

    public function __construct($config = []) {

        $this->request = $config['request'] ?? [];
        $this->post = $config['post'] ?? [];
        $this->query = $config['query'] ?? [];
        $this->server = $config['server'] ?? [];
        $this->body = $config['body'] ?? [];
        $this->headers = $config['headers'] ?? [];
        $this->cookies = $config['cookies'] ?? [];
        
        $this->site_url = $config['site_url'] ?? '';
        $this->base_url = $config['base_url'] ?? '';
        $this->base_route = $config['base_route'] ?? '';
        $this->route = $config['route'] ?? '/';
    }

    public function param($index=null, $default = null, $source = null) {

        $src = $source ? $source : $this->request;
        $cast = null;

        if (\strpos($index, ':') !== false) {
            list($index, $cast) = \explode(':', $index, 2);
        }

        $value = fetch_from_array($src, $index, $default);

        if ($cast) {

            if (\in_array($cast, ['bool', 'boolean']) && \is_string($value) && \in_array($cast, ['true', 'false'])) {
                $value = $value == 'true' ? true : false;
            }

            \settype($value, $cast);
        }

        return $value;
    }

    public function getClientIp(){

        if (isset($this->server['HTTP_X_FORWARDED_FOR'])){
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            return $this->server['HTTP_X_FORWARDED_FOR'];
        }elseif (isset($this->server['HTTP_CLIENT_IP'])){
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            return $this->server['HTTP_CLIENT_IP'];
        }
        elseif (isset($this->server['REMOTE_ADDR'])){
            // The remote IP address
            return $this->server['REMOTE_ADDR'];
        }

        return null;
    }

    public function getClientLang($default="en") {
        if (!isset($this->server['HTTP_ACCEPT_LANGUAGE'])) {
            return $default;
        }
        return \strtolower(\substr($this->server['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    }

    public function getSiteUrl($withpath = false) {

        $url = $this->site_url;

        if ($withpath) {
            $url .= \implode('/', \array_slice(\explode('/', $this->server['SCRIPT_NAME']), 0, -1));
        }

        return \rtrim($url, '/');
    }

    public function is($type){

        switch (\strtolower($type)){
            case 'ajax':
                return (
                    (isset($this->server['HTTP_X_REQUESTED_WITH']) && ($this->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))        ||
                    (isset($this->server['CONTENT_TYPE']) && \stripos($this->server['CONTENT_TYPE'],'application/json')!==false)           ||
                    (isset($this->server['HTTP_CONTENT_TYPE']) && \stripos($this->server['HTTP_CONTENT_TYPE'],'application/json')!==false)
                );
                break;

            case 'mobile':

                $mobileDevices = [
                    'midp','240x320','blackberry','netfront','nokia','panasonic','portalmmm','sharp','sie-','sonyericsson',
                    'symbian','windows ce','benq','mda','mot-','opera mini','philips','pocket pc','sagem','samsung',
                    'sda','sgh-','vodafone','xda','iphone', 'ipod','android'
                ];

                return \preg_match('/(' . \implode('|', $mobileDevices). ')/i', \strtolower($this->server['HTTP_USER_AGENT']));
                break;

            case 'post':
                return (\strtolower($this->server['REQUEST_METHOD']) == 'post');
                break;

            case 'get':
                return (\strtolower($this->server['REQUEST_METHOD']) == 'get');
                break;

            case 'put':
                return (\strtolower($this->server['REQUEST_METHOD']) == 'put');
                break;

            case 'delete':
                return (\strtolower($this->server['REQUEST_METHOD']) == 'delete');
                break;

            case 'ssl':
                return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off');
                break;
        }

        return false;
    }

    public static function getAllHeaders($server) {

        if (!$server) {
            $server = $_SERVER;
        }

        $headers = [];

        $copy_server = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];

        foreach ($server as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($server[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $server['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($server['PHP_AUTH_USER'])) {
                $basic_pass = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($server['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($server['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $server['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}
