<?php
/**
 * Geo2 Maps Add-on for NextGEN Gallery Uninstall
 *
 * Uninstalling Geo2 Maps deletes options.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Uninstall
 * @since      2.0.0
 * @author     Pawel Block &lt;pblock@op.pl&gt;
 * @copyright  Copyright (c) 2023, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

// Security check.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'plugin_geo2_maps_options' );
