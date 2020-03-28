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


class Import extends \Cockpit\AuthController {


    public function collection($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        return $this->render('collections:views/import/collection.php', compact('collection'));
    }

    public function execute() {

        \session_write_close();

        $collection = $this->param('collection', null);
        $entries = $this->param('entries', null);

        if (!$collection || !$entries) {
            return false;
        }

        if (!$this->module('collections')->exists($collection)) {
            return $this->stop(['error' => 'Collection not found'], 412);
        }

        if (!$this->module('collections')->hasaccess($collection, 'entries_create')) {
            return $this->stop(['error' => 'Unauthorized'], 401);
        }

        $_collection = $this->module('collections')->collection($collection);
        $cid  = $_collection['_id'];
        $userId = $this->module('cockpit')->getUser('_id');

        foreach ($entries as &$entry) {

            $entry['_by'] = $userId;
            $entry['_mby'] = $userId;

            if (isset($entry['_id']) && !$this->app->storage->count("collections/{$cid}", ['_id' => $entry['_id']])) {

                $this->app->trigger('collections.save.before', [$collection, &$entry, false]);
                $this->app->trigger("collections.save.before.{$collection}", [$collection, &$entry, false]);

                $this->app->storage->insert("collections/{$cid}", $entry);

                $this->app->trigger('collections.save.after', [$collection, &$entry, false]);
                $this->app->trigger("collections.save.after.{$collection}", [$collection, &$entry, false]);

            } else {
                $this->module('collections')->save($collection, $entry);
            }
        }

        return $entries;
    }
}
