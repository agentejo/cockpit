<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra\Helper;

class Cookie extends \Lime\Helper {
    /**
     * @var array
     */
    protected $_cookies = array();

    /**
     * @var array
     */
    protected $_deleted_cookies = array();

    /**
     * sets a cookie
     *
     * @param string $name
     * @param string $value
     * @param mixed $ttl
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $http_only
     * @param (string|null) $same_site
     * @return bool
     * @throws \Exception - throws Exception if SameSite=None and Secure=False
     */
    public function set($name, $value = "", $ttl = 86400 /* 1 day */, $path = '/', $domain = '', $secure = false, $http_only = false, $same_site = null)
    {
        if ($same_site && strtolower($same_site) === 'none' && !$secure) {
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite#fixing_common_warnings
            // "SameSite=None" only if "Secure=True"
            throw new \Exception('"SameSite=None" only if "Secure=True"');
        }

        $options = [
            'expires' => time() + $ttl,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $http_only
        ];
        if($same_site){
            $options['samesite'] = $same_site;
        }

        $result = \setcookie($name, $value, $options);

        if ($result) {
            $this->_cookies[$name] = $value;

            if (isset($this->_deleted_cookies[$name])) {
                unset($this->_deleted_cookies[$name]);
            }
        }

        return $result;
    }

    /**
     * gets a cookie
     *
     * @param string $name
     * @return mixed
     */
    public function get($name) {
        if (isset($this->_deleted_cookies[$name])) {
            return null;
        }

        if (\array_key_exists($name, $this->_cookies)) {
            return $this->_cookies[$name];
        }

        $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        $this->_cookies[$name] = $value;

        return $value;
    }

    /**
     * deletes a cookie
     *
     * @param string $name
     * @return bool
     */
    public function delete($name, $path = '/', $domain = '', $secure = false, $http_only = false, $same_site = null) {
        $success = $this->set($name, null, -10, $path, $domain, $secure, $http_only, $same_site);
        $this->_deleted_cookies[$name] = $name;

        if (isset($this->_cookies[$name])) {
            unset($this->_cookies[$name]);
        }

        return $success;
    }

    /**
     * gets a cookie and eats it
     *
     * @param string $name
     * @return mixed
     */
    public function getAndDelete($name, $path = '/', $domain = '', $secure = false, $http_only = false, $same_site = null) {
        $value = $this->get($name);
        $this->delete($name, $path, $domain, $secure, $http_only, $same_site);

        return $value;
    }
}
