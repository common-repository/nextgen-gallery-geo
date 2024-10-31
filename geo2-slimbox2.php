<?php
/**
 * Function used to show the Slimbox 2 Lightbox with a typical Bing map or a map
 * in the Worldmap mode.
 *
 * Outputs a Javascript code amending Slimbox 2 default functionality
 * and graphics according to the settings on the Geo2 admin page.
 *
 * @see        bing-map.php, bing-map-worldmap.php
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Lightbox Functions
 * @since      2.0.0
 * @since      2.0.4 Function geo2_maps_slimbox2_options() amended.
 * @author     Pawel Block &lt;pblock@op.pl&gt;
 * @copyright  Copyright (c) 2023, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

// Security: Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function used to show the Slimbox 2 Lightbox with a typical Bing map or a map
 * in the Worldmap mode.
 *
 * @since  2.0.0
 * @since  2.0.4 Capital letters removed from plugin $options keys.
 *
 * @see    function geo2_maps() in bing-map.php, function geo2_maps_worldmap() in bing-map-worldmap.php
 * @param  string $geo2_maps_lightbox Collected pictures info. Refer to geo2_maps_lightbox_data() in bing_map_function.php.
 * @param  array  $options Optional. An array of options.
 * @return string Javascript code.
 */
function geo2_maps_slimbox2_options( $geo2_maps_lightbox, $options ) {
	$s2_options =
		( $options['slimbox2_loop'] === 1 ? 'loop: true, ' : '' )
		. ( $options['slimbox2_overlay_opacity'] !== 0.8 ? 'overlayOpacity: ' . $options['slimbox2_overlay_opacity'] . ', ' : '' )
		. ( $options['slimbox2_scaler'] !== 0.8 ? 'scaler: ' . $options['slimbox2_scaler'] . ', ' : '' )
		. ( $options['slimbox2_initial_width'] !== 250 ? 'initialWidth: ' . $options['slimbox2_initial_width'] . ', ' : '' )
		. ( $options['slimbox2_initial_height'] !== 250 ? 'initialHeight: ' . $options['slimbox2_initial_height'] . ', ' : '' )
		. ( $options['slimbox2_counter_text'] !== 'Image {x} of {y}' ? 'counterText: "' . $options['slimbox2_counter_text'] . '", ' : '' );

	$s2_options = rtrim( $s2_options, ', ' );

	return 'jQuery.slimbox( [' . $geo2_maps_lightbox . '], indexNr, {' . $s2_options . '} );';
}
