<?php

$this->module("cockpit")->extend([

    'listAssets' => function($options = []) {

        $assets = $this->app->storage->find('cockpit/assets', $options)->toArray();
        $total  = (!isset($options['skip']) && !isset($options['limit'])) ? count($assets) : $this->app->storage->count('cockpit/assets', isset($options['filter']) ? $options['filter'] : null);

        $this->app->trigger('cockpit.assets.list', [$assets]);

        return compact('assets', 'total');
    },

    'addAssets' => function($files) use($app) {

        $files = isset($files[0]) ? $files : [$files];

        $finfo      = finfo_open(FILEINFO_MIME_TYPE);
        $path       = $this->app->path('#uploads:');
        $targetpath = $path.'/'.date('Y/m/d');
        $assets     = [];

        $created = time();

        foreach ($files as &$file) {

            // clean filename
            $name = basename($file);

            $asset = [
                'path' => str_replace($path, '', $file),
                'title' => $name,
                'mime' => finfo_file($finfo, $file),
                'description' => '',
                'tags' => [],
                'size' => filesize($file),
                'image' => preg_match('/\.(jpg|jpeg|png|gif|svg)$/i', $file) ? true:false,
                'video' => preg_match('/\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i', $file) ? true:false,
                'audio' => preg_match('/\.(mp3|weba|ogg|wav|flac)$/i', $file) ? true:false,
                'archive' => preg_match('/\.(zip|rar|7zip|gz|tar)$/i', $file) ? true:false,
                'document' => preg_match('/\.(txt|htm|html|pdf|md)$/i', $file) ? true:false,
                'code' => preg_match('/\.(htm|html|php|css|less|js|json|md|markdown|yaml|xml|htaccess)$/i', $file) ? true:false,
                'created' => $created,
                'modified' => $created,
                '_by' => $this->app->module('cockpit')->getUser('_id')
            ];

            if ($asset['image'] && !preg_match('/\.svg$/i', $file)) {

                $info = getimagesize($file);
                $asset['width']  = $info[0];
                $asset['height'] = $info[1];
                $asset['colors'] = [];

                if ($asset['width'] && $asset['height']) {

                    try {
                        $asset['colors'] = \ColorThief\ColorThief::getPalette($file, 5, ceil(($asset['width'] * $asset['height']) / 10000));
                    } catch (\Exception $e) {
                        $asset['colors'] = [];
                    }

                    foreach($asset['colors'] as &$color) {
                        $color = sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
                    }
                }
            }

            $assets[] = $asset;

        }

        if (count($assets)) {
            $this->app->trigger('cockpit.assets.save', [$assets]);
            $this->app->storage->insert('cockpit/assets', $assets);
        }

        return $assets;
    },

    'uploadAssets' => function($param = 'files') {

        $files      = isset($_FILES[$param]) ? $_FILES[$param] : [];
        $path       = $this->app->path('#uploads:');
        $targetpath = $path.'/'.date('Y/m/d');
        $uploaded   = [];
        $failed     = [];

        $_uploaded  = [];
        $_failed    = [];
        $_files     = [];
        $assets     = [];

        if (!file_exists($targetpath) && !mkdir($targetpath, 0777, true)) {
            return false;
        }

        if (isset($files['name']) && $path && file_exists($targetpath)) {

            for ($i = 0; $i < count($files['name']); $i++) {

                // clean filename
                $clean  = uniqid().preg_replace('/[^a-zA-Z0-9-_\.]/','', str_replace(' ', '-', $files['name'][$i]));
                $target = $targetpath.'/'.$clean;

                if (!$files['error'][$i] && move_uploaded_file($files['tmp_name'][$i], $target)) {

                    $_files[]    = $target;
                    $uploaded[]  = $files['name'][$i];
                    $_uploaded[] = $targetpath.'/'.$clean;

                } else {
                    $failed[]    = $files['name'][$i];
                    $_failed[]   = $targetpath.'/'.$clean;
                }
            }

        }

        if (count($_files)) {
            $assets = $this->addAssets($_files);
        }

        return ['uploaded' => $uploaded, 'failed' => $failed, 'assets' => $assets];
    },

    'removeAssets' => function($assets) {

        $assets = isset($assets[0]) ? $assets : [$assets];

        foreach($assets as &$asset) {

            if (!isset($asset['_id'])) continue;

            if (!isset($asset['path'])) {
                $asset = $this->app->storage->findOne('cockpit/assets', ['_id' => $asset['_id']]);
            }

            if (!$asset) continue;

            $this->app->storage->remove('cockpit/assets', ['_id' => $asset['_id']]);

            if ($file = $this->app->path('#uploads:'.$asset['path'])) {
                unlink($file);
            }
        }

        $this->app->trigger('cockpit.assets.remove', [$assets]);

        return $assets;
    },

    'updateAssets' => function($assets) {

        $assets = isset($assets[0]) ? $assets : [$assets];

        foreach ($assets as &$asset) {

            $_asset = $this->app->storage->findOne("cockpit/assets", ['_id' => $asset['_id']]);

            if (!$_asset) continue;

            $asset['modified'] = time();
            $asset['_by'] = $this->app->module('cockpit')->getUser('_id');

            $this->app->storage->save("cockpit/assets", $asset);

        }

        return $assets;
    }
]);
