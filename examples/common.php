<?php
define('SERVICES_HYPERESTRAIER_DEBUG', 1);
error_reporting(E_ALL & ~E_STRICT);

require_once 'Services/HyperEstraier/Node.php';

if (isset($_SERVER['PHP_EST_HTTP_CLIENT'])) {
    switch (strtoupper($_SERVER['PHP_EST_HTTP_CLIENT'])) {
        case 'PEAR':
            Services_HyperEstraier_Utility::setHttpClient(
                Services_HyperEstraier_Utility::HTTP_CLIENT_PEAR);
            break;
        case 'PECL':
            Services_HyperEstraier_Utility::setHttpClient(
                Services_HyperEstraier_Utility::HTTP_CLIENT_PECL);
            break;
        case 'ZEND':
            Services_HyperEstraier_Utility::setHttpClient(
                Services_HyperEstraier_Utility::HTTP_CLIENT_ZEND);
            break;
    }
}

$uri = 'http://localhost:1978/node/test';
$user = 'admin';
$pass = 'admin';
