<h2>Help</h2>

<p>The configuration page is divided into tabs. There is one tab for each of the main software components.
    <br/>
    You will find the following functionality there:
</p>
<dl>
    <dt>Help</dt><dd>The page you are currently reading.</dd>
    <dt>PHP</dt><dd>Provides an editor for modifying the PHP configuration file (php.ini).</dd>
    <dt>PHP Extensions</dt><dd>Shows the list of loaded and all available PHP Extensions for activation or deactivation.</dd>
    <?php if (FEATURE_3 == true) { ?>
    <dt>Nginx</dt><dd>Provides an editor for modifying the NGINX configuration file (nginx.conf).</dd>
    <dt>Nginx Domains</dt><dd>...</dd>
    <dt>MariaDB</dt><dd>Provides an editor for modifying the MariaDB configuration file (my.cnf).</dd>
    <dt>MongoDB</dt><dd>...</dd>
    <?php } ?>
    <dt>Xdebug</dt><dd>Shows the Xdebug configuration in detail.</dd>
</dl>
