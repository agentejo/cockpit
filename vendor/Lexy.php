<?php

/**
 * Lexy class. Simple on the fly template parser class
 *
 * based on: https://github.com/laravel/framework/blob/master/src/Illuminate/View/Compilers/BladeCompiler.php
 *
 */
class Lexy {

    protected $cachePath = false;

    protected $srcinfo;

    protected $compilers = array(
        'extensions',
        'comments',
        'echos',
        'default_structures',
        'else',
        'unless',
        'unescape_echos',
        'php_tags'
    );

    protected $extensions = array();

    /**
     * [$allowed_calls description]
     * @var array
     */
    protected $allowed_calls = array(

        // core
        'true','false',

        // string functions
        'explode','implode','strtolower','strtoupper','substr','stristr','strpos','print','print_r','number_format','htmlentities',
        'md5','strip_tags','htmlspecialchars',

        // time functions
        'date','time','mktime',

        // math functions
        'round','trunc','rand','ceil','floor','srand',
    );


    /**
     * [render description]
     * @param  [type]  $__content [description]
     * @param  array   $__params  [description]
     * @param  boolean $__sandbox [description]
     * @return [type]             [description]
     */
    public static function render($content, $params = array(), $sandbox=false, $srcinfo=null){

        $obj = new self();

        return $obj->execute($content, $params, $sandbox, $srcinfo);
    }

    /**
     * [render_file description]
     * @param  [type]  $file    [description]
     * @param  array   $params  [description]
     * @param  boolean $sandbox [description]
     * @return [type]           [description]
     */
    public static function render_file($file, $params = array(), $sandbox=false){

        $obj = new self();

        return $obj->file($file, $params, $sandbox);
    }

    /**
     * [setcachePath description]
     *
     * @param  [type]  $path    [description]
     */
    public function setCachePath($path){
        $this->cachePath = is_string($path) ? rtrim($path, "/\\") : $path;
    }

    /**
     * [execute description]
     * @param  [type]  $content [description]
     * @param  array   $params  [description]
     * @param  boolean $sandbox [description]
     * @param  [type]  $srcinfo [description]
     * @return [type]           [description]
     */
    public function execute($content, $params = array(), $sandbox=false, $srcinfo=null) {

        $obj = $this;

        ob_start();

        lexy_eval_with_params($obj, $content, $params, $sandbox, $srcinfo);

        $output = ob_get_clean();

        return $output;
    }

    /**
     * [file description]
     * @param  [type]  $file [description]
     * @param  array   $params  [description]
     * @param  boolean $sandbox [description]
     * @return [type]           [description]
     */
    public function file($file, $params = array(), $sandbox=false) {

        if ($this->cachePath) {

            $cachedfile = $this->get_cached_file($file, $sandbox);

            if ($cachedfile) {

                ob_start();

                lexy_include_with_params($cachedfile, $params, $file);

                $output = ob_get_clean();

                return $output;
            }
        }


        return $this->execute(file_get_contents($file), $params, $sandbox, $file);
    }

    protected function get_cached_file($file, $sandbox) {

        $cachedfile = $this->cachePath.'/'.md5($file).'.lexy.php';

        if (!file_exists($cachedfile)) {
            $cachedfile = $this->cache_file($file, $cachedfile, null, $sandbox);
        }

        if ($cachedfile) {

            $mtime = filemtime($file);

            if(filemtime($cachedfile)!=$mtime) {
                $cachedfile = $this->cache_file($file, $cachedfile, $mtime, $sandbox);
            }

            return $cachedfile;
        }

        return false;
    }

    protected function cache_file($file, $cachedfile, $filemtime = null, $sandbox = false) {

        if (!$filemtime){
            $filemtime = filemtime($file);
        }

        if (file_put_contents($cachedfile, $this->parse(file_get_contents($file), $sandbox, $file))){
            touch($cachedfile,  $filemtime);
            return $cachedfile;
        }

        return false;
    }

    /**
     * [parse description]
     * @param  [type]  $text    [description]
     * @param  boolean $sandbox [description]
     * @return [type]           [description]
     */
    public function parse($text, $sandbox=false, $srcinfo=null) {

        $this->srcinfo = $srcinfo;

        return $this->compile($text, $sandbox);
    }

    /**
     * [compile description]
     * @param  [type]  $text    [description]
     * @param  boolean $sandbox [description]
     * @return [type]           [description]
     */
    protected function compile($text, $sandbox=false){

        // disable php in sandbox mode
        if ($sandbox) {
            $text = str_replace( array("<?","?>"), array("&lt;?","?&gt;"), $text);
        }

        foreach ($this->compilers as $compiler) {
            $method = "compile_{$compiler}";
            $text   = $this->{$method}($text);
        }

        if($sandbox) {

            $lines = explode("\n", $text);

            foreach ($lines as $ln => &$line) {
                if($errors = $this->check_security($line)) {
                    return 'illegal call(s): '.implode(", ", $errors)." - on line ".$ln.($this->srcinfo ? ' ('.$this->srcinfo.') ':'');
                }
            }
        }

        if($errors = $this->check_syntax($text)) {

            if($this->srcinfo) $errors[] = '('.$this->srcinfo.')';

            return implode("\n", $errors);
        }

        return $text;
    }

    public function allowCall($call) {
        $this->allowed_calls[] = $call;
    }

    public function extend($compiler) {
        $this->extensions[] = $compiler;
    }

    /**
     * [check_security description]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    protected function check_security($code) {

        $tokens = token_get_all($code);
        $errors = array();

        foreach ($tokens as $index => $toc) {
            if(is_array($toc) && isset($toc[0])) {

                //var_dump($toc[0]);

                switch($toc[0]){

                    case T_STRING:

                        if(!in_array(strtolower($toc[1]), $this->allowed_calls)){

                            $prevtoc = $tokens[$index-1];

                            if(!isset($prevtoc[1]) || (isset($prevtoc[1]) &&$prevtoc[1]!='->')){
                                $errors[] = $toc[1];
                            }
                        }
                        break;

                    case T_REQUIRE_ONCE:
                    case T_REQUIRE:
                    case T_NEW:
                    case T_RETURN:
                    case T_BREAK:
                    case T_CATCH:
                    case T_CLONE:
                    case T_EXIT:
                    case T_PRINT:
                    case T_GLOBAL:
                    case T_INCLUDE_ONCE:
                    case T_INCLUDE:
                    case T_EVAL:
                    case T_FUNCTION:
                        if(!in_array(strtolower($toc[1]), $this->allowed_calls)){
                            $errors[] = 'illegal call: '.$toc[1];
                        }
                        break;
                }
            }
        }

        return count($errors) ? $errors:false;
    }

    /**
     * [check_syntax description]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    protected function check_syntax($code){

        $errors = array();

        ob_start();

        $check = function_exists('eval') ? eval('?>'.'<?php if(0): ?>'.$code.'<?php endif; ?><?php ') : true;

        if ($check === false) {
            $output = ob_get_clean();
            $output = strip_tags($output);

            if (preg_match_all("/on line (\d+)/m", $output, $matches)) {

                foreach($matches[1] as $m){
                    $errors[] = "Parse error on line: ".$m;
                }

            } else {
                $errors[] = 'syntax error';
            }

        } else {
            ob_end_clean();
        }

        return count($errors) ? $errors:false;
    }

    /* COMPILERS */

    /**
     * Rewrites Lexi's comments into PHP comments.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_comments($value) {

        return preg_replace('/\{\{\--((.|\s)*?)--\}\}/', "<?php /* $1 */ ?>", $value);
    }


    /**
     * Rewrites Lexi's escaped statements.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_unescape_echos($value) {


        return preg_replace('/\@@(.+?)@@/', '{{$1}}', $value);
    }

    /**
     * Rewrites Lexi's echo statements into PHP echo statements.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_echos($value) {

        $value = preg_replace('/\{\{\{(.+?)\}\}\}/', '<?php echo htmlentities($1, ENT_QUOTES, "UTF-8", false); ?>', $value);

        return preg_replace('/\{\{(.+?)\}\}/', '<?php echo $1; ?>', $value);
    }

    /**
     * Rewrites Lexi's structure openings into PHP structure openings.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_default_structures($value) {


        $value = preg_replace('/(?(R)\((?:[^\(\)]|(?R))*\)|(?<!\w)(\s*)@(if|elseif|foreach|for|while)(\s*(?R)+))/', '$1<?php $2$3 { ?>', $value);
        $value = preg_replace('/(\s*)@(endif|endforeach|endfor|endwhile)(\s*)/', '$1<?php } ?>$3', $value);
        $value = preg_replace('/(\s*)@(end)(\s*)/', '$1<?php } ?>$3', $value);

        return $value;
    }

    /**
     * Rewrites Lexi's else statements into PHP else statements.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_else($value) {
        return preg_replace('/(\s*)@(else)(\s*)/', '$1<?php }else{ ?>$3', $value);
    }

    /**
     * Rewrites Lexi's "unless" statements into valid PHP.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_unless($value) {
        $value = preg_replace('/(\s*)@unless(\s*\(.*\))/', '$1<?php if ( ! ($2)): ?>', $value);
        $value = str_replace('@endunless', '<?php endif; ?>', $value);

        return $value;
    }

    /**
     * Rewrites Lexi's php tags.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_php_tags($value) {

        return str_replace(array('{%', '%}'), array('<?php', '?>'), $value);
    }

    /**
     * Execute user defined compilers.
     *
     * @param  string  $value
     * @return string
     */
    protected function compile_extensions($value) {

        foreach ($this->extensions as &$compiler) {
            $value = call_user_func($compiler, $value);
        }

        return $value;
    }

}

function lexy_eval_with_params($__lexyobj, $__lexycontent, $__lexyparams, $__lexysandbox, $__lexysrcinfo) {
    extract($__lexyparams);
    $__FILE = $__lexysrcinfo;
    eval('?>'.$__lexyobj->parse($__lexycontent, $__lexysandbox, $__lexysrcinfo).'<?php ');
}

function lexy_include_with_params($__incfile, $__lexyparams, $__lexysrcinfo) {
    extract($__lexyparams);
    $__FILE = $__lexysrcinfo;
    include($__incfile);
}