<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Helper;

/**
 * Cookie helper
 */
class Cookie extends \Lime\Helper
{
    /**
     * @var array
     */
    protected $_cookies = array();

    /**
     * @var array
     */
    protected $_deleted_cookies = array();

    /**
     * Sets a cookie
     *
     * @param string $name
     * @param string $value
     * @param mixed $ttl
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $http_only
     * @return bool
     */
    public function set($name, $value, $ttl = 86400 /* 1 day */, $path = '/', $domain = '', $secure = false, $http_only = false) {
        $this->_cookies[$name] = $value;
        $result = \setcookie($name, $value, time() + $ttl, $path, $domain, $secure, $http_only);

        if (isset($this->_deleted_cookies[$name])) {
            unset($this->_deleted_cookies[$name]);
        }

        return $result;
    }

    /**
     * Gets a cookie
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
     * Deletes a cookie
     *
     * @param string $name
     * @return bool
     */
    public function delete($name, $path = '/', $domain = '', $secure = false, $http_only = false) {
        $success = $this->set($name, null, -10, $path, $domain, $secure, $http_only);
        $this->_deleted_cookies[$name] = $name;

        if (isset($this->_cookies[$name])) {
            unset($this->_cookies[$name]);
        }

        return $success;
    }

    /**
     * Gets a cookie and eats it
     *
     * @param string $name
     * @return mixed
     */
    public function getAndDelete($name, $path = '/', $domain = '', $secure = false, $http_only = false) {
        $value = $this->get($name);
        $this->delete($name, $path, $domain, $secure, $http_only);

        return $value;
    }
}
