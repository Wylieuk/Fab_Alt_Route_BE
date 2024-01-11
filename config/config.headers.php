<?php

global $config;


header("Content-Security-Policy: script-src 'report-sample' 'nonce-" . $config['CspNonce'] . "'; object-src 'none'; base-uri 'self';" . $policies . "; report-uri scripts/cps_logger.php");

header("X-Frame-Options: deny");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1");
