<?php

namespace Collections\Controller;


class Import extends \Cockpit\AuthController {


    public function collection($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        return $this->render('collections:views/import/collection.php', compact('collection'));
    }
}
