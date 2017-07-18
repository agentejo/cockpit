<?php

namespace Cockpit\Controller;

class Utils extends \Cockpit\AuthController {

    public function thumb_url() {

        $options = [
            'src' => $this->param('src', false),
            'mode' => $this->param('m', 'thumbnail'),
            'width' => intval($this->param('w', null)),
            'height' => intval($this->param('h', null)),
            'quality' => intval($this->param('q', 85)),
            'rebuild' => intval($this->param('r', false)),
            'base64' => intval($this->param('b64', false)),
            'output' => intval($this->param('o', false)),
        ];

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
}
