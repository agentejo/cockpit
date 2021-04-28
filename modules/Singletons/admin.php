<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app->on('admin.init', function() {

    if (!$this->module('cockpit')->getGroupRights('singletons') && !$this->module('singletons')->getSingletonsInGroup()) {

        $this->bind('/singletons/*', function() {
            return $this('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /singleton/*
    $this->bindClass('Singletons\\Controller\\Admin', 'singletons');

    $active = strpos($this['route'], '/singletons') === 0;

    // add to modules menu
    $this->helper('admin')->addMenuItem('modules', [
        'label' => 'Singletons',
        'icon'  => 'singletons:icon.svg',
        'route' => '/singletons',
        'active' => $active
    ]);

    if ($active) {
        $this->helper('admin')->favicon = 'singletons:icon.svg';
    }

    /**
     * listen to app search to filter singleton
     */
    $this->on('cockpit.search', function($search, $list) {

        foreach ($this->module('singletons')->getSingletonsInGroup() as $singleton => $meta) {

            if (stripos($singleton, $search)!==false || stripos($meta['label'], $search)!==false) {

                $list[] = [
                    'icon'  => 'th',
                    'title' => $meta['label'] ? $meta['label'] : $meta['name'],
                    'url'   => $this->routeUrl('/singletons/singleton/'.$meta['name'])
                ];
            }
        }
    });

    // dashboard widgets
    $this->on('admin.dashboard.widgets', function($widgets) {

        $singletons = $this->module('singletons')->getSingletonsInGroup();

        $widgets[] = [
            'name'    => 'singleton',
            'content' => $this->view('singletons:views/widgets/dashboard.php', compact('singletons')),
            'area'    => 'aside-right'
        ];

    }, 100);

    // register events for autocomplete
    $this->on('cockpit.webhook.events', function($triggers) {

        foreach([
            'singleton.getData.after',
            'singleton.getData.after.{$name}',
            'singleton.remove',
            'singleton.remove.{$name}',
            'singleton.save.after',
            'singleton.save.after.{$name}',
            'singleton.save.before',
            'singleton.save.before.{$name}',
            'singleton.saveData.after',
            'singleton.saveData.after.{$name}',
            'singleton.saveData.before',
            'singleton.saveData.before.{$name}',
        ] as &$evt) { $triggers[] = $evt; }
    });

    // update assets references on file update
    $this->on('cockpit.assets.updatefile', function($asset) {

        $id = $asset['_id'];
        $filter = ($this->storage->type == 'mongolite') ?
            function ($doc) use ($id) { return strpos(json_encode($doc), $id) !== false;}
            :
            ['$where' => "function() { return JSON.stringify(this).indexOf('{$id}') > -1; }"]
        ;

        $update = function(&$items) use($asset, $id, &$update) {

            if (!is_array($items)) return $items;

            foreach ($items as $k => &$v) {
                if (!is_array($v)) continue;
                if (is_array($items[$k])) $items[$k] = $update($items[$k]);
                if (isset($v['_id']) && $v['_id'] == $id) $items[$k] = $asset;
            }
            return $items;
        };

        $singletons = $this->storage->find('singletons', ['filter' => $filter])->toArray();

        if (!count($singletons)) return;

        $singletons = $update($singletons);

        foreach ($singletons as $singleton) {
            $this->storage->save('singletons', $singleton);
        }
    });
});
