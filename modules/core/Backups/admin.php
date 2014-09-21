<?php

// Helper
$app->helpers["backup"]   = 'Backups\\Helper\\Backup';

// Routes
$app->bindClass("Backups\\Controller\\Backups", "backups");