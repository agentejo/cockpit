<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Singletons\Controller;

class RestApi extends \LimeExtra\Controller {

    public function get($name = null, $field = null) {

        if (!$name) {
            return false;
        }

        if ($user = $this->module('cockpit')->getUser()) {

            if (!$this->module('singletons')->hasaccess($name, 'data') && !$this->module('singletons')->hasaccess($name, 'form')) {
                return $this->stop('{"error": "Unauthorized"}', 401);
            }
        }

        $options = [];

        if ($lang = $this->param('lang', false)) $options['lang'] = $lang;
        if ($populate = $this->param('populate', false)) $options['populate'] = $populate;
        if ($ignoreDefaultFallback = $this->param('ignoreDefaultFallback', false)) $options['ignoreDefaultFallback'] = $ignoreDefaultFallback;
        if ($user) $options['user'] = $user;

        $data = $this->module('singletons')->getData($name, $options);

        if (!$data) {
            return false;
        }

        return $field ? ($data[$field] ?? null) : $data;
    }

    public function singleton($name) {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            $singletons = $this->module('singletons')->getSingletonsInGroup($user['group']);
        } else {
            $singletons = $this->module('singletons')->singletons();
        }

        if (!isset($singletons[$name])) {
           return $this->stop('{"error": "Singleton not found"}', 412);
        }

        return $singletons[$name];
    }

    public function listSingletons($extended = false) {

        $user = $this->module('cockpit')->getUser();

        if ($user) {
            $singletons = $this->module('singletons')->getSingletonsInGroup($user['group']);
        } else {
            $singletons = $this->module('singletons')->singletons();
        }

        return $extended ? $singletons : \array_keys($singletons);
    }
}
