<?php

/**
 * Plugin Name: ImportWP - Pods Importer Addon
 * Plugin URI: https://www.importwp.com
 * Description: Allow ImportWP to import Pods.
 * Author: James Collings <james@jclabs.co.uk>
 * Version: 2.1.0
 * Author URI: https://www.importwp.com
 * Network: True
 */

add_action('admin_init', 'iwp_pods_check');

function iwp_pods_requirements_met()
{
    return false === (is_admin() && current_user_can('activate_plugins') &&  (!function_exists('pods_is_plugin_active') || version_compare(IWP_VERSION, '2.14.1', '<')));
}

function iwp_pods_check()
{
    if (!iwp_pods_requirements_met()) {

        add_action('admin_notices', 'iwp_pods_notice');

        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function iwp_pods_setup()
{
    if (!iwp_pods_requirements_met()) {
        return;
    }

    $base_path = dirname(__FILE__);

    require_once $base_path . '/setup.php';

    // Install updater
    if (file_exists($base_path . '/updater.php') && !class_exists('IWP_Updater')) {
        require_once $base_path . '/updater.php';
    }

    if (class_exists('IWP_Updater')) {
        $updater = new IWP_Updater(__FILE__, 'importwp-pods');
        $updater->initialize();
    }
}
add_action('plugins_loaded', 'iwp_pods_setup', 9);

function iwp_pods_notice()
{
    echo '<div class="error">';
    echo '<p><strong>ImportWP - Pods Importer Addon</strong> requires that you have <strong>ImportWP v2.14.1 or newer</strong>, <strong>ImportWP Pro</strong>, and <strong>Pods</strong> installed.</p>';
    echo '</div>';
}
