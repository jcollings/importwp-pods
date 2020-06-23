<?php

/**
 * Plugin Name: ImportWP - Pods Importer Addon
 * Plugin URI: https://www.importwp.com
 * Description: Allow ImportWP to import Pods.
 * Author: James Collings <james@jclabs.co.uk>
 * Version: 2.0.0
 * Author URI: https://www.importwp.com
 * Network: True
 */

add_action('admin_init', 'iwp_pods_check');

function iwp_pods_requirements_met()
{
    return false === (is_admin() && current_user_can('activate_plugins') &&  (!function_exists('pods_is_plugin_active') || version_compare(IWP_VERSION, '2.0.23', '<')));
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

    require_once $base_path . '/class/autoload.php';
    require_once $base_path . '/setup.php';
}
add_action('plugins_loaded', 'iwp_pods_setup', 9);

function iwp_pods_notice()
{
    echo '<div class="error">';
    echo '<p><strong>ImportWP - Pods Importer Addon</strong> requires that you have <strong>ImportWP PRO v2.0.23 or newer</strong>, and <strong>Pods</strong> installed.</p>';
    echo '</div>';
}
