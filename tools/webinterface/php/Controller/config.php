<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - onwards, Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

function index()
{
    $tpl_data = array(
        'load_jquery_additionals' => true,
        'mongodb_installed' => Webinterface\Helper\Serverstack::isInstalled('mongodb'),
        'xdebug_installed' => Webinterface\Helper\Serverstack::isInstalled('xdebug')
    );

    render('page-action', $tpl_data);
}

function project()
{
   $project = filter_input(INPUT_GET, 'project');

   echo $project;
}

function showtab()
{
    /**
     * Tab Controller - handles GET requests for tab pages.
     * Ajax calls to tab pages look like this: "index.php?page=config&action=showtab&tab=xy".
     * Each tab function renders content for inline display in the tabs content container.
     */
    $tab = filter_input(INPUT_GET, 'tab');
    $tab = strtr($tab, '-', '_'); // minus to underscore conversion
    $tabAction = 'tab_' . $tab;
    if (false === is_callable($tabAction)) {
        throw new \Exception(sprintf('The controller method "%s" for the Tab "%s" was not found!', $tabAction, $tab));
    }
    $tabAction();
}

function tab_help()
{
    render('Config\tab-help', array('no_layout' => true));
}

function tab_mariadb()
{
    render('Config\tab-mariadb', array('no_layout' => true));
}

function tab_mongodb()
{
    render('Config\tab-mongodb', array('no_layout' => true));
}

function tab_nginx()
{
    render('Config\tab-nginx', array('no_layout' => true));
}

function tab_nginx_domains()
{
    $projects = new Webinterface\Helper\Projects;
    $domains = new Webinterface\Helper\Domains;

    $tpl_data = array(
        'no_layout' => true,
        'project_folders' => $projects->getProjects(true),
        'domains' => $domains->listDomains()
    );

    render('Config\tab-nginx-domains', $tpl_data);
}

function tab_php()
{
    $tpl_data = array(
        'no_layout' => true,
        'php_version_switcher_form' => renderPhpVersionSwitcherForm(),
        'ini' => Webinterface\Helper\PHPINI::read(),
    );

    render('Config\tab-php', $tpl_data);
}

function tab_php_ext()
{
    $phpext = new Webinterface\Helper\PHPExtensionManager();

    $tpl_data = array(
        'no_layout' => true,
        'number_available_extensions' => count($phpext->getExtensionDirFileList()),
        'number_enabled_extensions' => count($phpext->getEnabledExtensionsFromIni()),
        'form' => renderPHPExtensionsFormContent()
    );

    render('Config\tab-phpext', $tpl_data);
}

function tab_xdebug()
{
    $xdebug_installed = Webinterface\Helper\Serverstack::isInstalled('xdebug');

    $tpl_data = array(
        'no_layout' => true,
        'xdebug_installed' => $xdebug_installed,
        'ini_settings' => ($xdebug_installed) ? ini_get_all('xdebug') : array(),
    );

    render('Config\tab-xdebug', $tpl_data);
}

function update_phpextensions()
{
    $extensions = $_POST['extensions'];
    //var_dump($extensions); /* show extensions to enable */

    $extensionManager = new Webinterface\Helper\PHPExtensionManager();

    $enabledExtensions = array_flip($extensionManager->getEnabledExtensionsFromIni());

    $disableTheseExtensions = array_diff($enabledExtensions, $extensions);
    $disableTheseExtensions = array_values($disableTheseExtensions); // re-index

    //var_dump($disableTheseExtensions); /* show extensions to disable */

    foreach ($extensions as $extension) {
        $extensionManager->enable($extension);
    }

    foreach ($disableTheseExtensions as $extension) {
        $extensionManager->disable($extension);
    }

    // prepare response data
    $array = array(
        'enabled_extensions' => $extensions,
        'disabled_extensions' => $disableTheseExtensions,
        'responseText' => 'Extensions updated. Restarting PHP ...'
    );

    // send as JSON
    echo json_encode($array);
}

function renderPHPExtensionsFormContent()
{
    $phpext = new Webinterface\Helper\PHPExtensionManager();
    $available_extensions = $phpext->getExtensionDirFileList();
    $enabled_extensions = $phpext->getEnabledExtensionsFromIni();

    $html_checkboxes = '';
    $i = 1; // start at first element
    $itemsTotal = count($available_extensions); // elements total
    $itemsPerColumn = ceil($itemsTotal/4);

    // use list of available_extensions to draw checkboxes
    foreach ($available_extensions as $name => $file) {
        // if extension is enabled, check the checkbox
        $checked = false;
        if (isset($enabled_extensions[$file])) {
            $checked = true;
        }

        /**
         * Set disabled state of "zend_extension"s (deactivate the checkbox).
         * XDebug, Opcache is not loaded as normal PHP extension, but as a Zend Engine extension.
         */
        $disabled = '';
        if ($name === 'php_xdebug' or $name === 'php_opcache') {
            $disabled = 'disabled';
        }

        // render column opener (everytime on 1 of 12)
        if ($i === 1) {
            $html_checkboxes .= '<div class="form-group">';
        }

        // the input tag is wrapped by the label tag
        $html_checkboxes .= '<label class="checkbox';
        $html_checkboxes .= ($checked === true) ? ' active-element">' : ' not-active-element">';
        $html_checkboxes .= '<input type="checkbox" name="extensions[]" value="'.$file.'" ';
        $html_checkboxes .= ($checked === true) ? 'checked="checked" ' : '';
        $html_checkboxes .=  $disabled.'>';
        $html_checkboxes .= substr($name, 4);
        $html_checkboxes .= '</label>';

        if ($i == $itemsPerColumn or $itemsTotal === 1) {
            $html_checkboxes .= '</div>';
            $i = 0; /* reset column counter */
        }

        $i++;
        $itemsTotal--;
    }

   if (isAjaxRequest() and !isset($_GET['tab'])) {
       echo $html_checkboxes;
   } else {
       return $html_checkboxes;
   }
}

function update_phpini_setting()
{
    $directive = filter_input(INPUT_POST, 'directive');
    $value = filter_input(INPUT_POST, 'value');

    // @todo figure out, if we need to set a ini [section], in order to save the directive correctly?
    // @see IniReaderWriter::set() $section is not used there
    $section = '';

    Webinterface\Helper\PHPINI::setDirective($section, $directive, $value);

    Webinterface\Helper\Daemon::restartDaemon('php');

    echo '<div class="modal"><p class="info">Saved. PHP restarted.</div>';
}

function update_phpversionswitch()
{
    $new_version = filter_input(INPUT_POST, 'new-php-version');

    Webinterface\Helper\PHPVersionSwitch::switchVersion($new_version);

    Webinterface\Helper\Daemon::restartDaemon('php');

    echo '<div class="modal"><p class="info">PHP version switched. PHP restarted.</div>';
}

function renderPhpVersionSwitcherForm()
{
    $versionFolders      = Webinterface\Helper\PHPVersionSwitch::getVersions();
    $currentVersion      = Webinterface\Helper\PHPVersionSwitch::getCurrentVersion();
    $number_php_versions = count($versionFolders);

    $options = '';
    foreach ($versionFolders as $folder) {
        $options .= '<option value="' . $folder['php-version'] . '"';
        $options .= ($folder['php-version'] === $currentVersion) ? ' selected' : '';
        $options .= '>' . $folder['php-version'] . '</option>';
    }

    $html = '<form action="index.php?page=config&action=update_phpversionswitch" method="POST">';
    
    $html .= '<p><select name="new-php-version" size="' . $number_php_versions . '">';
    $html .= $options;
    $html .= '</select></p>';
    
    // show switch button only, if multiple PHP version present
    if ($number_php_versions > 1) {
        $html .= '<div class="right">';
        $html .= '<button class="btn btn-danger" type="reset">Reset</button>';
        $html .= '<button class="btn btn-success" type="submit">Switch</button>';
        $html .= '</div>';
    }
    
    $html .= '</form>';

    return $html;
}
