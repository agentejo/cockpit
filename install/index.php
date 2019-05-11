<?php

define('COCKPIT_INSTALL', true);

$sqlitesupport = false;

// check whether sqlite is supported
try {

    if (extension_loaded('pdo')) {
        $test = new PDO('sqlite::memory:');
        $sqlitesupport = true;
    }

} catch (Exception $e) { }

require(__DIR__.'/../bootstrap.php');

function ensure_writable($path) {
    try {
        $dir = COCKPIT_STORAGE_FOLDER.$path;
        if (!file_exists($dir)) {
            mkdir($dir, 0700, true);
            if ($path === '/data') {
                if (file_put_contents($dir.'/.htaccess', 'deny from all') === false) {
                    return false;
                }
            }
        }
        return is_writable($dir);
    } catch (Exception $e) {
        error_log($e);
        return false;
    }
}

// misc checks
$checks = array(
    'Php version >= 7.1.0'                              => (version_compare(PHP_VERSION, '7.1.0') >= 0),
    'Missing PDO extension with Sqlite support'         => $sqlitesupport,
    'GD extension not available'                        => extension_loaded('gd'),
    'MBString extension not available'                  => extension_loaded('mbstring'),
    'Data folder is not writable: /storage/data'        => ensure_writable('/data'),
    'Cache folder is not writable: /storage/cache'      => ensure_writable('/cache'),
    'Temp folder is not writable: /storage/tmp'         => ensure_writable('/tmp'),
    'Thumbs folder is not writable: /storage/thumbs'    => ensure_writable('/thumbs'),
    'Uploads folder is not writable: /storage/uploads'  => ensure_writable('/uploads'),
);

$failed = [];

foreach ($checks as $info => $check) {

    if (!$check) {
        $failed[] = $info;
    }
}

if (!count($failed)) {

    $app = cockpit();

    // check whether cockpit is already installed
    try {

        if ($app->storage->getCollection('cockpit/accounts')->count()) {
            header('Location: '.$app->baseUrl('/'));
            exit;
        }

    } catch(Exception $e) { }

    $created = time();

    $account = [
        'user'     => 'admin',
        'name'     => 'Admin',
        'email'    => 'admin@yourdomain.de',
        'active'   => true,
        'group'    => 'admin',
        'password' => $app->hash('admin'),
        'i18n'     => 'en',
        '_created' => $created,
        '_modified'=> $created,
    ];

    $app->storage->insert("cockpit/accounts", $account);
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System installation</title>
    <script src="../assets/lib/jquery.js"></script>
    <script src="../assets/lib/uikit/js/uikit.min.js"></script>
    <link rel="stylesheet" href="../assets/app/css/style.css">
    <style>
        .info-container {
            width: 460px;
            max-width: 90%;
        }

        .install-dialog {
            box-shadow: 0 30px 75px 0 rgba(10, 25, 41, 0.2);
        }
    </style>
</head>
<body class="uk-height-viewport uk-flex uk-flex-middle">


    <div class="info-container uk-container-center uk-text-center uk-animation-slide-fade">

        <div class="install-dialog uk-panel uk-panel-box uk-panel-space uk-animation-scale">

            <img src="../assets/app/media/logo.svg" width="80" height="80" alt="logo">

            <?php if (count($failed)): ?>

                <h1 class="uk-text-bold">Installation failed</h1>

                <img src="../assets/app/media/icons/emoticon-sad.svg" width="100" alt="sad">

                <div class="uk-margin">

                    <?php foreach ($failed as &$info): ?>
                    <div class="uk-alert uk-alert-danger">
                        <?php echo @$info;?>
                    </div>
                    <?php endforeach; ?>

                </div>

                <div>
                    <a href="?<?php echo time();?>" class="uk-button uk-button-large uk-button-outline uk-button-primary uk-width-1-1">Retry installation</a>
                </div>


            <?php else: ?>

                <h1 class="uk-text-bold">Installation completed</h1>

                <img src="../assets/app/media/icons/party.svg" width="100" alt="success">

                <div class="uk-margin-large">
                    <span class="uk-badge uk-badge-outline uk-text-muted">Login Credentials</span>
                    <p>admin / admin</p>
                </div>

                <div class="uk-alert uk-alert-warning">
                    Please change the login information after your first login into the system for obvious security reasons.
                </div>

                <div class="uk-margin-top">
                    <a href="../" class="uk-button uk-button-large uk-button-primary uk-button-outline uk-width-1-1">Login now</a>
                </div>

            <?php endif; ?>

        </div>

    </div>

</body>
</html>
