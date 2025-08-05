<?php


// BASE URL untuk digunakan pada HTML (link, script, img)
define('BASE_URL', '/raperda');

// URL asset (otomatis mengikuti BASE_URL)
define('ASSETS_URL', BASE_URL . '/assets');

// Path absolut sistem (untuk include file)
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define('MODUL_PATH', ROOT_PATH . '/modul');
define('LAYOUT_PATH', ROOT_PATH . '/layout');
define('CONFIG_PATH', ROOT_PATH . '/config');


// NAMA SISTEM (opsional)
define('APP_NAME', 'APLIKASI PENGELOLAAN KEGIATAN PEMBENTUKAN PERDA - DPRD KOTA BANJARMASIN');

// Waktu Default Zona Indonesia
date_default_timezone_set('Asia/Makassar');

// Untuk debugging (aktifkan saat pengembangan)
error_reporting(E_ALL);
ini_set('display_errors', 1);



