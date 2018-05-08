<?php

namespace Cockpit\Controller;

class Assets extends \Cockpit\AuthController {

    public function index() {

        return $this->render('cockpit:views/assets/index.php');
    }

    public function listAssets() {

        $options = [
            'sort' => ['created' => -1]
        ];

        if ($filter = $this->param("filter", null)) $options["filter"] = $filter;
        if ($limit  = $this->param("limit", null))  $options["limit"] = $limit;
        if ($sort   = $this->param("sort", null))   $options["sort"] = $sort;
        if ($skip   = $this->param("skip", null))   $options["skip"] = $skip;

        return $this->module('cockpit')->listAssets($options);
    }

    public function upload() {

        return $this->module('cockpit')->uploadAssets('files');
    }

    public function removeAssets() {

        if ($assets = $this->param('assets', false)) {
            return $this->module('cockpit')->removeAssets($assets);
        }

        return false;
    }

    public function updateAsset() {

        if ($asset = $this->param('asset', false)) {
            return $this->module('cockpit')->updateAssets($asset);
        }

        return false;
    }

}
