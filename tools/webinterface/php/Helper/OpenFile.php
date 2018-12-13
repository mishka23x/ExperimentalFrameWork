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

class OpenFile
{
   /**
    * Opens the file (in a background process)
    * @param string $file The file to open.
    */
   public static function openFile($file)
   {
       pclose(popen("start /B notepad ". $file, "r"));
       /*

      // extension check
      if (!class_exists('COM') and !extension_loaded("com_dotnet")) {
          throw new \Exception(
              'The \COM class was not found. The PHP Extension "php_com_dotnet.dll" is required.'
          );
      }

      // file check
      if (false === is_file($file)) {
          throw new \InvalidArgumentException(
              sprintf('File not found: "%s".', $file)
          );
      }

      // tool of choice
      // @todo ask user for the tool, for now open with notepad
      $tool = 'notepad';

       //* Notice, that we are not using exec() here.
       //* Using exec() would leave the page loading, till the executed application window is closed.
       //* Running via WScript.Shell will launch the process in the background.
      $WshShell = new \COM("WScript.Shell");
      $WshShell->run('cmd /c ' . $tool . ' ' . $file, 0, false);
      */
   }
}
