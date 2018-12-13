<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - onwards, Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>WPИ-XM Server Stack for Windows - <?php echo WPNXM_VERSION; ?></title>
    <meta name="description" content="WPИ-XM Server Stack for Windows - Webinterface.">
    <meta name="author" content="Jens-André Koch" />
    <link rel="shortcut icon" href="favicon.ico" />

    <!-- jQuery -->
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.min.js"></script>
    <!-- Twitter's Bootstrap -->
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/bootstrap.min.js"></script>
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/bootstrap.min.css" rel="stylesheet" />

    <?php if (isset($load_jquery_additionals) && $load_jquery_additionals === true) { ?>
    <!-- jQuery Plugins -->
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.form.js"></script>
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.organicTabs.js"></script>
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.treeTable.js"></script>
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.jeditable.js"></script>
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/jquery.treeTable.css" rel="stylesheet" />
    <?php } ?>

    <!-- WPN-XM stuff last in line, because using jQuery and overwriting CSS -->
    <link rel="stylesheet" type="text/css" href="<?=WPNXM_ASSETS?>css/style.css"  media="screen, projection" />
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/wpnxm.js"></script>

</head>
<body>

<!--
    These CSS will come alive only, when Javascript is disabled.
    It's displaying a message for all the security nerds with disabled javascript.
    We need this reminder, because the WPN-XM configuration pages depend on jQuery and AJAX.
-->
<noscript><style type="text/css">
#page{ display:none; }
#javascript-off-errorbox { display:block; font-size:20px; color:red; }
</style></noscript>

<div class="container center">

    <h1 class="logo">
        <small style="position:relative; top:90px; left:150px;">Version <?php echo WPNXM_VERSION; ?></small>
    </h1>

    <?php
        Webinterface\Helper\Viewhelper::showMenu();
        Webinterface\Helper\Viewhelper::showWelcome();
    ?>

    <div id="javascript-off-errorbox">
      <div class="error">
      Please enable "javascript" in your browser in order to use this application.
      </div>
    </div>

    <div class="content-centered">
<!-- stop "header.php" -->
