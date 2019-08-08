<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Collections\Controller;


class Trash extends \Cockpit\AuthController {

    public function collection($name = null) {

        if (!$name) {
            return null;
        }

        if (!$this->module('collections')->hasaccess($name, 'entries_create')) {
            return $this->helper('admin')->denyRequest();
        }

        $collection = $this->module('collections')->collection($name);

        return $this->render('collections:views/trash/collection.php', compact('collection'));
    }
}