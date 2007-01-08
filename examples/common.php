<?php
define('SERVICES_HYPERESTRAIER_DEBUG', 1);
error_reporting(E_ALL & ~E_STRICT);

require_once 'Services/HyperEstraier/Node.php';

$uri = 'http://localhost:1978/node/test';
$user = 'admin';
$pass = 'admin';
