<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DotEnv {

    public static function load($dir = '.') {

        $config = is_file($dir) ? $dir : "{$dir}/.env";

        if (file_exists($config)) {

            $vars = self::parse(file_get_contents($config));

            foreach ($vars as $key => $value) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }

            return true;
        }

        return false;
    }

    public static function parse($str, $expand = true) {

        $lines = explode("\n", $str);
        $vars = [];

        foreach ($lines as &$line) {

            $line = trim($line);

            if (!$line) continue;
            if ($line[0] == '#') continue;
            if (!strpos($line, '=')) continue;

            list($name, $value) = explode('=', $line, 2);

            $value = trim($value, '"\' ');
            $name = trim($name);

            $vars[$name] = $value;
        }

        if ($expand) {

            $envs = array_merge(getenv(), $vars);

            foreach ($envs as $key => $value) {
                $str = str_replace('${'.$key.'}', $value, $str);
            }

            $vars = self::parse($str, false);
        }

        return $vars;
    }
}
