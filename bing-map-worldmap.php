<?php
/**
 * Main function used to create a Bing map in the Worldmap mode with NextGEN
 * Gallery albums and galleries.
 *
 * Outputs a Javascript code. Creates an embed map with pictures.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Functions
 * @since      2.0.0 Function geo2_maps_lightbox_callback() amended and moved from plugin.php.
 * @since      2.0.7 Function geo2_maps_worldmap() amended.
 * @since      2.0.8 Function geo2_maps_worldmap() amended.
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
 * Main function used to create a typical Bing map with images linked to a selected Lightbox.
 *
 * Similar to geo2_maps() in bing-map.php
 *
 * @since  2.0.0 Moved from plugin.php, amended and supplemented with additional code.
 * @since  2.0.7 Variable $map_output and $geo2_maps_lightbox defined.
 * @since  2.0.8 Exif and GPS data not acquired if not needed.
 *
 * @see    function geo2_maps_data_worldmap() in functions.php
 * @param  array $picture_list An array of pictures data.
 * @param  array $options Optional. An array of options.
 * @return string Javascript code.
 */
function geo2_maps_worldmap( $picture_list, $options = null ) {
	// Get options.
	$default_options    = get_option( 'plugin_geo2_maps_options' );
	$options            = geo2_maps_convert_to_int( wp_parse_args( $options, $default_options ) );
	$map_output         = '';
	$geo2_maps_lightbox = '';

	// Create a random number to make map parameters & functions unique. mid must be a number.
	if ( empty( $options['mid'] ) || ! is_numeric( $options['mid'] ) ) {
		$options['mid'] = wp_rand( 0, 999 );
	}

	foreach ( $picture_list as $picture_data ) {
		// only Fancybox3 uses metadata.
		if ( $options['lightbox'] === 'fancybox3' ) {
			if ( $options['fancybox3_caption'] !== 'no' ) {
				// Get exif-geolocation.
				if ( $options['gps'] === 1 ) {
					$picture_data->gps = geo2_maps_coordinates( $picture_data->image_path );
				}
				// Get exif information ( needed for old galleries, created before ngg stored meta_data!).
				if ( $options['exif'] === 1 ) {
					if ( empty( $picture_data->meta_data ) ) {
						$picture_data = geo2_maps_exif( $picture_data );
					} elseif ( ! empty( $picture_data->meta_data['created_timestamp'] ) ) {
						// NextGEN stores date-time as Unix timestamp using PHP function strtotime().
						if ( is_numeric( $picture_data->meta_data['created_timestamp'] ) ) {
							$picture_data->meta_data['created_timestamp'] = gmdate( 'Y-m-d H:i:s', $picture_data->meta_data['created_timestamp'] );
						}
					}
				}
			}
		}
		// Create data for lightbox.
		$type                = $options['lightbox'];
		$geo2_maps_lightbox .= geo2_maps_lightbox_data( $picture_data, $type );
	}
	// Index number always is 0 for any lightbox. Lightbox should start showing photos from first image in gallery. Currently organized by picture id.
	$map_output .= '	var indexNr = 0;
	';
	// Load Lightbox.
	// Fancybox 1.3.4  used in NextGEN uses old jQuery.
	if ( $options['lightbox'] === 'fancybox' ) {
		include_once 'geo2-fancybox.php';
		$map_output .= geo2_maps_fancybox_options( $geo2_maps_lightbox, $options );
	}

	// Slimbox 2.
	if ( $options['lightbox'] === 'slimbox2' ) {
		include 'geo2-slimbox2.php';
		$map_output .= geo2_maps_slimbox2_options( $geo2_maps_lightbox, $options );
	}

	if ( $options['lightbox'] === 'fancybox3' ) {
		include_once 'geo2-fancybox3-worldmap.php';
		$map_output .= geo2_maps_fancybox3_options_worldmap( $geo2_maps_lightbox, $picture_list, $options );
	}
	return $map_output;
}
