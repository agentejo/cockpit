<?php

/**
 * fetch_url_contents - a simple js to php proxy function to get cross domain content.
 *
 * @author     Artur Heinze
 * @copyright  (c) since 2016 Artur Heinze
 * @license    MIT - http://opensource.org/licenses/MIT
 * @url        https://github.com/aheinze/fetch_url_contents
 */

if (isset($_REQUEST['url'])) {

    // allow only query from same host
    if ($_SERVER['HTTP_HOST'] != parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) {
        header('HTTP/1.0 401 Unauthorized');
        return;
    }

    $url     = $_REQUEST['url'];
    $content = null;

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        header('HTTP/1.0 400 Bad Request');
        return;
    }

    // allow only http requests
    if (!preg_match('#^http(|s)\://#', $url)) {
        header('HTTP/1.0 403 Forbidden');
        return;
    }

    if (function_exists('curl_exec')){
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
        curl_setopt($conn, CURLOPT_AUTOREFERER, true);
        curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conn, CURLOPT_VERBOSE, 0);
        $content = curl_exec($conn);
        curl_close($conn);
    }

    if (!$content && function_exists('file_get_contents')){
        $content = @file_get_contents($url);
    }

    if (!$content && function_exists('fopen') && function_exists('stream_get_contents')){
        $handle  = @fopen ($url, "r");
        $content = @stream_get_contents($handle);
    }

    if (!$content) {
        header('HTTP/1.0 503 Service Unavailable');
    }

    return print($content);
}

header('Content-type: application/javascript');

?>(function(doc){

    var script = doc.querySelector('script[src*="fuc.js.php"]');

    if (!script) {
        throw "Script fuc.js.php not found";
    }

    var queryUrl = script.src.replace(/\?(.+)/, ''); // remove possible query string

    function _request(url) {

        return (new Promise(function(resolve, reject) {

            var request = new XMLHttpRequest();
            request.open('POST', queryUrl+'?url='+encodeURI(url), true);

            request.onload = function(e) {
                if (request.status >= 200 && request.status < 400) {
                    resolve(request.responseText);
                } else {
                    reject(arguments);
                }
            };

            request.onerror = function() {
                reject(arguments);
            };

            request.send();
        }));
    }

    window.fetch_url_contents = function(url, type) {

        type = type || 'text';

        var data, promise = new Promise(function(resolve, reject){

            _request(url).then(function(text){

                switch(type.toLowerCase()) {
                    case 'html':
                        data = doc.createElement('div');
                        data.innerHTML = text;
                        break;
                    case 'json':
                        try {
                            data = JSON.parse(text);
                        } catch(e) {
                            return reject(e);
                        }
                        break;
                    default:
                        data = text;
                }

                resolve(data);

            }).catch(function(){
                reject(arguments);
            });
        });

        return promise;
    }

})(document)
