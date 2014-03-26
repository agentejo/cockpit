<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cockpit installation</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <script src="../assets/vendor/jquery.js"></script>
    <script src="../assets/vendor/uikit/js/uikit.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="install-page app-panel uk-animation-fade">

        <div id="logo">
            <img src="../assets/images/cockpit.png" width="60" height="60" alt="logo">
        </div>

        <div class="uk-container uk-container-center">

            <h1 class="uk-margin">Installation failed</h1>

            <div class="uk-panel">
                Failed:
                <div class="uk-alert uk-alert-danger">
                    <?php echo @$info;?>
                </div>
            </div>

            <div class="uk-margin-top">
                <a href="?<?php echo time();?>" class="uk-button uk-button-large uk-button-primary">Retry installation</a>
            </div>
        </div>
    </div>
</body>
</html>