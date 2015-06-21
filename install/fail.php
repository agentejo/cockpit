<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cockpit installation</title>
    <script src="../assets/lib/jquery.js"></script>
    <script src="../assets/lib/uikit/js/uikit.min.js"></script>
    <link rel="stylesheet" href="../assets/app/css/style.css">
</head>
<body class="uk-bg-light-radial uk-height-viewport uk-flex uk-flex-middle">

    <div class="uk-width-medium-1-3 uk-container-center uk-text-center uk-animation-slide-fade">

        <div class="uk-panel uk-panel-box uk-panel-card">

            <h1>Installation failed</h1>

            <div class="uk-panel uk-panel-box">

                <strong>Please check the following requirement(s):</strong>

                <div class="uk-alert uk-alert-danger">
                    <?php echo @$info;?>
                </div>

            </div>

            <div class="uk-margin-top">
                <a href="?<?php echo time();?>" class="uk-button uk-button-large uk-button-primary uk-width-1-1">Retry installation</a>
            </div>

        </div>

    </div>

</body>
</html>
