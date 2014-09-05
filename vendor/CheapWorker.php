<?php


class CheapWorker {

    public $description;
    protected $log;

    public function __construct() {

    }

    /* to implement in sub-class */
    public function before() {}
    public function after($output) {}
    public function finally($output) {}


    public function clearlog(){
        $this->log = [];
    }

    public function getLog() {
        return $this->log;
    }

    protected function log($message) {
        $this->log[] = $message;
    }

    public static function run($renderer=null) {

        $classname = get_called_class();
        $obj       = new $classname();
        $methods   = [];

        foreach (get_class_methods($obj) as $method) {

            if (substr($method, 0,4)=="step") {
                $methods[$method] = array($obj, $method);
            }
        }

        ksort($methods);

        $output = new \ArrayObject([
            'description'     => $obj->description,
            'steps'           => array_keys($methods),
            'logs'            => [],
            'completed'       => true,
            'duration'        => 0,
            'steps_completed' => [],
            'error'           => false
        ]);

        $start = microtime();

        $obj->before();

        foreach ($methods as $name => $callback) {

            $obj->clearlog();

            try {
                $result = call_user_func_array($callback, [$output]);
            } catch(\Exception $e) {
                $result = false;
                $output['error'] = $e->getMessage();
            }

            $output['logs'][$name] = $obj->getlog();

            if ($result === false) {
                $output['completed']  = false;
                $output['stopped_at'] = $name;
                break;
            } else {
                $output['steps_completed'][] = $name;
            }
        }

        $output["duration"] = microtime() - $start;

        if (!$output['completed']) {
            $obj->after($output);
        }

        $obj->finally($output);

        return $renderer ? call_user_func($renderer, $output) : $output;
    }
}