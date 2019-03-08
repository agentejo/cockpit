<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\Controller;

class Utils extends \Cockpit\AuthController {

    public function thumb_url() {

        $options = [
            'src' => $this->param('src', false),
            'fp' => $this->param('fp', null),
            'mode' => $this->param('m', 'thumbnail'),
            'filters' => (array) $this->param('f', []),
            'width' => intval($this->param('w', null)),
            'height' => intval($this->param('h', null)),
            'quality' => intval($this->param('q', 85)),
            'rebuild' => intval($this->param('r', false)),
            'base64' => intval($this->param('b64', false)),
            'output' => intval($this->param('o', false)),
        ];

        // Set single filter when available
        foreach([
            'blur', 'brighten',
            'colorize', 'contrast',
            'darken', 'desaturate',
            'edge detect', 'emboss',
            'flip', 'invert', 'opacity', 'pixelate', 'sepia', 'sharpen', 'sketch'
        ] as $f) {
            if ($this->param($f)) $options[$f] = $this->param($f);
        }

        return $this->module('cockpit')->thumbnail($options);
    }


    public function revisionsCount() {

        if ($id = $this->param('id')) {
            $cnt = $this->app->helper('revisions')->count($id);
            return (string)$cnt;
        }

        return 0;
    }

    public function revisionsRemove() {

        if ($rid = $this->param('rid')) {
            $this->app->helper('revisions')->remove($rid);
            return true;
        }

        return false;
    }

    public function revisionsRemoveAll() {

        if ($oid = $this->param('oid')) {
            $this->app->helper('revisions')->removeAll($oid);
            return true;
        }

        return false;
    }

    public function isResourceLocked($resourceId) {

        $meta = $this->app->helper('admin')->isResourceLocked($resourceId);

        if ($meta) {
            return array_merge($meta, ['locked' => true]);
        }

        return ['locked' => false];
    }

    public function lockResourceId($resourceId) {
        $meta = $this->app->helper('admin')->lockResourceId($resourceId);
        return $meta;
    }

    public function unlockResourceId($resourceId) {
        $this->app->helper('admin')->unlockResourceId($resourceId);

        return ['success' => true];
    }
}
