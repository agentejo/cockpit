<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System installation</title>
    <script src="../assets/lib/jquery.js"></script>
    <script src="../assets/lib/uikit/js/uikit.min.js"></script>
    <link rel="stylesheet" href="../assets/app/css/style.css">
    <style>
        html, body { background: #0e0f19; }
        .info-container {
            width: 460px;
            max-width: 90%;
        }
    </style>
</head>
<body class="uk-height-viewport uk-flex uk-flex-middle">

    <div class="info-container uk-container-center uk-text-center uk-animation-slide-fade">

        <div class="uk-panel uk-panel-box uk-panel-space uk-panel-card uk-animation-scale">

            <img src="../favicon.ico" width="50" height="50" alt="logo">

            <h1>Installation failed</h1>

            <img src="../assets/app/media/icons/emoticon-sad.svg" width="100" alt="sad">

            <div class="uk-margin-large">

                <strong>Following requirement(s) failed:</strong>

                <div class="uk-alert uk-alert-danger">
                    <?php echo @$info;?>
                </div>

            </div>

            <div class="uk-margin-top">
                <a href="?<?php echo time();?>" class="uk-button uk-button-large uk-button-outline uk-button-primary uk-width-1-1">Retry installation</a>
            </div>

        </div>

    </div>

</body>
</html>
