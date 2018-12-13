<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - onwards, Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

namespace Webinterface\Helper;

class PHPVersionSwitch
{
    public static function switchVersion($newVersion)
    {
        $targetFolder     = WPNXM_BIN . 'php';
        $newVersionFolder = WPNXM_BIN . 'php-' . $newVersion;
        $oldVersionFolder = WPNXM_BIN . 'php-' . PHP_VERSION;

        if (is_dir($targetFolder) === false) {
            throw new \Exception(sprintf(
                'Folder (%s) is missing. Check your environment.', $targetFolder)
            );
        }

        if (is_dir($oldVersionFolder) === true) {
            throw new \Exception(sprintf(
                'The folder (%s) for the current version already exists.', $oldVersionFolder)
            );
        }

        if (is_dir($newVersionFolder) === false) {
            throw new \Exception(sprintf(
                'You are trying to switch to a PHP version not existing (%s).', $newVersionFolder)
            );
        }

        if (rename($targetFolder, $oldVersionFolder) === false) {
            throw new \Exception(sprintf('Renaming (%s) to (%s) failed.', $targetFolder, $oldVersionFolder));
        }

        if (rename($newVersionFolder, $targetFolder) === false) {
            throw new \Exception(sprintf('Renaming (%s) to (%s) failed.', $newVersionFolder, $targetFolder));
        }

        self::checkForPhpIni($newVersionFolder);

        self::setEnvironmentPath($oldVersionFolder);

        return true;
    }

    /**
     * Check for php.ini file in the PHP folder and activate development ini as fallback.
     * @param string $folder
     */
    public static function checkForPhpIni($folder)
    {
        $ini = $folder.DS.'php.ini';
        $iniDev = $folder.DS.'php.ini-development';

        if(false === is_file($ini) && true === is_file($iniDev)) {
            if (!copy($iniDev, $ini)) {
                echo "copy $iniDev error...\n";
            }
        }
    }

    /**
     * Set Folder to PATH var (with implicit PATH cleanup).
     * @param string $folder
     */
    public static function setEnvironmentPath($folder)
    {
        $paths = explode(';', getenv('path'));

        // remove duplicates (implicit env var cleanup)
        $paths = array_unique($paths);

        // remove "non existing" paths
        foreach($paths as $key => $path) {
            if(!is_dir($path)) {
                unset($paths[$key]);
            }
        }

        $paths[] = $folder;

        putenv('PATH=' . implode(';', $paths));
    }

    public static function getPhpVersionFromFolder($dir)
    {
        if(!is_file($dir . '\php.exe')) {
            return '0.0.0'; // php.exe was not found
        }
            
        $enableErrorLogging = ' -d log_errors=on -d error_log=' . WPNXM_DIR . 'logs\php_error.log';

        $out = shell_exec($dir . '\php.exe -v' . $enableErrorLogging);

        preg_match('#PHP\s(\d+\.\d+\.\d+)\s\(cli\)#', $out, $matches);
                
        return $matches[1];
    }

    public static function getCurrentVersion()
    {
        return self::getPhpVersionFromFolder(WPNXM_BIN . 'php');
    }

    /**
     * First apply an automatic folder name fix on all PHP folders.
     * Then return the now properly versionized PHP folders.
     * @return array
     */
    public static function getVersions()
    {        
        self::renameFoldersVersionized();

        return self::determinePhpVersions();
    }
    
    public static function getPhpFolders()
    {
        return glob(WPNXM_BIN . 'php*', GLOB_ONLYDIR);
    }

    public static function determinePhpVersions()
    {
        $folders = self::getPhpFolders();

        // fetch php version from all php folders
        foreach($folders as $key => $folder) {            
            
            $phpVersion = self::getPhpVersionFromFolder($folder);
          
            $folders[$key] = array(
                'dir' => $folder,
                'php-version' => $phpVersion
            );
        }
        
        return self::removeInvalidFolders($folders);
    }
    
    public static function removeInvalidFolders(array $folders)
    {
        foreach ($folders as $key => $folder){
            if($folder['php-version'] === '0.0.0') {
                unset($folders[$key]);
            }
        }
        return $folders;
    }

    /**
     * Automatically renames all folders starting with "bin\php*" to "php-x.y.z".
     */
    public static function renameFoldersVersionized()
    {
        $folders = self::determinePhpVersions();       
        
        // pop first item, its "bin\php"
        array_shift($folders); 

        // rename all other crude "php" folder names into "php-{version}" folder
        foreach($folders as $key => $folder) {
                                   
            if(false === strpos($folder['dir'], $folder['php-version'])) {
                $newFolderName = WPNXM_BIN . 'php-' . $folder['php-version'];

                // chmod, because the folder might be hidden or write protected
                self::chmodDeep($folder['dir'], 0755);

                rename($folder['dir'], $newFolderName);
            }
        }
    }

    public static function chmodDeep($path, $perms = 0777)
    {
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $item) {
            chmod($item->getPathname(), $perms);
            if ($item->isDir() && !$item->isDot()) {
                self::chmodDeep($item->getPathname());
            }
        }
    }
}
