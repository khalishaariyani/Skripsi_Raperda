
<!-- layouts/head.php -->
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';

?>
<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Spruha - PHP Modular Admin Template" />
    <meta name="author" content="Spruko Technologies" />
    <meta
        name="keywords"
        content="admin, dashboard, bootstrap, php, template, modular" />

    <title><?= $pageTitle ?? 'Dashboard' ?> | Raperda Banjarmasin</title>
    <link rel="icon" href="<?= ASSETS_URL ?>/images/brand-logos/logoatas2.png" type="image/x-icon" />


    <!-- BOOTSTRAP CSS -->
    <link
        id="style"
        href="<?= ASSETS_URL ?>/libs/bootstrap/css/bootstrap.min.css"
        rel="stylesheet" />

    <!-- ICONS CSS -->
    <link href="<?= ASSETS_URL ?>/css/icons.css" rel="stylesheet" />

    <!-- THEME CSS -->
    <link href="<?= ASSETS_URL ?>/css/styles.min.css" rel="stylesheet" />
    <link href="<?= ASSETS_URL ?>/css/dark-style.css" rel="stylesheet" />
    <link href="<?= ASSETS_URL ?>/css/skin-modes.css" rel="stylesheet" />

    <!-- VENDORS CSS -->
    <link
        href="<?= ASSETS_URL ?>/libs/node-waves/waves.min.css"
        rel="stylesheet" />
    <link
        href="<?= ASSETS_URL ?>/libs/simplebar/simplebar.min.css"
        rel="stylesheet" />
    <link
        href="<?= ASSETS_URL ?>/libs/flatpickr/flatpickr.min.css"
        rel="stylesheet" />
    <link
        href="<?= ASSETS_URL ?>/libs/choices.js/public/assets/styles/choices.min.css"
        rel="stylesheet" />
    <link
        href="<?= ASSETS_URL ?>/libs/jsvectormap/css/jsvectormap.min.css"
        rel="stylesheet" />
    <link
        href="<?= ASSETS_URL ?>/libs/swiper/swiper-bundle.min.css"
        rel="stylesheet" />

        <!-- DataTables CSS 
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" /> -->


    <!-- CUSTOM STYLES (Jika ada) -->
    <link href="<?= ASSETS_URL ?>/css/custom.css" rel="stylesheet" />
    <style>
        .pcr-app,
        .pcr-selection,
        .pcr-color-preview {
            display: none !important;
        }
    </style>
</head>