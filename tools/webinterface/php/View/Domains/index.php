<h2 class="heading">Local Nginx Domains</h2>

<div class="cs-message">
    <table class="cs-message-content" style="width: 100%;">
        <tr>
            <td class="bold">Enable/Disable</td>
            <td class="bold">Domain Name</td>
            <td class="bold">Webfolder</td>
            <td class="bold">Config (fullpath)</td>
            <td class="bold">Config (filename)</td>
        </tr>
        <?php
        $html = '';
        foreach ($domains as $domainName => $domainValues) {
            $html .= '<tr>';
            $html .= '<td>' . $domainValues['enabled'] . '</td>';
            $html .= '<td>' . $domainName . '</td>';

            // this 1:1 relationship of domainName to project folder is only a fallback
            // normally the domain.conf points to the correct project folder (server root)
            $projectFolder = WPNXM_WWW_ROOT . $domainName;
            $html .= '<td><a href="' . $projectFolder . '">' . $projectFolder . '</a></td>';

            $html .= '<td>' . $domainValues['fullpath'] . '</td>';
            $html .= '<td>' . $domainValues['filename'] . '</td>';
            $html .= '</tr>';
        }
        echo $html;
        ?>
    </table>
</div>
