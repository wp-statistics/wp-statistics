<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Plugin_Installer_Skin;
use Plugin_Upgrader;

class PluginInstaller
{
    public function installPlugin($downloadUrl)
    {
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $upgrader = new Plugin_Upgrader(new Plugin_Installer_Skin());
        $upgrader->install($downloadUrl);
    }

    public function activatePlugin($pluginSlug)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $pluginFile = $pluginSlug . '/' . $pluginSlug . '.php';
        return activate_plugin($pluginFile);
    }

    public function deactivatePlugin($pluginSlug)
    {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $pluginFile = $pluginSlug . '/' . $pluginSlug . '.php';
        return deactivate_plugins($pluginFile);
    }
}
