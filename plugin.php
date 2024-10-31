<?php
/**
 * Various functions and hooks used by the plugin when initiating or saving settings.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Administration
 * @since      1.0.0
 * @since      2.0.0 Replaced with different functions.
 * @since      2.0.1 Amended functions: geo2_maps_options_validate(), geo2_maps_defaults_array().
 * @since      2.0.4 Amended function: geo2_maps_defaults_array().
 * @since      2.0.6 Amended function: geo2_maps_defaults_array().
 * @since      2.0.7 File names amended to align with WordPress standards.Amended functions: geo2_maps_defaults_array(), geo2_maps_options_validate().
 * @author     Pawel Block &lt;pblock@op.pl&gt;
 * @copyright  Copyright (c) 2023, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Plugin Name: Geo2 Maps Add-on for NextGEN Gallery
 * Plugin URI:  https://wordpress.org/plugins/nextgen-gallery-geo/
 * Description: Geo2 Maps Add-on for NextGEN Gallery is a flexible plugin, displaying beautiful maps with your photos by using EXIF data or geocoding.
 * Version:     2.0.9
 * Author:      Pawel Block
 * Author URI:  http://www.geo2maps.plus
 * License:     GNLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: ngg-geo2-maps
 */

// Security: Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defines the universal path to Geo2 Maps directory in plugins folder.
define( 'GEO2_MAPS_DIR_URL', plugins_url( '', __FILE__ ) );

// Defines the universal path to WordPress plugins folder.
define( 'GEO2_MAPS_PLUGINS_DIR_URL', plugins_url( '', basename( __DIR__ ) ) );

if ( is_admin() ) {
	/**
 * Includes only on the admin pages.
 *
 * @since 1.0.0
 * @since 2.0.0 Moved from administration.php.
 *
 * @todo  Find a way to include only when opening the Geo2 Maps settings page.
 */
	include_once 'administration.php';
}

/**
 * Includes always.
 */
require_once 'bing-map.php';
require_once 'bing-map-functions.php';
require_once 'functions.php';

/**
 * Convert string integers to actual integers in an array.
 *
 * This function iterates over each element of an array. If an element is a string
 * that represents an integer, it converts the string to an actual integer. All
 * other elements are left unchanged.
 *
 * @since 2.0.7
 *
 * @param array $options The input array with WordPress plugin options.
 * @return array The output array with string integers converted to actual integers.
 */
function geo2_maps_convert_to_int( $options ) {
	// Check if $options is an array needed during activation.
	if ( is_array( $options ) ) {
		return array_map(
			function ( $item ) {
				if ( is_string( $item ) && ctype_digit( $item ) ) {
					return intval( $item );
				}
				return $item;
			},
			$options
		);
	} else {
		return $options;
	}
}
/**
 * Creates an array of default plugin settings.
 *
 * Code run only on plugin activation/deactivation or when settings are saved,
 *
 * @since  1.0.0
 * @since  2.0.0 Part of ngg_geo_options_admin() moved from functions.php.
 * @since  2.0.1 Keys added to $defaults array: geo_bing_auth_status, mapquest_auth_status.
 * @since  2.0.4 Map options added by adding keys: locate_me_button, copyright, terms_link, logo to $defaults array, capital letters removed from $defaults array keys
 * @since  2.0.6 Variables under the "Other options" section used in code added to default settings to prevent warnings.
 * @since  2.0.7 'search' setting removed from defaults array.
 * @see    function geo2_maps_options_validate( $input ), function geo2_maps_options_activation(), function geo2_maps_options_deactivation()
 * @return array
 */
function geo2_maps_defaults_array() {
	// Defines $options defaults.
	$defaults =
	array(
		// 'map_provider'                      => 'bing_map',
		'geo_bing_key'                         => null,
		'geo_bing_auth_status'                 => 0,     // 0 - not activated, 1 - activated
		'mapquest_key'                         => null,
		'mapquest_auth_status'                 => 0,      // 0 - not activated, 1 - activated
		'user_email'                           => null,
		'geocoding_provider'                   => 'bing', // mapquest, openstreetmaps.
		'zoom'                                 => '16',
		'map_height'                           => '300px',
		'map_width'                            => '100%',
		'map'                                  => 'aerial', // Road/Aerial/Canvas Light/Canvas Dark/Grayscale/Ordinance Survey.
		'custom_map'                           => null,     // Code to customize map TBC.
		'bev'                                  => 0,        // Birds Eye View.
		'thumb'                                => 1,        // 1 - thumbs, 2 - pushpins
		'thumb_title'                          => 0,
		'thumb_height'                         => 100,
		'thumb_width'                          => 100,
		'thumb_radius'                         => 50,
		'thumb_shape'                          => 'rect', // Thumbnail shape: "rect" or  "round".
		'thumb_border'                         => 4, // width in px.
		'thumb_border_color'                   => 'rgba(255,255,255,1 )',
		'lightbox'                             => 'fancybox3', // fancybox / fancybox3 / slimbox2 /infobox/ no.
		'open_lightbox'                        => 0, // Worldmap option to enable Infobox/Lightbox.
		'url_link'                             => 0, // 0/1 Enable/Disable url link in WP Media Library
		'url_link_type'                        => 'same_tab', // URL link options: same_tab / new_tab / iframe.
		'infobox_width'                        => null,
		'infobox_height'                       => null,
		'infobox_color'                        => 'rgba(0,0,0,0.7)', // Background color.
		'infobox_text_color'                   => '#fff', // Text color.
		'infobox_title_over'                   => 0,      // 0 = false, 1 = true.
		'gallery_title'                        => 1,
		'exif'                                 => 1,
		'gps'                                  => 1,
		'route'                                => 0,      // 1 = activate 0= deactivate Route mode
		'route_width'                          => 5,
		'route_color'                          => null,
		'route_polygon_fillcolor'              => null,
		'route_polygon_width'                  => 5,
		'route_polygon_color'                  => null,

		'xmlurl'                               => null,
		'auto_mode'                            => 1,        // 1 - Auto Mode activated
		'top_bottom'                           => 1,        // Auto map placed on pages: top -> 0, bottom -> 1.
		'auto_include'                         => 'all_auto', // Auto Mode option to create a map only for:
															// Albums from Albums  => albums
															// Galleries from Albums  => galleries
															// Albums and Galleries from Albums  => all_albums
															// Images from Galleries  => images
															// All from Albums and if no albums images from Galleries => all_auto.
		'include'                              => 'galleries', // Specifies content of a Worldmap: galleries, albums, all.
		// MAP OPTIONS.
		'dashboard'                            => 1, // Shows/hides map navigation controls.
		'locate_me_button'                     => 1, // Shows/hides Locate Me button in the map's navigation controls.
		'scalebar'                             => 1, // Shows/hides scalebar from the map.
		'copyright'                            => 1, // Shows/hides copyrights info at the bottom of the page.
		'terms_link'                           => 1, // Shows/hides TOU link on the right of the copyright info when it is enabled.
		'logo'                                 => 1, // Shows/hides BING logo in the left bottom corner.
		'minimap'                              => 0,
		'minimap_type'                         => 'same', // Same/Road/Aerial/Canvas Light/CanvasDark/Grayscale/Ordinance Survey.
		'minimap_show_at_start'                => 0,
		'minimap_height'                       => 150,
		'minimap_width'                        => 150,
		'minimap_top_offset'                   => 0,
		'minimap_side_offset'                  => 0,
		// PINS OPTIONS.
		'pin_color'                            => 'rgba(0, 255, 0, 1 )', // Pins for images. Color of the main pin on a map.
		'pin_gal_color'                        => 'rgba(255, 0, 0, 1 )', // Pins for galleries. Color of th main pin on the side panel map.
		'pin_alb_color'                        => 'rgba(255, 0, 0, 1 )', // Pins for albums. Color of th main pin on the side panel map.
		'restore_defaults'                     => 0,
		'show_wp_caption'                      => 0,    // Shows or not caption below the pins/thumbnails.
		// FANCYBOX 3 OPTIONS.
		'fancybox3_caption'                    => 'no', // Caption appears on the bottom. values: no / bottom.
		'fancybox3_prevent_caption_overlap'    => 1,    // Should allow caption to overlap the content.
		'fancybox3_colors_override'            => 0,
		'fancybox3_background'                 => 'rgba(30,30,30,0.9)',
		'fancybox3_caption_text_color'         => '#eee',
		'fancybox3_thumbs_background'          => 'rgba(0,0,0,0.3)',
		'fancybox3_buttons_background'         => 'rgba(30,30,30,0.6)',
		'fancybox3_buttons_color'              => '#cccccc', // Optional: rgba(204,204,204,1 ).
		'fancybox3_buttons_color_hover'        => '#ffffff', // Optional: rgba(255,255,255,1 ).
		'fancybox3_thumbs_active_border_color' => '#52bfff', // Original #ff5268.
		'fancybox3_thumbs_autostart'           => 0, // 0 - no / 1 - Display thumbnails preview on opening
		'fancybox3_loop'                       => 1, // Enable infinite gallery navigation.
		'fancybox3_arrows'                     => 1, // Should display navigation arrows.
		'fancybox3_infobar'                    => 1, // Should display counter at the top left corner.
		'fancybox3_close_btn'                  => 'auto', // Should display close button (using `btnTpl.smallBtn` template ) over the content. Can be true, false, "auto". If "auto" - will be automatically enabled for "html", "inline" or "ajax" items.
		'fancybox3_toolbar'                    => 'auto', // Should display toolbar ( buttons at the top ). Can be true, false, "auto". If "auto" - will be automatically hidden if "closeBtn" is enabled.
		'fancybox3_buttons_zoom'               => 1,
		'fancybox3_buttons_share'              => 0,
		'fancybox3_buttons_slideshow'          => 1,
		'fancybox3_buttons_fullScreen'         => 1,
		'fancybox3_buttons_download'           => 0,
		'fancybox3_buttons_thumbs'             => 1,
		'fancybox3_buttons_close'              => 1,
		'fancybox3_protect'                    => 0,    // Disable right-click and use simple image protection for images.
		'fancybox3_slideshow_autostart'        => 0,
		'fancybox3_slideshow_speed'            => 3000, // In ms.
		'fancybox3_fullscreen_autostart'       => 0,
		'fancybox3_lang'                       => 'en', // languages available: en, pl, de.
		// SLIMBOX 2 OPTIONS.
		'slimbox2_loop'                        => 1,    // Allows the user to navigate between the first and last image.
		'slimbox2_scaler'                      => 0.8,  // Scale factor to use when auto-resizing the image to fit in browser - only version amended 2.04 , not official.
		'slimbox2_overlay_opacity'             => 0.8,  // Opacity of the background overlay. 1 Is opaque, 0 is transparent.
		'slimbox2_initial_width'               => 250,  // The initial width of the box, in pixels.
		'slimbox2_initial_height'              => 250,  // The initial height of the box, in pixels.
		'slimbox2_counter_text'                => 'Image {x} of {y}', // Default = "Image {x} of {y}"/ "Photo {x} of {y} / "{x}/{y}" / false  or "" to disable.

		// FANCYBOX OPTIONS.
		'fancybox_show_close_button'           => 1,
		'fancybox_show_nav_arrows'             => 1,
		'fancybox_padding'                     => 0,      // Def: 10/ Space between FancyBox wrapper and content.
		'fancybox_margin'                      => 0,      // Def: 20/ Space between viewport and FancyBox wrapper.
		'fancybox_overlay_opacity'             => 0.3,    // Opacity of the overlay ( from 0 to 1; default - 0.3).
		'fancybox_overlay_color'               => '#666', // Color of the overlay.
		'fancybox_auto_scale'                  => 1,      // If true, FancyBox is scaled to fit in viewport.
		'fancybox_cyclic'                      => 1,      // When true, galleries will be cyclic, allowing you to keep pressing next/back.
		'fancybox_title_show'                  => 1,      // Toggle title. true or false.
		'fancybox_title_position'              => 'over', // The position of title. Can be set to 'outside', 'inside' or 'over'.
		'load_ajax'                            => 0,      // Show ajax link or map.
		'ajax'                                 => null,   // Show ajax link or map.
		// Other options.
		'id'                                   => null,   // Gallery id - accepts numbers divided by "," i.e id=12,16.
		'pid'                                  => null,   // Picture id - accepts numbers divided by ",".
		'worldmap'                             => 0,      // 1 = shows Worldmap;
		'status'                               => null,   // Status: which mode is used actual? ( auto, worldmap ).
	);
	return $defaults;
}

register_activation_hook( __FILE__, 'geo2_maps_options_activation' );
/**
 * Runs on plugin activation and adds default options to MySQL database.
 *
 * @since 2.0.0
 *
 * @see   function geo2_maps_defaults_array()
 */
function geo2_maps_options_activation() {
	// Security check.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$defaults = geo2_maps_defaults_array();

	if ( ! get_option( 'plugin_geo2_maps_options' ) ) {
		add_option( 'plugin_geo2_maps_options', $defaults );
	}
}

register_deactivation_hook( __FILE__, 'geo2_maps_options_deactivation' );
/**
 * Runs on plugin deactivation and conditionally restores plugins default options.
 *
 * @since 2.0.0
 *
 * @see   function geo2_maps_defaults_array().
 */
function geo2_maps_options_deactivation() {
	// Security check.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$defaults = geo2_maps_defaults_array();
	$options  = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );

	if ( $options['restore_defaults'] === 1 ) {
		update_option( 'plugin_geo2_maps_options', $defaults );
	}
}

/**
 * Validates color
 *
 * Checks if string representing a color format is correct.
 *
 * @since  2.0.0
 * @since  2.0.7 Function compacted.
 *
 * @see      function geo2_maps_options_validate() below, function geo2_maps_shortcodes_ajax()
 * @param  string $text Color value.
 * @return string
 */
function geo2_maps_validate_color( $text ) {
	$error_message = '';
	if ( substr( $text, 0, 1 ) === '#' ) {
		$trimmed_text = ltrim( $text, '#' );
		if ( strlen( $trimmed_text ) !== 3 && strlen( $trimmed_text ) !== 6 ) {
			$error_message = esc_html__( ' is not a valid hex color. Please enter i.e. #000 or #9900ff!', 'ngg-geo2-maps-plus' );
			$color         = '#ccc';
		} else {
			$color = sanitize_hex_color( $text );
		}
	} elseif ( substr( $text, 0, 4 ) === 'rgba' || substr( $text, 0, 4 ) === 'rgb(' ) {
		// PHP trim() function removes a set of characters, not specific string.
		$trimmed_text   = rtrim( ltrim( $text, 'rgba(' ), ' )' );
		$color_no_array = explode( ',', $trimmed_text );
		foreach ( $color_no_array as $number ) {
			$number = trim( $number ); // Removes whitespace.
			if ( ! is_numeric( $number ) || strlen( $number ) === 0 || strlen( $number ) > 4 ) {
				$error_message = esc_html__( ' Please enter a valid RGB(A) color!', 'ngg-geo2-maps-plus' );
				$color         = 'rgba(0,0,0,1)';
				break;
			} else {
				$color = $text;
			}
		}
	} else {
		$error_message = esc_html__( ' is not a valid color code. Please enter a correct hex or RGB(A) color!', 'NGG-gallery-geo2' );
	}

	if ( ! empty( $error_message ) ) {
		add_settings_error( 'plugin_geo2_maps', 'invalid_color_number_error', $text . $error_message, 'error' );
	}
	return $color;
}

/**
 * Validates options ( administration )
 *
 * Runs when settings are registered - saved to MySQL Database
 *
 * @since  1.0.0
 * @since  2.0.0 Moved from functions.php, renamed and split.
 * @since  2.0.1 Bing an Mapquest API Keys validation added.
 * @since  2.0.4 New keys added under "Clears undefined checkboxes", capital letters removed from $input array keys.
 * @since  2.0.7 Reference to undefined variable removed. Function geo2_maps_validate_url() now returns empty string.
 * @see      function geo2_maps_options_init() in administration.php, function geo2_maps_defaults_array()
 * @param  mixed[] $input Array of option values.
 * @return array
 */
function geo2_maps_options_validate( $input ) {
	// Gets variable values already saved on the server.
	$saved_options = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );

	// Invalid characters in API key for validation below.
	$special_chars = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', '’', '«', '»', '”', '“', chr( 0 ), '<', '>', '.', ',' );

	// Sample query - location to check if a Bing or MapQuest key is working.
	$query = 'London';

	// Checks if API keys are already validated and saved to avoid overriding by default value.
	if ( $saved_options['geo_bing_auth_status'] === 1 ) {
		$input['geo_bing_auth_status'] = 1;
	}

	if ( $saved_options['mapquest_auth_status'] === 1 ) {
		$input['mapquest_auth_status'] = 1;
	}

	// Validates and sanitizes Bing API Key.
	if ( strlen( $input['geo_bing_key'] ) !== 0 ) {
		if ( $saved_options['geo_bing_key'] !== $input['geo_bing_key'] ) {
			$check = 0;
			foreach ( $special_chars as $char ) {
				if ( strpos( $input['geo_bing_key'], $char ) !== false ) {
					$check = 1;
				}
			}
			if ( $check === 1 ) {
				add_settings_error( 'plugin_geo2_maps', 'invalid_API_key_error', esc_html__( 'Please enter a valid API key! Special characters are not allowed.', 'ngg-geo2-maps' ), 'error' );
				$input['geo_bing_key']         = '';
				$input['geo_bing_auth_status'] = 0;
			} else {
				// Sends sample query to Bing REST server to validate credentials.
				// URL of Bing Maps REST Services Locations API.
				$base_url = 'http://dev.virtualearth.net/REST/v1/Locations';
				// Construct the final Locations API URI.
				$url = $base_url . '/' . $query . '?output=json&maxResults=1&key=' . $input['geo_bing_key'];
				// Gets the response from the Locations API and store it in a string.
				$response = wp_remote_get( $url );
				// Get the body of the response.
				$jsonfile = wp_remote_retrieve_body( $response );
				// Decode the json.
				if ( ! json_decode( $jsonfile, true ) ) {
					add_settings_error( 'plugin_geo2_maps', 'Bing API key validation.', esc_html__( 'API key validation request failed when trying to decode Bing Maps server response!', 'ngg-geo2-maps' ), 'error' );
					$input['geo_bing_auth_status'] = 0;
				} else {
					$response = json_decode( $jsonfile, true );
					// Extract Status Code.
					if ( $response['statusCode'] !== 0 ) {
						$status_codes          = array(
							200 => 'OK - The request is successful',
							201 => 'Created	- A new resource is created',
							202 => 'Accepted - The request has been accepted for processing',
							400 => 'Bad Request	- The request contained an error',
							401 => 'Unauthorized - Access was denied. You may have entered your credentials incorrectly, or you might not have access to the requested resource or operation',
							403 => 'Forbidden - The request is for something forbidden. Authorization will not help',
							404 => 'Not Found - The requested resource was not found',
							429 => 'Too Many Requests	The user has sent too many requests in a given amount of time. The account is being rate limited',
							500 => 'Internal Server Error	Your request could not be completed because there was a problem with the service',
							503 => 'Service Unavailable	There\'s a problem with the service right now. Please try again later',
						);
						$status_code           = $response['statusCode'];
						$authentication_result = $response['authenticationResultCode'];

						if ( $authentication_result === 'ValidCredentials' ) {
							add_settings_error( 'plugin_geo2_maps', 'Bing API key validation error.', esc_html__( 'Server response:', 'ngg-geo2-maps' ) . ' ' . $status_codes[ $status_code ] . '. ( ' . esc_html__( 'Status Code:', 'ngg-geo2-maps' ) . ' ' . $status_code . ' )', 'info' );
							add_settings_error( 'plugin_geo2_maps', 'Bing API key validation.', esc_html__( 'Bing Maps API key validation successful! You can start using Geo2 Maps.', 'ngg-geo2-maps' ), 'success' );
							$input['geo_bing_auth_status'] = 1;
						} else {
							add_settings_error( 'plugin_geo2_maps', 'Bing API key validation error.', esc_html__( 'Bing API key validation unsuccessful!', 'ngg-geo2-maps' ) . ' ' . esc_html__( 'Server response:', 'ngg-geo2-maps' ) . ' ' . $status_codes[ $status_code ] . '. ( ' . esc_html__( 'Status Code:', 'ngg-geo2-maps' ) . ' ' . $status_code . ' )', 'error' );
							$input['geo_bing_auth_status'] = 0;
						}
					} else {
						add_settings_error( 'plugin_geo2_maps', 'Bing API key validation error.', esc_html__( 'Bing API key validation unsuccessful!', 'ngg-geo2-maps' ), 'error' );
						$input['geo_bing_auth_status'] = 0;
					}
				}
			}
		}
	} else {
		$input['geo_bing_auth_status'] = 0;
	}

	// Validates and sanitizes Mapquest API Key.
	if ( strlen( $input['mapquest_key'] ) !== 0 ) {
		if ( $saved_options['mapquest_key'] !== $input['mapquest_key'] ) {
			$check = 0;
			foreach ( $special_chars as $char ) {
				if ( strpos( $input['mapquest_key'], $char ) !== false ) {
					$check = 1;
				}
			}
			if ( $check === 1 ) {
				add_settings_error( 'plugin_geo2_maps', 'invalid_API_key_error', esc_html__( 'Please enter a valid API key! Special characters are not allowed.', 'ngg-geo2-maps' ), 'error' );
				$input['mapquest_key']         = '';
				$input['mapquest_auth_status'] = 0;
			} else {
				$url      = 'http://www.mapquestapi.com/geocoding/v1/address?key=' . $input['mapquest_key'] . '&outFormat=json&maxResults=1&location=' . rawurlencode( $query );
				$response = wp_remote_get( $url );
				// Get the body of the response.
				$jsonfile = wp_remote_retrieve_body( $response );
				// Decode the json.
				if ( ! json_decode( $jsonfile, true ) ) {
					add_settings_error( 'plugin_geo2_maps', 'MapQuest API key validation.', esc_html__( 'API key validation request failed when trying to decode Mapquest server response!', 'ngg-geo2-maps' ) . ' ' . esc_html__( 'Server response:', 'ngg-geo2-maps' ) . ' ' . $jsonfile, 'error' );
					$input['mapquest_key']         = '';
					$input['mapquest_auth_status'] = 0;
				} else {
					$response = json_decode( $jsonfile, true );
					if ( isset( $response['info']['statuscode'] ) ) {
						$status_code = $response['info']['statuscode'];
						$message     = $response['info']['message'];

						if ( $status_code === 0 ) {
							add_settings_error( 'plugin_geo2_maps', 'MapQuest API key validation error.', esc_html__( 'Server response:', 'ngg-geo2-maps' ) . ' A successful geocode call. ( ' . esc_html__( 'Status Code:', 'ngg-geo2-maps' ) . ' ' . $status_code . ' )', 'info' );
							add_settings_error( 'plugin_geo2_maps', 'MapQuest API key validation.', esc_html__( 'API key validation successful! You can start using geocoding with MapQuest service.', 'ngg-geo2-maps' ), 'success' );
							$input['mapquest_auth_status'] = 1;
						} else {
							add_settings_error( 'plugin_geo2_maps', 'MapQuest API key validation error.', esc_html__( 'MapQuest API key validation unsuccessful!', 'ngg-geo2-maps' ) . ' ' . esc_html__( 'Server response:', 'ngg-geo2-maps' ) . ' ' . $message . '. ( ' . esc_html__( 'Status Code:', 'ngg-geo2-maps' ) . ' ' . $status_code . ' )', 'error' );
							$input['mapquest_key']         = '';
							$input['mapquest_auth_status'] = 0;
						}
					} else {
						add_settings_error( 'plugin_geo2_maps', 'MapQuest API key validation error.', esc_html__( 'MapQuest API key validation unsuccessful!', 'ngg-geo2-maps' ), 'error' );
						$input['mapquest_key']         = '';
						$input['mapquest_auth_status'] = 0;
					}
				}
			}
		}
	} else {
		$input['mapquest_auth_status'] = 0;
	}

	// Clears undefined checkboxes (undefined = unchecked!).
	if ( ! isset( $input['bev'] ) ) {
		$input['bev'] = 0; }
	if ( ! isset( $input['gallery_title'] ) ) {
		$input['gallery_title'] = 0; }
	if ( ! isset( $input['exif'] ) ) {
		$input['exif'] = 0; }
	if ( ! isset( $input['gps'] ) ) {
		$input['gps'] = 0; }
	if ( ! isset( $input['route'] ) ) {
		$input['route'] = 0; }
	if ( ! isset( $input['auto_mode'] ) ) {
		$input['auto_mode'] = 0; }
	if ( ! isset( $input['dashboard'] ) ) {
		$input['dashboard'] = 0; }
	if ( ! isset( $input['locate_me_button'] ) ) {
		$input['locate_me_button'] = 0; }
	if ( ! isset( $input['scalebar'] ) ) {
		$input['scalebar'] = 0; }
	if ( ! isset( $input['copyright'] ) ) {
		$input['copyright'] = 0; }
	if ( ! isset( $input['terms_link'] ) ) {
		$input['terms_link'] = 0; }
	if ( ! isset( $input['logo'] ) ) {
		$input['logo'] = 0; }
	if ( ! isset( $input['minimap'] ) ) {
		$input['minimap'] = 0; }
	if ( ! isset( $input['minimap_show_at_start'] ) ) {
		$input['minimap_show_at_start'] = 0; }
	if ( ! isset( $input['thumb_title'] ) ) {
		$input['thumb_title'] = 0; }
	if ( ! isset( $input['restore_defaults'] ) ) {
		$input['restore_defaults'] = 0; }
	if ( ! isset( $input['thumb_wp_caption'] ) ) {
		$input['thumb_wp_caption'] = 0; }
	if ( ! isset( $input['open_lightbox'] ) ) {
		$input['open_lightbox'] = 0; }
	if ( ! isset( $input['infobox_title_over'] ) ) {
		$input['infobox_title_over'] = 0; }
	if ( ! isset( $input['fancybox3_colors_override'] ) ) {
		$input['fancybox3_colors_override'] = 0; }
	if ( ! isset( $input['fancybox3_prevent_caption_overlap'] ) ) {
		$input['fancybox3_prevent_caption_overlap'] = 0; }
	if ( ! isset( $input['fancybox3_thumbs_autostart'] ) ) {
		$input['fancybox3_thumbs_autostart'] = 0; }
	if ( ! isset( $input['fancybox3_loop'] ) ) {
		$input['fancybox3_loop'] = 0; }
	if ( ! isset( $input['fancybox3_arrows'] ) ) {
		$input['fancybox3_arrows'] = 0; }
	if ( ! isset( $input['fancybox3_infobar'] ) ) {
		$input['fancybox3_infobar'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_zoom'] ) ) {
		$input['fancybox3_buttons_zoom'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_share'] ) ) {
		$input['fancybox3_buttons_share'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_slideshow'] ) ) {
		$input['fancybox3_buttons_slideshow'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_fullScreen'] ) ) {
		$input['fancybox3_buttons_fullScreen'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_download'] ) ) {
		$input['fancybox3_buttons_download'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_thumbs'] ) ) {
		$input['fancybox3_buttons_thumbs'] = 0; }
	if ( ! isset( $input['fancybox3_buttons_close'] ) ) {
		$input['fancybox3_buttons_close'] = 0; }
	if ( ! isset( $input['fancybox3_protect'] ) ) {
		$input['fancybox3_protect'] = 0; }
	if ( ! isset( $input['fancybox3_slideshow_autostart'] ) ) {
		$input['fancybox3_slideshow_autostart'] = 0; }
	if ( ! isset( $input['fancybox3_fullscreen_autostart'] ) ) {
		$input['fancybox3_fullscreen_autostart'] = 0; }
	if ( ! isset( $input['slimbox2_loop'] ) ) {
		$input['slimbox2_loop'] = 0; }
	if ( ! isset( $input['fancybox_show_close_button'] ) ) {
		$input['fancybox_show_close_button'] = 0; }
	if ( ! isset( $input['fancybox_show_nav_arrows'] ) ) {
		$input['fancybox_show_nav_arrows'] = 0; }
	if ( ! isset( $input['fancybox_cyclic'] ) ) {
		$input['fancybox_cyclic'] = 0; }
	if ( ! isset( $input['fancybox_title_show'] ) ) {
		$input['fancybox_title_show'] = 0; }
	if ( ! isset( $input['fancybox_auto_scale'] ) ) {
		$input['fancybox_auto_scale'] = 0; }

	if ( ! isset( $input['load_ajax'] ) ) {
		$input['load_ajax'] = 0; }

	/**
	 * Validate a text value and return it if valid, or default value if not.
	 *
	 * This function checks if the text is 'auto', a valid pixel value, or a valid
	 * percentage. If the text is not valid, it adds an error message and returns
	 * the default value.
	 *
	 * @param string $text The text to validate.
	 * @param int    $max The maximum valid pixel value.
	 * @param mixed  $default_value The default value to return if the text is not valid.
	 * @return mixed The validated text or the default value.
	 */
	function geo2_maps_validate_auto_number( $text, $max, $default_value ) {
		$error_message = sprintf(
			/* translators: %s: maximum pixel value */
			esc_html__( 'Please enter a number from 24 to %s, percentage 1-100%% or "auto"!', 'ngg-geo2-maps' ),
			$max
		);

		if ( $text === 'auto' ) {
			return $text;
		} elseif ( strpos( $text, 'px' ) !== false ) {
			$string = str_replace( 'px', '', $text );
			if ( ! is_numeric( $string ) ) {
				add_settings_error( 'plugin_geo2_maps', 'invalid_number_error', $error_message, 'error' );
				return $default_value;
			} elseif ( $string >= 24 && $string <= $max ) {
				return $text;
			} else {
				add_settings_error( 'plugin_geo2_maps', 'invalid_number_error', $error_message, 'error' );
				return $default_value;
			}
		} elseif ( strpos( $text, '%' ) !== false ) {
			$string = str_replace( '%', '', $text );
			if ( ! is_numeric( $string ) ) {
				add_settings_error( 'plugin_geo2_maps', 'invalid_number_error', $error_message, 'error' );
				return $default_value;
			} elseif ( $string > 0 && $string <= 100 ) {
				return $text;
			} else {
				add_settings_error( 'plugin_geo2_maps', 'invalid_number_error', esc_html__( 'Please enter a number from 0% to 100%!', 'ngg-geo2-maps' ), 'error' );
				return $default_value;
			}
		} else {
			add_settings_error( 'plugin_geo2_maps', 'invalid_number_error', $error_message, 'error' );
			return $default_value;
		}
	}

	// Validates options.
	if ( strlen( $input['zoom'] ) !== 0 && $saved_options['zoom'] !== $input['zoom'] ) {
		if ( ! is_numeric( $input['zoom'] ) || $input['zoom'] > 19 || $input['zoom'] < 1 ) {
			unset( $input['zoom'] );
			add_settings_error( 'plugin_geo2_maps', 'invalid_zoom_number_error', esc_html__( 'Please enter a valid number for Zoom Level in the range 1-19!', 'ngg-geo2-maps' ), 'error' );
		}
	}

	if ( strlen( $input['route_width'] ) !== 0 && $saved_options['route_width'] !== $input['route_width'] ) {
		if ( ! is_numeric( $input['route_width'] ) || $input['route_width'] > 50 || $input['route_width'] < 1 ) {
			unset( $input['route_width'] );
			add_settings_error( 'plugin_geo2_maps', 'invalid_polyline_width_error', esc_html__( 'Please enter a valid Routes Polyline Width in the range 1-50!', 'ngg-geo2-maps' ), 'error' );
		}
	}

	if ( strlen( $input['route_polygon_width'] ) !== 0 && $saved_options['route_polygon_width'] !== $input['route_polygon_width'] ) {
		if ( ! is_numeric( $input['route_polygon_width'] ) || $input['route_polygon_width'] > 50 || $input['route_polygon_width'] < 1 ) {
			unset( $input['route_polygon_width'] );
			add_settings_error( 'plugin_geo2_maps', 'invalid_polyline_polygon_width_error', esc_html__( 'Please enter a valid Routes Polygon Edge Width in the range 1-50!', 'ngg-geo2-maps' ), 'error' );
		}
	}

	if ( strlen( $input['map_height'] ) !== 0 && $saved_options['map_height'] !== $input['map_height'] ) {
		if ( is_numeric( $input['map_height'] ) && $input['map_height'] <= 4320 && $input['map_height'] >= 24 ) {
			$input['map_height'] = strval( $input['map_height'] ) . 'px';
		} else {
			$input['map_height'] = geo2_maps_validate_auto_number( $input['map_height'], 4320, '300px' );
		}
	}

	if ( strlen( $input['map_width'] ) !== 0 && $saved_options['map_width'] !== $input['map_width'] ) {
		if ( is_numeric( $input['map_width'] ) && $input['map_width'] <= 7680 && $input['map_width'] >= 24 ) {
			$input['map_width'] = strval( $input['map_width'] ) . 'px';
		} else {
			$input['map_width'] = geo2_maps_validate_auto_number( $input['map_width'], 7680, '400px' );
		}
	}

	/**
	 * Validate a text value and return it if valid, or default value if not.
	 *
	 * This function checks if the text is a valid pixel value within the specified range.
	 * If the text is not valid, it adds an error message and returns
	 * the default value.
	 *
	 * @param string $input The text to validate.
	 * @param int    $min The minimum valid pixel value.
	 * @param int    $max The maximum valid pixel value.
	 * @param mixed  $default_value The default value to return if the text is not valid.
	 * @param string $param_name The name of the parameter being validated.
	 * @return mixed The validated text or the default value.
	 */
	function geo2_maps_validate_pos_no( $input, $min, $max, $default_value, $param_name ) {
		if ( strlen( $input ) !== 0 ) {
			if ( ! is_numeric( $input ) || $input < $min || $input > $max ) {
				$error_message = sprintf(
					/* translators: 1: Parameter name 2: Minimum pixel value 3: Maximum pixel value */
					esc_html__( 'For parameter "%1$s" please enter a number ≥ %2$s and ≤ %3$s!', 'ngg-geo2-maps' ),
					$param_name,
					$min,
					$max
				);
				if ( strpos( $input, 'px' ) !== false ) {
					$string = str_replace( 'px', '', $input );
					if ( ! is_numeric( $string ) ) {
						add_settings_error( 'plugin_geo2_maps', 'invalid_number_in_px_error', $error_message, 'error' );
						return $default_value;
					} elseif ( $string >= $min && $string <= $max ) {
						return $string;
					} else {
						add_settings_error( 'plugin_geo2_maps', 'invalid_number_error', $error_message, 'error' );
						return $default_value;
					}
				} else {
					add_settings_error( 'plugin_geo2_maps', 'invalid_number_in_px_error', $error_message, 'error' );
					return $default_value;
				}
			} else {
				return $input; }
		} else {
			return $default_value; }
	}

	// Minimap.
	$input['minimap_width']       = geo2_maps_validate_pos_no( $input['minimap_width'], 24, 4320, 150, 'minimap_width' );
	$input['minimap_height']      = geo2_maps_validate_pos_no( $input['minimap_height'], 24, 4320, 150, 'minimap_height' );
	$input['minimap_top_offset']  = geo2_maps_validate_pos_no( $input['minimap_top_offset'], 0, 4320, 0, 'minimap_top_offset' );
	$input['minimap_side_offset'] = geo2_maps_validate_pos_no( $input['minimap_side_offset'], 0, 4320, 0, 'minimap_side_offset' );
	// Thumbs.
	$input['thumb_width']  = geo2_maps_validate_pos_no( $input['thumb_width'], 1, 500, 100, 'thumb_width' );
	$input['thumb_height'] = geo2_maps_validate_pos_no( $input['thumb_height'], 1, 500, 100, 'thumb_height' );
	$input['thumb_radius'] = geo2_maps_validate_pos_no( $input['thumb_radius'], 1, 500, 50, 'thumb_radius' );
	$input['thumb_border'] = geo2_maps_validate_pos_no( $input['thumb_border'], 0, 300, 4, 'thumb_border' );
	// Infobox.
	$input['infobox_width']  = geo2_maps_validate_pos_no( $input['infobox_width'], 0, 4320, null, 'infobox_width' );
	$input['infobox_height'] = geo2_maps_validate_pos_no( $input['infobox_height'], 0, 4320, null, 'infobox_height' );
	// Fancybox.
	$input['fancybox_padding']         = geo2_maps_validate_pos_no( $input['fancybox_padding'], 0, 1000, 0, 'fancybox_padding' );
	$input['fancybox_margin']          = geo2_maps_validate_pos_no( $input['fancybox_margin'], 0, 1000, 0, 'fancybox_margin' );
	$input['fancybox_overlay_opacity'] = geo2_maps_validate_pos_no( $input['fancybox_overlay_opacity'], 0, 1, 0.3, 'fancybox_overlay_opacity' );
	// Fancybox 3.
	$input['fancybox3_slide_showspeed'] = geo2_maps_validate_pos_no( $input['fancybox3_slideshow_speed'], 0, 86400000, 3000, 'fancybox3_slideshow_speed' );
	// Slimbox 2.
	$input['slimbox2_overlay_opacity'] = geo2_maps_validate_pos_no( $input['slimbox2_overlay_opacity'], 0, 1, 0.8, 'slimbox2_overlay_opacity' );
	$input['slimbox2_scaler']          = geo2_maps_validate_pos_no( $input['slimbox2_scaler'], 0, 1, 0.8, 'slimbox2_scaler' );
	$input['slimbox2_initial_width']   = geo2_maps_validate_pos_no( $input['slimbox2_initial_width'], 10, 4320, 250, 'slimbox2_initial_width' );
	$input['slimbox2_initial_height']  = geo2_maps_validate_pos_no( $input['slimbox2_initial_height'], 10, 4320, 250, 'slimbox2_initial_height' );

	// Validates colors.
	$colors = array(
		$input['route_color']                          => 'route_color',
		$input['route_polygon_fillcolor']              => 'route_polygon_fillcolor',
		$input['route_polygon_color']                  => 'route_polygon_color',
		$input['pin_color']                            => 'pin_color',
		$input['pin_gal_color']                        => 'pin_gal_color',
		$input['pin_alb_color']                        => 'pin_alb_color',
		$input['thumb_border_color']                   => 'thumb_border_color',
		$input['infobox_color']                        => 'infobox_color',
		$input['infobox_text_color']                   => 'infobox_text_color',
		$input['fancybox3_background']                 => 'fancybox3_background',
		$input['fancybox3_caption_text_color']         => 'fancybox3_caption_text_color',
		$input['fancybox3_thumbs_background']          => 'fancybox3_thumbs_background',
		$input['fancybox3_buttons_background']         => 'fancybox3_buttons_background',
		$input['fancybox3_buttons_color']              => 'fancybox3_buttons_color',
		$input['fancybox3_buttons_color_hover']        => 'fancybox3_buttons_color_hover',
		$input['fancybox3_thumbs_active_border_color'] => 'fancybox3_thumbs_active_border_color',
		$input['fancybox_overlay_color']               => 'fancybox_overlay_color',
	);
	foreach ( $colors as $color => $key ) {
		if ( strlen( $color ) !== 0 ) {
				$input[ $key ] = geo2_maps_validate_color( $color );
		}
	}
	// Validates email.
	if ( strlen( $input['user_email'] ) !== 0 ) {
		if ( ! is_email( $input['user_email'] ) ) {
			$input['user_email'] = '';
			add_settings_error( 'plugin_geo2_maps', 'invalid_email_error', esc_html__( 'Please enter a valid email!', 'ngg-geo2-maps' ), 'error' );
		} else {
			$input['user_email'] = sanitize_email( $input['user_email'] );
		}
	}

	/**
	 * Validate a URL and return it if valid, or an empty string if not.
	 *
	 * This function checks if the URL is valid and uses one of the specified protocols.
	 * If the URL is not valid, it adds an error message and returns an empty string.
	 *
	 * @param string $url The URL to validate.
	 *
	 * @return string The validated URL or an empty string.
	 */
	function geo2_maps_validate_url( $url ) {
		// Define the error message outside of conditionals.
		$error_message = esc_html__( 'Please enter a valid URL! Acceptable protocols: http, https, ftp, ftps', 'ngg-geo2-maps' );

		if ( strlen( $url ) !== 0 ) {
			$protocols = array( 'http', 'https', 'ftp', 'ftps' ); // Acceptable protocols.
			$esc_url   = esc_url( $url, $protocols );
			// Esc_url function above returns empty string if protocol is wrong.
			if ( strlen( $esc_url ) === 0 || filter_var( $esc_url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED ) === false ) {
				add_settings_error( 'plugin_geo2_maps', 'invalid_url_error', $error_message, 'error' );
				return '';
			}
			// If all checks pass, return the escaped URL.
			return $esc_url;
		} else {
			return '';
		}
	}

	// Validates URL.
	$input['xmlurl'] = geo2_maps_validate_url( $input['xmlurl'] );
	// Unsets empty variables ( otherwise wp_parse_args won't work ) and sanitize the rest.
	foreach ( $input as $key => $line ) {
		if ( strlen( $line ) === 0 ) {
			unset( $input[ $key ] ); } else {
			$input[ $key ] = sanitize_text_field( $line ); }
	}

	if ( $input['minimap_type'] === 'same' ) {
		$input['minimap_type'] = $input['map'];
	}

	// Options defaults.
	$defaults = geo2_maps_defaults_array();
	// Parses it.
	$input = wp_parse_args( $input, $defaults );

	return $input;
}
