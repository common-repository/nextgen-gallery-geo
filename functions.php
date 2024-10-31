<?php
/**
 * Various functions, actions and filter used by the plugin before maps are created.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Functions
 * @since      1.0.0
 * @since      2.0.0 Amended and supplemented with additional code and functions.
 * @since      2.0.4 Amended function: geo2_maps_enqueue_scripts()
 * @since      2.0.5 Amended functions: geo2_maps_data(), geo2_maps_enqueue_scripts()
 * @since      2.0.6 Amended functions: geo2_maps_get_id(), geo2_maps_check_content(), geo2_maps_auto(), geo2_maps_data(), geo2_maps_data_single(), geo2_maps_data_worldmap(), geo2_maps_shortcodes(), geo2_maps_exif_camera(), geo2_maps_exif().
 * @since      2.0.7 Amended functions: geo2_maps_init(), geo2_maps_exif_camera(), geo2_maps_exif(), geo2_maps_shortcodes(), geo2_maps_shortcodes_ajax(), geo2_maps_check_content(), geo2_maps_search(), geo2_maps_data(), geo2_maps_data_single(), geo2_maps_data_worldmap().
 * @since      2.0.8 Amended functions: geo2_maps_data(), geo2_maps_coordinates(), geo2_maps_exif_camera(), geo2_maps_exif().
 * @since      2.0.9 Amended functions:geo2_maps_check_content(), geo2_maps_data(), geo2_maps_data_single(), geo2_maps_data_worldmap().
 * @copyright  Copyright (c) 2023, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

// Security: Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'script_loader_tag', 'geo2_maps_amend_loaded_scripts', 10 );
/**
 * WordPress filter. Function defers, asynchronously loads scripts or removes them.
 *
 * @since 2.0.0
 *
 * @param string $tag Defined by WordPress.
 */
function geo2_maps_amend_loaded_scripts( $tag ) {
	// Gets options.
	$options = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );

	$scripts_to_add_async = array( 'www.bing.com/api/maps/mapcontrol', 'wp-color-picker-alpha.min.js', 'upload_media_img_js' );
	$scripts_to_add_defer = array( 'wp-color-picker-alpha.min.js', 'upload_media_img_js' );

	if ( $options['lightbox'] === 'fancybox3' ) {
		$scripts_to_add_async[] = 'jquery.fancybox3.min.js';
		$scripts_to_add_defer[] = 'jquery.fancybox3.min.js';
	}

	if ( $options['lightbox'] === 'slimbox2' ) {
		$scripts_to_add_async[] = '/slimbox2.min.js';
		$scripts_to_add_defer[] = '/slimbox2.min.js';
	}

	if ( $options['ajax'] === 1 ) {
		$scripts_to_add_async[] = 'geo2-ajax.js';
	}

	// Async all remaining scripts not excluded above.
	foreach ( $scripts_to_add_defer as $add_defer_script ) {
		if ( strpos( $tag, $add_defer_script ) === true ) {
			$tag = str_replace( ' src', ' defer="defer" src', $tag );
		}
	}
	foreach ( $scripts_to_add_async as $add_async_script ) {
		if ( strpos( $tag, $add_async_script ) === true ) {
			return str_replace( ' src', ' async="async" src', $tag );
		}
	}
	return $tag;
}

// Gets options to dequeue NextGEN Lightbox styles, for Auto mode (below in the script ), admin page Map Preview and for geo2_media_add_attachment.php.
$geo2_maps_options = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );

add_action( 'init', 'geo2_maps_enqueue_scripts' );
/**
 * WordPress action. Loads scripts and styles:
 * - jQuery (jQuery 3+ is preferred, but fancybox3 works with jQuery 1.9.1+ and jQuery 2+)
 * - Geo2 style.css
 * - inline styles - according to Geo2 settings
 * - jquery.mousewheel-3.0.4.pack.min.js enabling mouse navigation for Fancybox Lightbox
 * - jquery.fancybox3.min.css, jquery.fancybox3.min.js and inline styles for Fancybox 3 Lightbox
 * - slimbox2.css, slimbox2.min.js and jquery.easing.1.3.js for Slimbox 2 Lightbox
 * - Bing Maps mapcontrol
 *
 * @since 2.0.0
 * @since 2.0.4  CSS for Infobox & Route Mode corrected. Condition !empty( $options['xmlurl'] ) removed.
 * @since 2.0.5  Code related to not supported functionality in section "Adds Slimbox 2" removed.
 */
function geo2_maps_enqueue_scripts() {
	// Gets options.
	$options = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );

	// Adds jQuery.
	if ( $options['lightbox'] === 'fancybox3' || $options['lightbox'] === 'slimbox2' ) {
		wp_enqueue_script( 'jquery' );
	}

	// Adds NGG Geo2 CSS.
	wp_enqueue_style( 'geo2_style', GEO2_MAPS_DIR_URL . '/css/style.css', array(), '2.0.0', 'all' );

	// Var to customize CSS.
	$css_to_add = '';
	// Moves fullscreen button when minimap button is visible.
	if ( $options['minimap'] === 1 ) {
		$css_to_add .= '
		.geo2_fullscreen_icon{
			left: 24px;
			-webkit-transition: -webkit-transform left 0.2s;
			transition: left 0.2s;
		}';
	}
	// Adds CSS for Infobox & Route Mode.
	if ( $options['lightbox'] === 'infobox' || $options['route'] === 1 ) {
		if (
			$options['infobox_color'] !== 'rgba(0,0,0,0.7)' ||
			$options['infobox_text_color'] !== '#fff' ||
			! empty( $options['infobox_width'] ) ||
			! empty( $options['infobox_height'] )
		) {
			$css_to_add .= '
		.geo2_InfoboxCustom{'
				. ( $options['infobox_color'] !== 'rgba(0,0,0,0.7)' ? 'background-color: ' . $options['infobox_color'] . ';' : '' )
				. ( $options['infobox_text_color'] !== '#fff' ? 'color: ' . $options['infobox_text_color'] . ';' : '' )
				. ( ! empty( $options['infobox_width'] ) ? 'width: ' . $options['infobox_width'] . 'px;' : '' )
				. ( ! empty( $options['infobox_height'] ) ? 'height: ' . $options['infobox_height'] . 'px;' : '' ) . '
		}';
		}

		if ( $options['infobox_text_color'] !== '#fff' ) {
			$css_to_add .= '
		.geo2_InfoboxCustom  h3, .geo2_InfoboxCustom h2 {
			color: ' . $options['infobox_text_color'] . '
		}';
		} else {
			$css_to_add .= '
		.geo2_InfoboxCustom  h3, .geo2_InfoboxCustom h2 {
			color: #fff
		}';
		}

		if ( $options['infobox_color'] !== 'rgba(0,0,0,0.7)' ) {
			$css_to_add .= '
		.geo2_scrollbar_style::-webkit-scrollbar-track
		{
			' . ( $options['infobox_text_color'] !== '#fff' ? '
			-webkit-box-shadow: inset 0 0 6px ' . $options['infobox_text_color'] . ';' : '' )
				. ( $options['infobox_color'] !== 'rgba(0,0,0,0.7)' ? 'background-color: ' . $options['infobox_color'] . ';' : '' ) . '
		}
		
		' . ( $options['infobox_color'] !== 'rgba(0,0,0,0.7)' ? '
		.geo2_scrollbar_style::-webkit-scrollbar
		{
			background-color: ' . $options['infobox_color'] . ';
		}' : '' ) . '

		.geo2_scrollbar_style::-webkit-scrollbar-thumb
		{
			' . ( $options['infobox_text_color'] !== '#fff' ? '
			-webkit-box-shadow: inset 0 0 6px ' . $options['infobox_text_color'] . ';' : '' )
				. ( $options['infobox_color'] !== 'rgba(0,0,0,0.7)' ? 'background-color: ' . $options['infobox_color'] . ';' : '' ) . '
		}
		/* for Firefox  */
		' . ( $options['infobox_color'] !== 'rgba(0,0,0,0.7)' ? '
		.geo2_scrollbar_style
		{
			scrollbar-color: #c1c1c1 ' . $options['infobox_color'] . ';
		}' : '' );
		}

		$css_to_add .= '
		.geo2_close_icon {
			position: absolute;
			right: -30px;
			opacity: 0.5;
			cursor: pointer;
			padding: 0px 0px;
			background: transparent;
			width: 24px;
			height: 24px;
		}

		.geo2_close_icon:hover {
			opacity: 1;
			color: #000;
			visibility: visible;
			background-color: rgba(255, 255, 255, 0.5);
		}
		
		.geo2_close_icon svg path {
			fill: currentColor;
			stroke-width: 0;
		}';
	}

	if ( strlen( $css_to_add ) !== 0 ) {
		wp_add_inline_style( 'geo2_style', $css_to_add );
	}

	// Adds for Fancybox 1.
	if ( $options['lightbox'] === 'fancybox' ) {
		// Enables mouse wheel image switching - not loaded in standard NextGEN.
		wp_enqueue_script( 'jquery-mousewheel-3-js', GEO2_MAPS_PLUGINS_DIR_URL . '/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/fancybox/jquery.mousewheel-3.0.4.pack.min.js', array( 'jquery' ), '3.0.4', true );

		// In a situation when only Geo2 Maps Plus is installed, the following code will enqueue the scripts and styles.
		if ( ! wp_script_is( 'fancybox-0', 'enqueued' ) ) {
			wp_enqueue_script( 'fancybox-0', GEO2_MAPS_PLUGINS_DIR_URL . '/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/fancybox/jquery.browser.min.js', array( 'jquery' ), '1.0.0', true );
		}
		if ( ! wp_script_is( 'fancybox-1', 'enqueued' ) ) {
			wp_enqueue_script( 'fancybox-1', GEO2_MAPS_PLUGINS_DIR_URL . '/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/fancybox/jquery.easing-1.3.pack.js', array( 'jquery' ), '1.3.0', true );
		}
		if ( ! wp_script_is( 'fancybox-2', 'enqueued' ) ) {
			wp_enqueue_script( 'fancybox-2', GEO2_MAPS_PLUGINS_DIR_URL . '/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/fancybox/jquery.fancybox-1.3.4.pack.js', array( 'jquery' ), '1.3.4', true );
		}
		if ( ! wp_style_is( 'fancybox-0', 'enqueued' ) ) {
			wp_enqueue_style( 'fancybox-0', GEO2_MAPS_PLUGINS_DIR_URL . '/nextgen-gallery/products/photocrati_nextgen/modules/lightbox/static/fancybox/jquery.fancybox-1.3.4.min.css', array(), '1.3.4', 'screen' );
		}
	}

	// Adds Fancybox 3.
	if ( $options['lightbox'] === 'fancybox3' ) {
		// Fancybox3 style sheet does not have a version.
		wp_enqueue_style( 'fancybox3', GEO2_MAPS_DIR_URL . '/js/fancybox3/jquery.fancybox3.min.css', array(), '2.0.0', 'screen' );

		wp_enqueue_script( 'fancybox3-js', GEO2_MAPS_DIR_URL . '/js/fancybox3/jquery.fancybox3.min.js', array( 'jquery' ), '3.5.7', true );

		// Var to customize CSS.
		$f3_css_to_add = '';

		// Inserts F3 side caption css.
		if ( $options['fancybox3_caption'] === 'bottom' ) {
			$f3_css_to_add .= '
			.fancybox3-caption {
				padding: 0px;
			}
			.fancybox3-caption h3 {
				margin: 0px;
				display: inline;
			}
			
			.fancybox3-caption-header {
				float: right;
			}
			
			.fancybox3-caption-column-right {
				text-align: right;
				writing-mode:lr-tb;
			}
			
			.fancybox3-caption-column-left {
				text-align: left;
			}';

			if ( $options['gps'] === 1 || $options['exif'] === 1 ) {
				$f3_css_to_add .= '
			.fancybox3-caption-column {
				padding: 10px;
				float: left;
  			width: 50%;
			}
			
			#fancybox3-caption-bottom-counter {
				position: absolute;
				margin-bottom: 10px;
				bottom: 0;
				width: 100%;
				text-align: center;
			}';
			} else {
				$f3_css_to_add .= '
			.fancybox3-caption-column {
				padding: 10px;
				float: left;
			}
			
			.fancybox3-caption-column-right {
				width: 0px;
			}
			
			.fancybox3-caption-column-left {
				width: 100%;
			}	
				
			#fancybox3-caption-bottom-counter {
				position: absolute;
				bottom: 0;
				right: 0;
				margin-right: 10px;
				margin-bottom: 10px;
				text-align: right;
			}';
			}
		}

		// Insert color overrides.
		if ( $options['fancybox3_colors_override'] === 1 ) {
			$f3_css_to_add .= '
			.fancybox3-bg {
				background: ' . $options['fancybox3_background'] . ';
			}

			.fancybox3-caption {
			background: ' . $options['fancybox3_background'] . ';
			}';

			$f3_css_to_add .= '
			.fancybox3-thumbs {
				background: ' . $options['fancybox3_thumbs_background'] . ';
			}';

			$f3_css_to_add .= '
			.fancybox3-buttons {
				background: ' . $options['fancybox3_buttons_background'] . ';
			}

			.geo2_fullscreen_icon{
				background-color: ' . $options['fancybox3_buttons_background'] . ';
			}';

			if ( $options['minimap'] === 1 ) {
				$f3_css_to_add .= '
			.minimap-glyph {
				background-color: ' . $options['fancybox3_buttons_background'] . ';
			}';
			}

			$f3_css_to_add .= '
			.fancybox3-button,
			.fancybox3-button:visited,
			.fancybox3-button:link {
				color: ' . $options['fancybox3_buttons_color'] . ';
			}

			.geo2_fullscreen_icon{
				color: ' . $options['fancybox3_buttons_color'] . ';
			}';

			if ( $options['minimap'] === 1 ) {
				$f3_css_to_add .= '
			.minimap-glyph svg path {
				fill: ' . $options['fancybox3_buttons_color'] . ';
			}';
			}

			$f3_css_to_add .= '
			.fancybox3-button:hover {
				color: ' . $options['fancybox3_buttons_color_hover'] . ';
			}

			.geo2_fullscreen_icon:hover {
				color: ' . $options['fancybox3_buttons_color_hover'] . ';
				background-color: ' . $options['fancybox3_buttons_color_hover'] . ';
			}';

			if ( $options['minimap'] === 1 ) {
				$f3_css_to_add .= '
			.minimap-glyph:hover {
				color: ' . $options['fancybox3_buttons_color_hover'] . ';
				background-color: ' . $options['fancybox3_buttons_color_hover'] . ';
			}';
			}

			$f3_css_to_add .= '
			.fancybox3-thumbs__list a::before {
				border-color: ' . $options['fancybox3_thumbs_active_border_color'] . ';
			}

			.fancybox3-caption a, .fancybox3-caption a:link, .fancybox3-caption a:visited {
				color: ' . $options['fancybox3_thumbs_active_border_color'] . ';
			}';

			$f3_css_to_add .= '
			.fancybox3-caption h3, .fancybox3-caption h2 {
				color: ' . $options['fancybox3_caption_text_color'] . ';
			}
			
			.fancybox3-caption {
				color: ' . $options['fancybox3_caption_text_color'] . ';
			}';
		}

		// Adds custom css which can override all styles.
		if ( strlen( $f3_css_to_add ) !== 0 ) {
			wp_add_inline_style( 'fancybox3', $f3_css_to_add );
		}
	}

	// Adds Slimbox 2.
	if ( $options['lightbox'] === 'slimbox2' ) {
		// Changed to Slimbox 2.04. http://www.digitalia.be/software/slimbox2/ and https://blog.sotel.de/2018/09/23/slimbox2-auto-resize-version/.
		wp_enqueue_style( 'slimbox2_css', GEO2_MAPS_DIR_URL . '/js/slimbox2/css/slimbox2.css', array(), '2.0.0', 'screen' );
		wp_enqueue_script( 'slimbox2-js', GEO2_MAPS_DIR_URL . '/js/slimbox2/js/slimbox2.min.js', array( 'jquery' ), '2.06', true );
	}

	// Adds mapcontrol.
	wp_register_script( 'bing-maps', 'https://www.bing.com/api/maps/mapcontrol', array(), null, false ); // Async defer? After Fancybox3 only when it is used.
	wp_enqueue_script( 'bing-maps' );
}

add_filter( 'load_textdomain_mofile', 'geo2_maps_load_my_own_textdomain', 10, 2 );
/**
 * Load custom textdomain for the plugin.
 *
 * WordPress function for own translations from:
 * https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/
 * It checks if the textdomain is 'ngg-geo2-maps' and if the .mo file is in the plugins language directory.
 * If both conditions are met, it changes the path to the .mo file to use the one in the plugin's languages directory.
 *
 * @since 2.0.1
 *
 * @param string $mofile Path to the .mo file.
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @return string Filtered path to the .mo file.
 */
function geo2_maps_load_my_own_textdomain( $mofile, $domain ) {
	if ( $domain === 'ngg-geo2-maps' && strpos( $mofile, WP_LANG_DIR . '/plugins/' ) !== false ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
		$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
	}
	return $mofile;
}

add_action( 'init', 'geo2_maps_init' );
/**
 * WordPress action. Loads additional scripts and styles:
 * - plugin translations
 * - Ajax for maps not loaded at page open
 * - Ajax for Worldmap
 *
 * @since 1.0.0
 * @since 2.0.0 Amended.
 * @since 2.0.1 geo2-ajax.js version updated.
 *
 * @todo  Check if this function can be integrated with function geo2_maps_enqueue_scripts()
 */
function geo2_maps_init() {
	// Adds I18n.
	load_plugin_textdomain( 'ngg-geo2-maps', false, basename( __DIR__ ) . '/languages' );

	// Gets options.
	$options = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );

	if ( $options['load_ajax'] === 1 || $options['open_lightbox'] === 1 ) {
		// Adds Ajax javascript.
		wp_register_script( 'geo2-ajax', GEO2_MAPS_DIR_URL . '/js/geo2-ajax.js', array( 'jquery' ), '2.0.3', true );
		wp_localize_script(
			'geo2-ajax',
			'geo2_Ajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'geo2url' => GEO2_MAPS_DIR_URL,
				'nonce'   => wp_create_nonce( 'geo2-ajax-nonce' ),
			)
		);
		wp_enqueue_script( 'geo2-ajax' );
	}
	// geo2_maps_code_integrity_check.
	if ( $options['load_ajax'] === 1 ) {
		// Adds Ajax ( show map ) for logged in users.
		add_action( 'wp_ajax_geo2_maps_showmap', 'geo2_maps_shortcodes_ajax' );
		// For not logged in users.
		add_action( 'wp_ajax_nopriv_geo2_maps_showmap', 'geo2_maps_shortcodes_ajax' );
	}
	// Ajax should also be enabled for Worldmap when option to load and show galleries in F3 is enabled.
	if ( $options['open_lightbox'] === 1 ) {
		// Adds Ajax lightbox for Worldmap.
		add_action( 'wp_ajax_geo2_maps_lightbox', 'geo2_maps_lightbox_callback' );
		add_action( 'wp_ajax_nopriv_geo2_maps_lightbox', 'geo2_maps_lightbox_callback' );
	}
}

/**
 * Extracts GPS coordinates from an image file and converts them to a proper format.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended name.
 * @since  2.0.7 Error control operator @ removed. $exif defined first.
 * @since  2.0.8 Error handler added.
 *
 * @param  string $picture_path A path to a picture.
 * @return string[]|bool $geo Latitude and longitude coordinates
 */
function geo2_maps_coordinates( $picture_path ) {
	// Sets error handler for potential errors in exif_read_data().
	set_error_handler(
		function ( $err_no, $err_str, $err_file, $err_line ) {
			$error = 'Error no: ' . $err_no . '\\nError message: ' . $err_str . '\\nError file: ' . str_replace( '\\', '\\\\', $err_file ) . '\\nError line: ' . $err_line;
			// Shows errors in the browser console.
			echo "<script>console.log('exif_read_data() error: \\n" . $error . "' );</script>";
		}
	);

	// Gets Exif data.
	$exif = exif_read_data( $picture_path, 'GPS', 0 );

	// Restores error handler to stop errors from being displayed.
	restore_error_handler();

	if ( $exif !== false ) {

		// Any coordinates available?
		if ( ! isset( $exif['GPSLongitude'][0] ) ) {
			return false;
		} {
			// South or West?
		if ( $exif['GPSLatitudeRef'] === 'S' ) {
			$gps['latitude_string']    = -1;
			$gps['latitude_direction'] = 'S';
		} else {
			$gps['latitude_string']    = 1;
			$gps['latitude_direction'] = 'N';
		}
		if ( $exif['GPSLongitudeRef'] === 'W' ) {
			$gps['longitude_string']    = -1;
			$gps['longitude_direction'] = 'W';
		} else {
			$gps['longitude_string']    = 1;
			$gps['longitude_direction'] = 'E';
		}

			$gps['latitude_hour']    = $exif['GPSLatitude'][0];
			$gps['latitude_minute']  = $exif['GPSLatitude'][1];
			$gps['latitude_second']  = $exif['GPSLatitude'][2];
			$gps['longitude_hour']   = $exif['GPSLongitude'][0];
			$gps['longitude_minute'] = $exif['GPSLongitude'][1];
			$gps['longitude_second'] = $exif['GPSLongitude'][2];

			// Calculates.
		foreach ( $gps as $key => $value ) {
			$pos = strpos( $value, '/' );
			if ( $pos !== false ) {
				$temp        = explode( '/', $value );
				$gps[ $key ] = $temp[0] / $temp[1];
			}
		}

			$geo['latitude_format']  = $gps['latitude_direction'] . ' ' . $gps['latitude_hour'] . '&deg;' . $gps['latitude_minute'] . '&#x27;' . round( $gps['latitude_second'], 4 ) . '&#x22;';
			$geo['longitude_format'] = $gps['longitude_direction'] . ' ' . $gps['longitude_hour'] . '&deg;' . $gps['longitude_minute'] . '&#x27;' . round( $gps['longitude_second'], 4 ) . '&#x22;';

			$geo['latitude']  = $gps['latitude_string'] * ( $gps['latitude_hour'] + ( $gps['latitude_minute'] / 60 ) + ( $gps['latitude_second'] / 3600 ) );
			$geo['longitude'] = $gps['longitude_string'] * ( $gps['longitude_hour'] + ( $gps['longitude_minute'] / 60 ) + ( $gps['longitude_second'] / 3600 ) );
		}
	} else {
		return false;
	}
	return $geo;
}

/**
 * Extracts lens brand and model from an image Exif data.
 *
 * @since  2.0.0
 * @since  2.0.6 Variable $lens_brand and $lens_model defined if not created from EXIF, Bool in exif_read_data() changed to 1, check added to insure exif_read_data() returned an array
 * @since  2.0.7 Error control operator @ removed. Argument value corrected in exif_read_data().
 * @since  2.0.8 Error handler added.
 *
 * @see    function geo2_maps_pin_desc() in bing-map-functions.php
 * @param  object $picture_data Data of a specific picture.
 * @return string lens_info Camera nad lens info.
 */
function geo2_maps_exif_camera( $picture_data ) {
	// Sets error handler for potential errors in exif_read_data().
	set_error_handler(
		function ( $err_no, $err_str, $err_file, $err_line ) {
			$error = 'Error no: ' . $err_no . '\\nError message: ' . $err_str . '\\nError file: ' . str_replace( '\\', '\\\\', $err_file ) . '\\nError line: ' . $err_line;
			// Shows errors in the browser console.
			echo "<script>console.log('exif_read_data() error: \\n" . $error . "' );</script>";
		}
	);

	// Gets Exif data.
	$exif_exif = exif_read_data( $picture_data->image_path, 'EXIF', 0 );

	// Restores error handler to stop errors from being displayed.
	restore_error_handler();

	// Checks if exif_read_data() returned an array (exif_read_data() returns array or false).
	if ( $exif_exif === false ) {
		$exif_exif = array();
	}
	// Lens brand i.e. "FUJIFILM".
	if ( array_key_exists( 'UndefinedTag:0xA433', $exif_exif ) ) {
		$lens_brand = $exif_exif['UndefinedTag:0xA433'];
	} else {
		$lens_brand = '';
	}
	// Lens model i.e. "XF18-55mmF2.8-4 R LM OIS".
	if ( array_key_exists( 'UndefinedTag:0xA434', $exif_exif ) ) {
		$lens_model = $exif_exif['UndefinedTag:0xA434'];
	} else {
		$lens_model = '';
	}
	if ( strlen( $lens_brand ) > 2 || strlen( $lens_model ) > 2 ) {
		$lens_info = '<span class="fancybox3-caption-exif-param">' . esc_html__( 'Lens', 'ngg-geo2-maps' ) . ':</span>&nbsp' . str_replace( ' ', '&nbsp;', $lens_brand . ' ' . $lens_model ) . '<br />';
	} else {
		$lens_info = '';
	}
	return $lens_info;
}

/**
 * Extracts information from an image Exif data.
 *
 * Used when there is no data in MySQL database extracted by NextGEN Gallery.
 * Older versions of NextGEN Gallery were not doing it.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended name.
 * @since  2.0.6 Bool in exif_read_data changed to 1, check added to insure exif_read_data returned an array.
 * @since  2.0.7 Error control operator removed, undefined data added as null.
 * @since  2.0.8 Error handler added. Focal length number rounding added.
 *
 * @see    function geo2_maps_pin_desc() in bing-map-functions.php
 * @param  object $picture_data Data of a specific picture.
 * @return object $picture_data Picture data supplemented with EXIF info.
 */
function geo2_maps_exif( $picture_data ) {
	// Sets error handler for potential errors in exif_read_data().
	set_error_handler(
		function ( $err_no, $err_str, $err_file, $err_line ) {
			$error = 'Error no: ' . $err_no . '\\nError message: ' . $err_str . '\\nError file: ' . str_replace( '\\', '\\\\', $err_file ) . '\\nError line: ' . $err_line;
			// Shows errors in the browser console.
			echo "<script>console.log('exif_read_data() error: \\n" . $error . "' );</script>";
		}
	);

	$exif_ifd0 = exif_read_data( $picture_data->image_path, 'IFD0', 0 );
	$exif_exif = exif_read_data( $picture_data->image_path, 'EXIF', 0 );

	// Restores error handler to stop errors from being displayed.
	restore_error_handler();

	// Checks if exif_read_data() returned an array (exif_read_data() returns array or false).
	if ( $exif_ifd0 === false ) {
		$exif_ifd0 = array();
	}
	if ( $exif_exif === false ) {
		$exif_exif = array();
	}
	// Array with EXIF data.
	$data = array();
	// Timestamp.
	// Defines a default value for 'created_timestamp'.
	$data_time = '';
	if ( ! empty( $exif_ifd0['DateTimeDigitized'] ) ) {
		$data_time = $exif_ifd0['DateTimeDigitized'];
	} elseif ( ! empty( $exif_ifd0['DateTimeOriginal'] ) ) {
		$data_time = $exif_ifd0['DateTimeOriginal'];
	} elseif ( ! empty( $exif_ifd0['DateTime'] ) ) {
		$data_time = $exif_ifd0['DateTime'];
	}
	// Converts date-time to international format.
	if ( ! empty( $data_time ) ) {
		$data['created_timestamp'] = str_replace( ':', '-', substr( $data_time, 0, 10 ) ) . ' ' . substr( $data_time, 10 );
	} else {
		$data['created_timestamp'] = '';
	}

	// Camera.
	if ( array_key_exists( 'Make', $exif_ifd0 ) && array_key_exists( 'Model', $exif_ifd0 ) ) {
		$data['camera'] = $exif_ifd0['Make'] . ' ' . $exif_ifd0['Model'];
	} else {
		$data['camera'] = '';
	}

	// Aperture.
	if ( array_key_exists( 'ApertureFNumber', $exif_ifd0['COMPUTED'] ) ) {
		$data['aperture'] = $exif_ifd0['COMPUTED']['ApertureFNumber'];
	} else {
		$data['aperture'] = '';
	}

	// Focal length.
	if ( array_key_exists( 'FocalLength', $exif_ifd0 ) ) {
		list($num, $den)      = explode( '/', $exif_ifd0['FocalLength'] );
		$data['focal_length'] = round( ( $num / $den ), 1 ) . 'mm';
	} else {
		$data['focal_length'] = '';
	}

	// ISO.
	if ( array_key_exists( 'ISOSpeedRatings', $exif_exif ) ) {
		$data['iso'] = $exif_exif['ISOSpeedRatings'];
	} else {
		$data['iso'] = '';
	}

	// Shutter speed.
	if ( array_key_exists( 'ExposureTime', $exif_ifd0 ) ) {
		$data['shutter_speed'] = $exif_ifd0['ExposureTime'];
	} else {
		$data['shutter_speed'] = '';
	}
	$picture_data->meta_data = array_merge( $picture_data->meta_data, $data );
	return $picture_data;
}

/**
 * Connects with geocoding provider and asks for GPS coordinates of a place
 * with a specific name.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended. Google geocoding removed.
 *
 * @see    function geo2_maps(), function geo2_maps_worldmap()
 * @param  string $location Location name.
 * @param  array  $options Optional. An array of options.
 * @return array|bool $gps Location coordinates.
 */
function geo2_maps_geocode( $location, $options ) {
	/*
	 * GOOGLE - removed, using Geocoding would violate Google API policy
	 * @link https://developers.google.com/maps/documentation/geocoding/policies
	 */

	// MapQuest.
	if ( $options['geocoding_provider'] === ( 'mapquest' ) ) {
		if ( ! empty( $options['mapquest_key'] ) ) {
			$geo_key = $options['mapquest_key'];
		}
		$url      = 'http://www.mapquestapi.com/geocoding/v1/address?key=' . $geo_key . '&outFormat=json&maxResults=1&location=' . rawurlencode( $location );
		$response = wp_remote_get( $url );
		// Get the body of the response.
		$jsonfile = wp_remote_retrieve_body( $response );
		// Decodes the json file.
		if ( ! json_decode( $jsonfile, true ) ) {
			return false;
		} else {
			$response = json_decode( $jsonfile, true );
		}

		if ( ! empty( $response['results']['0']['locations'] ) ) {
			$gps['latitude']  = $response['results']['0']['locations']['0']['latLng']['lat'];
			$gps['longitude'] = $response['results']['0']['locations']['0']['latLng']['lng'];
			return $gps;
		} else {
			return false;
		}
	}

	// OpenStreet Maps.
	if ( $options['geocoding_provider'] === ( 'openstreetmaps' ) ) {
		$url = 'https://nominatim.openstreetmap.org/search/' . rawurlencode( $location ) . '?format=json&limit=1&email=' . $options['user_email'];
		// Open the file - get the json response using the HTTP headers set above.
		$response = wp_remote_get( $url );
		// Get the body of the response.
		$jsonfile = wp_remote_retrieve_body( $response );
		// Decode the json.
		if ( ! json_decode( $jsonfile, true ) ) {
			return false;
		} else {
			$response = json_decode( $jsonfile, true );
			// Extract data ( e.g. latitude and longitude ) from the results.
			$gps['latitude']  = $response[0]['lat'];
			$gps['longitude'] = $response[0]['lon'];
			return $gps;
		}
	}

	// BING Maps.
	if ( $options['geocoding_provider'] === ( 'bing' ) ) {
		$key = $options['geo_bing_key'];
		// URL of Bing Maps REST Services Locations API.
		$base_url = 'http://dev.virtualearth.net/REST/v1/Locations';
		$query    = str_ireplace( ' ', '%20', $location );
		// Construct the final Locations API URI.
		// Adds '&includeNeighborhood=1' to get address.
		$url = $base_url . '/' . $query . '?output=json&maxResults=1&key=' . $key;
		// Gets the response from the Locations API and store it in a string.
		$response = wp_remote_get( $url );
		// Get the body of the response.
		$jsonfile = wp_remote_retrieve_body( $response );
		// Decode the json.
		if ( ! json_decode( $jsonfile, true ) ) {
			return false;
		}
		$response = json_decode( $jsonfile, true );
		// Extract data ( e.g. latitude and longitude ) from the results.
		if ( $response['resourceSets']['0']['estimatedTotal'] !== 0 ) {
			$gps['latitude']  = $response['resourceSets']['0']['resources']['0']['point']['coordinates']['0'];
			$gps['longitude'] = $response['resourceSets']['0']['resources']['0']['point']['coordinates']['1'];
			return $gps;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Creates array with a code which is transferred on Ajax request.
 *
 * @since  2.0.0
 *
 * @param  array $options Plugin options.
 * @return array of strings
 */
function geo2_map_approved_code( $options ) {
	$geo2_map_approved_code = array(
		'var loc_',
		'var pin_',
		'= geo2_thumbnail_' . $options['mid'] . '( loc_',
		'= new Microsoft.Maps.Location(',
		'locs_' . $options['mid'] . '.push( loc_',
		'pins_' . $options['mid'] . '.push( pin_',
		"', \"",
	);

	if ( $options['thumb'] === 1 ) {
		array_push(
			$geo2_map_approved_code,
			'", function( pin_',
			'pins_',
			') {',
			'} );',
			"\", '",
			"',"
		);
	} else {
		array_push(
			$geo2_map_approved_code,
			'.metadata = {',
			'nonce: "',
			'title: "',
			'thumb_width:',
			'thumb_height:',
			'pid: "',
			'gid: "',
			'aidslug: "',
			'pageURL: "',
			'src: "',
			'HTMLcontent: \'',
			'picture_nr: "',
			'_HandlerId = Microsoft.Maps.Events.addHandler( pin_',
			', "click", pushpinClicked_' . $options['mid'],
			'bringForwardOnHover_' . $options['mid'] . '( pin_',
			'.setOptions( { enableHoverStyle: true, enableClickedStyle: false }',
			'pin_',
			'"
		};',
			'\','
		);
	}

	if ( $options['lightbox'] === 'infobox' || ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) ) {
		array_push(
			$geo2_map_approved_code,
			'<div id="geo2_InfoboxCustom_',
			'" class="geo2_InfoboxCustom"',
			'style="max-width:',
			'{maxWidth};">',
			'{maxWidth};',
			'style="max-height: {maxDescHeight};',
			'style="max-height: ',
			'max-height:',
			'{maxHeight};">',
			'{maxDescHeight};',
			'<img id="infoboxImg_',
			'" src="',
			'" align="left"',
			'style="margin:0px;"/>',
			'<div id="geo2_close_',
			'" class="geo2_close_icon" onclick="closeInfobox_' . $options['mid'] . '( ' . "\'",
			"\'" . ' )"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" width="24" height="24"><path d="M5 7.5L9.5 12L5 16.5L5 19L7.5 19L12 14.5L16.5 19L19 19L19 16.5L14.5 12L19 7.51L19 5L16.5 5L12 9.5L7.5 5L5 5L5 7.5Z" id="a2GhEW8Onw"></path></svg></div>',
			'<div class="geo2_infobox_title_wrap"',
			'{imgWidth};"><div id="geo2_infobox_title_',
			'" class="geo2_infobox_title geo2_scrollbar_style"',
			'style="--ratio:{ratio};"><div class="geo2_infobox_title_cont"><div class="geo2_infobox_title_text">',
			'style="',
			'<b>EXIF</b>',
			'<h3>',
			'<b>',
			'</b>',
			'</h3>',
			'<br />',
			'</div>',
			'<div id="geo2_infobox_desc_',
			'" class="geo2_infobox_desc geo2_scrollbar_style"',
			'<span class="fancybox3-caption-exif-param">',
			':</span>',
			'">',
			'&deg;',
			'&#x27;',
			'&#x22;',
			'f/',
			'mm'
		);
	}

	array_push(
		$geo2_map_approved_code,
		'" );',
		');'
	);
	if ( $options['thumb'] === 1 ) {
		array_push(
			$geo2_map_approved_code,
			'"", "',
			'", "',
			', "',
			'",'
		);
	} else {
		array_push(
			$geo2_map_approved_code,
			'", "',
			', "',
			'",'
		);
	}

	if ( $options['lightbox'] === 'fancybox' ) {
		array_push(
			$geo2_map_approved_code,
			'{lbox}',
			'{
	\'href\' : "',
			'",	
	\'title\'	: "',
			'</br>"
	},',
			'"
},',
			'</br>'
		);
	}
	if ( $options['lightbox'] === 'fancybox3' ) {
		array_push(
			$geo2_map_approved_code,
			'{lbox}',
			'    {
				src  : "',
			'",
				opts : {
					imageId : "',
			'",
					gid  : "',
			'",
					aid  : "',
			'",
					thumb: "',
			'"
				}
			},',
			'"
				}
			}'
		);
		if ( $options['fancybox3_caption'] === 'bottom' ) {
			array_push(
				$geo2_map_approved_code,
				'{pic_desc}',
				'		<div class="fancybox3-caption-row">
				<div class="fancybox3-caption-column fancybox3-caption-column-left">',
				'<div style="height:4px;font-size:1px;">&nbsp;</div>',
				'		<div id="fancybox3-caption-bottom-counter" class="fancybox3-caption-exif-param">',
				' <span data-fancybox3-index></span> ',
				' <span data-fancybox3-count></span></div>
			</div>
				<div class="fancybox3-caption-column fancybox3-caption-column-right">',
				'<div class="fancybox3-caption-header">| <b>',
				':</span>&nbsp;',
				'&nbsp;| <span class="fancybox3-caption-exif-param">',
				'<div class="fancybox3-caption-header">&nbsp|| <b>EXIF</b></div>',
				'<span class="fancybox3-caption-exif-param">',
				' |&nbsp;',
				'&nbsp;|',
				'&nbsp;',
				'</div>',
				' | ',
				'<h3>',
				'<b>',
				'</b>',
				'</h3>',
				'&deg;',
				'&#x27;',
				'&#x22;',
				'f/',
				'mm'
			);
		}
	}
	if ( $options['lightbox'] === 'slimbox2' ) {
		array_push(
			$geo2_map_approved_code,
			'{lbox}',
			' ["',
			'", "',
			'"], ',
			'"]'
		);
	}
	return $geo2_map_approved_code;
}

add_shortcode( 'geo2', 'geo2_maps_shortcodes' );
/**
 * Adds shortcodes.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.3 Security update - "Serialize" replaced with "wp_localize_script"
 * @since  2.0.6 "Isset" replaced with "!empty" in "Single picture shortcode" section. Whole array $options passed to functions.
 * @since  2.0.7 Unused code removed.
 *
 * @param  array $options Optional. An array of options.
 * @return string|object[] $out When Ajax option is selected HTML code is returned. If Ajax is disabled Javascript code is output using "echo".
 */
function geo2_maps_shortcodes( $options ) {
	// Gets options.
	$default_options = get_option( 'plugin_geo2_maps_options' );

	$options = geo2_maps_convert_to_int( wp_parse_args( $options, $default_options ) );

	// 'mid' must be a number.
	$options['mid'] = wp_rand( 0, 999 );

	// For maps on demand in Ajax mode.
	if ( $options['load_ajax'] === 1 && $options['ajax'] === 1 ) {
		// Saves map option in open web page to be send to server and create a map based on them.
		wp_localize_script( 'geo2-ajax', 'geo2_options', $options );
		$options['status'] = 'ajax';

		$out = "\n\n" . '<!-- Start NGG Geo2 Maps - Load on Demand -->' . "\n";

		$out .= '<script>' . "\n";
		// Serializes map code.
		// base64_encode() was used to ensure that the data can be safely transmitted without modification.
		$out .= 'var geo2_map_code_' . $options['mid'] . ' = "' . base64_encode( geo2_maps_data( $options ) ) . '";' . "\n";

		// Serializes map approved code words and symbols.
		// base64_encode() was used to ensure that the data can be safely transmitted without modification.
		$out .= 'var geo2_map_approved_code_' . $options['mid'] . ' = "' . base64_encode( wp_json_encode( geo2_map_approved_code( $options ) ) ) . '";
</script>' . "\n";

		$out .= '
<div id="geo2_map_' . $options['mid'] . '_ajax_placeholder">
	<div id="geo2_maps_' . $options['mid'] . '" class="geo2_maps_map" style="display: none;"></div>
	<div class="geo2_slide_line" style="width: ' . $options['map_width'] . ';"></div>
	<p class="geo2_slide" >
		<a href="#" id="geo2_slide_' . $options['mid'] . '" class="geo2_btn_slide" onclick="geo2_maps_showmap_ajax( ' . $options['mid'] . ', geo2_map_code_' . $options['mid'] . ', geo2_map_approved_code_' . $options['mid'] . ' );return false;">
		' . esc_html__( 'Map', 'ngg-geo2-maps' ) . '
		</a>
	</p>
</div>
<!-- End NGG Geo2 Maps - Load on Demand -->' . "\n\n";
		return $out;
	}

	// Worldmap shortcode.
	if ( $options['worldmap'] === 1 ) {
		$options['status'] = 'worldmap';
		return geo2_maps_data_worldmap( $options );
	}

	// Single picture shortcode.
	if ( ! empty( $options['pid'] ) ) {
		$picture_ids_array = explode( ',', $options['pid'] );
		foreach ( $picture_ids_array as $pid ) {
			if ( ! is_numeric( $pid ) ) {
				echo 'To show photos provide picture id numbers only.';
				break;
			}
		}
		$options['status'] = 'pictures_map';
		return geo2_maps_data_single( $options );
	}
	$options['status'] = 'standard_map';
	return geo2_maps_data( $options );
}

/**
 * Check a security nonce and runs shortcodes function for Ajax.
 * Javascript code is output using "echo".
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.3 Security update - "Unserialize" removed
 * @since  2.0.7 Unused parameter removed from function reference geo2_maps_lightbox_data(). Variable $pic_desc got value as empty string to avoid exception.
 * @since  2.0.8 Exif data not acquired if not needed.
 *
 * @see    function geo2_maps_init()
 */
function geo2_maps_shortcodes_ajax() {
	// Nonce security.
	check_ajax_referer( 'geo2-ajax-nonce', 'nonce' );

	$options = geo2_maps_convert_to_int( $_POST['options'] );
	// Validates & sanitize returned data.
	if ( ! is_array( $options ) ) {
		die( 'Security check failed!' );
	} else {
		foreach ( $options as $key => $opt ) {
			if ( is_numeric( $opt ) ) {
				continue;
			} elseif ( substr( $opt, 0, 3 ) === 'rgb' || substr( $opt, 0, 1 ) === '#' ) {
				$options[ $key ] = geo2_maps_validate_color( $opt );
				continue;
			} elseif ( substr( $opt, 0, 4 ) === 'http' || substr( $opt, 0, 3 ) === 'ftp' ) {
				$protocols       = array( 'http', 'https', 'ftp', 'ftps' ); // Acceptable protocols.
				$options[ $key ] = esc_url( $opt, $protocols );
				continue;
			} elseif ( $opt === 'Image {x} of {y}' || $opt === 'Photo {x} of {y}' || $opt === '{x}/{y}' ) {
				continue;
			} else {
				// Sanitizes option.
				$str = $options[ $key ];
				$str = wp_strip_all_tags( $str );
				if ( seems_utf8( $str ) ) {
					$str = utf8_uri_encode( $str, 200 );
				}
				$str             = preg_replace( '/&.+?;/', '', $str ); // Kill entities.
				$str             = preg_replace( '/[^a-zA-Z0-9 _-]/', '', $str );
				$str             = preg_replace( '/\s+/', '', $str );
				$str             = preg_replace( '|-+|', '-', $str );
				$options[ $key ] = $str;
			}
		}
	}

	// Unsets ajax, no loop.
	$options['ajax'] = 2;

	$picture_list = geo2_maps_shortcodes( $options );

	$picture_nr         = 0;  // Counts pictures with geodata, $picture_nr_total counts all pictures, used for lightbox.
	$picture_nr_total   = 0;
	$geo2_maps_lightbox = '';
	$pic_desc           = array();
	$pins               = '';

	foreach ( $picture_list as $picture_data ) {
		// If not Worldmap.
		if ( $options['worldmap'] !== 1 ) {
			// Gets exif information ( needed for old galleries, created before NGG stored meta_data ).
			if ( $options['exif'] === 1 && ( ( $options['lightbox'] === 'fancybox3' && $options['fancybox3_caption'] !== 'no' ) || $options['lightbox'] === 'infobox' ) ) {
				if ( empty( $picture_data->meta_data ) && $options['exif'] === 1 ) {
					$picture_data = geo2_maps_exif( $picture_data );
				} elseif ( ! empty( $picture_data->meta_data['created_timestamp'] ) ) {
					// NextGEN stores date-time as Unix timestamp using PHP function strtotime().
					if ( is_numeric( $picture_data->meta_data['created_timestamp'] ) ) {
						$picture_data->meta_data['created_timestamp'] = gmdate( 'Y-m-d H:i:s', $picture_data->meta_data['created_timestamp'] );
					}
				}
			}
			// Creates info for lightbox.
			$geo2_maps_lightbox .= geo2_maps_lightbox_data( $picture_data, $options['lightbox'] );

			// Gets exif-geolocation.
			$picture_data->gps = geo2_maps_coordinates( $picture_data->image_path );
			// Shows only photos with gps - for these collect lightbox data and create pins.
			if ( is_array( $picture_data->gps ) ) {
				// Adds pins.
				$pins .= geo2_maps_add_pin( $picture_data, $picture_nr, $picture_nr_total, $options );
				++$picture_nr;
			}
			++$picture_nr_total;

			if ( $options['lightbox'] === 'fancybox3' ) {
				$f3_options             = $options;
				$f3_options['lightbox'] = 'fancybox3';
				$pic_desc[]             = geo2_maps_pin_desc( $picture_data, $f3_options );
			}
		} else {
			// Get exif-geodata from preview picture.
			$picture_data->gps = geo2_maps_coordinates( $picture_data->image_path );
			// If no GPS coordinates geocode from gallery Title.
			if ( ! is_array( $picture_data->gps ) ) {
				$picture_data->gps = geo2_maps_geocode( $picture_data->title, $options );
			}
			// Checks again if $picture_data->gps is an array.
			if ( is_array( $picture_data->gps ) ) {
				// Adds pin.
				$pins .= geo2_maps_add_pin( $picture_data, $picture_nr, $picture_nr_total, $options );
				++$picture_nr;
			}
			++$picture_nr_total;
		}
	}

	if ( $options['worldmap'] !== 1 && $options['lightbox'] === 'fancybox3' ) {
		$pic_desc = wp_json_encode( $pic_desc );
	} else {
		$pic_desc = '';
	}

	if ( $options['worldmap'] !== 1 && ( $options['lightbox'] === 'slimbox2' || $options['lightbox'] === 'fancybox3' ) ) {
		$geo2_maps_lightbox = rtrim( $geo2_maps_lightbox, ', ' );
	}
	if ( $options['thumb'] === 1 ) {
		$pins = rtrim( $pins, '		} );' );
	}
	// Escaping function is not needed because output will be validated later in geo2-ajax.js.
	echo $pins . '{split}' . $geo2_maps_lightbox . '{split}' . $pic_desc;

	die;
}

// Checks if Auto Mode is enabled.
if ( $geo2_maps_options['auto_mode'] === 1 ) {
	add_filter( 'the_content', 'geo2_maps_auto' );
	/**
	 * Auto Mode - adds a map to a top or a bottom of a page.
	 *
	 * @since  1.0.0
	 * @since  2.0.0 Amended.
	 * @since  2.0.6 Code removed: $options = wp_parse_args( $options, $default_options );
	 *
	 * @param  string $content Raw page content.
	 * @return string $content Raw page content with inserted code creating a map.
	 */
	function geo2_maps_auto( $content ) {
		$options           = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );
		$options['status'] = 'auto';
		$page_top          = '';
		$page_bottom       = '';

		if ( $options['top_bottom'] === 0 ) {
			$page_top     = geo2_maps_check_content( $options );
			$full_content = $page_top . $content;
		} else {
			$page_bottom  = geo2_maps_check_content( $options );
			$full_content = $content . $page_bottom;
		}
		return $full_content;
	}
}

/**
 * Searches the page content to get inserted gallery Id number.
 *
 * NextGEN Gallery 3.0.8 uses 'container_ids=' instead of "nggallery=".
 * 'ngg src="galleries" ids=' for galleries, 'ngg src="albums" ids=' for albums.
 *
 * @since  2.0.0
 * @since  2.0.6 Variable $gallery_ids defined before it's used.
 * @since  2.0.7 Returns array of strings not string with commas.
 *
 * @see    function geo2_maps_search(), function geo2_maps_data()
 * @param  string $content Raw page content.
 * @return string[] $gallery_ids Id numbers of inserted galleries divided by a comma.
 */
function geo2_maps_get_id( $content ) {
	$str_array   = array();
	$str_array[] = 'ngg src="galleries" ids="';
	$str_array[] = 'container_ids="';
	$str_array[] = 'nggallery="';
	$str_array[] = 'justified_image_grid ng_gallery=';
	$gallery_ids = '';

	foreach ( $str_array as $str ) {
		$text = $content;
		while ( strpos( $text, $str ) !== false ) {
			// Removes string from the $content and everything in front of it.
			$first    = strpos( $text, $str );
			$position = ( strlen( $str ) + $first );
			$cut_text = substr( $text, $position );
			// Id numbers are contained within the ". The search is done to find the first occurrence in the string $cut_text.
			// The Justified Image Grid does not contain ", therefore the search is done for a " " ( space ) or ending bracket ].
			if ( $str === 'justified_image_grid ng_gallery=' ) {
				$gap1 = strpos( $cut_text, ' ' );
				$gap2 = strpos( $cut_text, ']' );
				if ( $gap1 < $gap2 ) {
					$gap = $gap1;
				} else {
					$gap = $gap2;
				}
			} else {
				$gap = strpos( $cut_text, '"' );
			}
			$gallery_id   = substr( $cut_text, 0, $gap );
			$text         = $cut_text;
			$gallery_ids .= $gallery_id . ',';
		}
	}
	// String returned from the Content search may contain duplicates if the same gallery was placed twice.
	return array_unique( explode( ',', rtrim( $gallery_ids, ',' ) ) );
}

/**
 * Searches the content and the database to get albums and contained in them albums and gallery Id numbers.
 *
 * @since  2.0.0
 * @since  2.0.6 Variable $album_ids defined before it's used. "wp_caption" and "page_url" default empty value defined.
 * @since  2.0.7 $_SERVER['DOCUMENT_ROOT'] replaced with ABSPATH. Picture no. pid acquired from database to prevent undefined variable warning. $content passed to geo2_maps_search(). "page_url" for albums statement corrected. Function improved to get data from database also for galleries if no albums are found.
 * @since  2.0.9 Thumbnails full filename acquired from meta_data.
 *
 * @see    function geo2_maps_auto()
 * @param  array $options An array of options.
 * @return string Javascript code creating a desired map.
 */
function geo2_maps_check_content( $options ) {
	$content = get_the_content();

	if ( $options['auto_include'] !== 'images' ) {
		$str_array       = array();
		$str_array[]     = 'ngg src="albums" ids='; // New NGG shortcode.
		$str_array[]     = 'source="albums" container_ids=';
		$str_array[]     = 'justified_image_grid ng_album=';
		$album_ids       = '';
		$all_gallery_ids = array();
		$all_album_ids   = array();
		$all_data        = array();
		// Gets common global.
		global $wpdb;
		// Gets common ngg pictures database prefix for galleries and albums.
		$wpdb->ngg_pictures = $wpdb->prefix . 'ngg_pictures';
		// Gets ngg gallery database prefix.
		$wpdb->ngg_gallery = $wpdb->prefix . 'ngg_gallery';

		foreach ( $str_array as $str ) {
			$text = $content;
			while ( ( strpos( $text, $str ) ) !== false ) {
				$first    = strpos( $text, $str );
				$position = strlen( $str ) + 1 + $first;
				$cut_text = substr( $text, $position );
				$gap      = strpos( $cut_text, '"', 1 );
				if ( $gap !== false ) {
					$album_id   = substr( $cut_text, 0, $gap );
					$album_ids .= $album_id;
				}
				$text = $cut_text;
			}
		}

		if ( strlen( $album_ids ) !== 0 ) {
			// If in the content search albums are detected these need to be shown on the Worldmap.
			$options['worldmap'] = 1;
			$options['status']   = 'auto worldmap';

			// Returns array of unique album ids.
			$album_ids_array = array_unique( explode( ',', rtrim( $album_ids, ',' ) ) );

			global $nggdb;
			$gallery_and_album_ids = array();

			foreach ( $album_ids_array as $aid ) {
				// SQL: get album data using NextGEN Class.
				$album = $nggdb->find_album( $aid );
				// Array key "gallery_ids" contains all gallery and album ids that belong to that album.
				foreach ( $album->gallery_ids as $g_a_id ) {
					$gallery_and_album_ids[] = $g_a_id;
				}
			}

			foreach ( $gallery_and_album_ids as $id ) {
				if ( is_numeric( $id ) ) {
					$all_gallery_ids[] = $id;
				} else {
					$all_album_ids[] = ltrim( $id, 'a' );
				}
			}

			if ( $options['auto_include'] === 'galleries' ) {
				// Clears data in albums to only show galleries.
				$all_album_ids = array();
			}
			if ( $options['auto_include'] === 'albums' ) {
				// Clears data in galleries to only show albums.
				$all_gallery_ids = array();
			}
		} elseif ( $options['auto_include'] !== 'all_auto' ) {
			$all_gallery_ids = geo2_maps_get_id( $content );
		}
		// Galleries data search.
		if ( ! empty( $all_gallery_ids ) && $all_gallery_ids[0] !== '' ) {
			foreach ( $all_gallery_ids as $gid ) {
				// SQL: gets data for specific gallery id (%d replaces integer, %s string).
				$gallery_data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT path, filename, title, galdesc, gid, description, alttext, meta_data, pid, slug, pageid
						FROM $wpdb->ngg_pictures, $wpdb->ngg_gallery
						WHERE $wpdb->ngg_pictures.pid = $wpdb->ngg_gallery.previewpic 
						AND $wpdb->ngg_gallery.gid = %d",
						$gid
					),
					OBJECT
				);
				// Added meta_data to prevent warning in Local test.
				$gallery_data[0]->meta_data  = json_decode( base64_decode( $gallery_data[0]->meta_data ), true );
				$gallery_data[0]->image_url  = site_url() . '/' . $gallery_data[0]->path . $gallery_data[0]->filename;
				$gallery_data[0]->thumb_url  = site_url() . '/' . $gallery_data[0]->path . 'thumbs/' . $gallery_data[0]->meta_data['thumbnail']['filename'];
				$gallery_data[0]->image_path = ABSPATH . '/' . $gallery_data[0]->path . $gallery_data[0]->filename;
				$gallery_data[0]->thumb_path = ABSPATH . '/' . $gallery_data[0]->path . 'thumbs/' . $gallery_data[0]->meta_data['thumbnail']['filename'];

				// Search in WP Media Library for Caption string ( post_excerpt in SQL database ).
				if ( $options['show_wp_caption'] === 1 ) {
					$search_text3                = '%' .
					$wpdb->esc_like( $gallery_data[0]->filename );
					$gallery_data[0]->wp_caption = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT post_excerpt 
							FROM $wpdb->posts 
							WHERE $wpdb->posts.guid LIKE %s",
							$search_text3
						)
					);
				} else {
					$gallery_data[0]->wp_caption = '';
				}
				// Gets NextGEN Gallery set up link.
				if ( $options['open_lightbox'] === 0 ) {
					$gallery_data[0]->page_url = get_permalink( $gallery_data[0]->pageid );
				} else {
					$gallery_data[0]->page_url = '';
				}
				$all_data[] = $gallery_data[0];
			}
		}
		// Albums data search.
		if ( ! empty( $all_album_ids ) ) {
			// Gets ngg album database prefix.
			$wpdb->ngg_album = $wpdb->prefix . 'ngg_album';
			foreach ( $all_album_ids as $aid ) {
				// SQL: gets data for specific album id.
				$album_data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT albumdesc, id, name, filename, alttext, meta_data, galleryid, slug, pageid, pid
						FROM $wpdb->ngg_album, $wpdb->ngg_pictures
						WHERE $wpdb->ngg_album.id = %d 
						AND $wpdb->ngg_album.previewpic = $wpdb->ngg_pictures.pid",
						$aid
					),
					OBJECT
				);

				$album_path = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT path
						FROM $wpdb->ngg_gallery
						WHERE $wpdb->ngg_gallery.gid = %d",
						$album_data[0]->galleryid
					),
					OBJECT
				);
				// Parse it.
				$album_data[0]->path  = $album_path[0]->path;
				$album_data[0]->title = $album_data[0]->name;
				// To distinguish between galleries Id and albums Id I decided to add back prefix "a" to "id".
				$album_data[0]->id = 'a' . $album_data[0]->id;
				// Added meta_data to prevent warning in Local test.
				$album_data[0]->meta_data  = json_decode( base64_decode( $album_data[0]->meta_data ), true );
				$album_data[0]->image_url  = site_url() . '/' . $album_data[0]->path . $album_data[0]->filename;
				$album_data[0]->thumb_url  = site_url() . '/' . $album_data[0]->path . 'thumbs/' . $album_data[0]->meta_data['thumbnail']['filename'];
				$album_data[0]->image_path = ABSPATH . '/' . $album_data[0]->path . $album_data[0]->filename;
				$album_data[0]->thumb_path = ABSPATH . '/' . $album_data[0]->path . 'thumbs/' . $album_data[0]->meta_data['thumbnail']['filename'];

				// Search in WP Media Library for Caption string ( post_excerpt in SQL database ).
				if ( $options['show_wp_caption'] === 1 ) {
					$search_text3              = '%' . $wpdb->esc_like( $album_data[0]->filename );
					$album_data[0]->wp_caption = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT post_excerpt 
							FROM $wpdb->posts 
							WHERE $wpdb->posts.guid LIKE %s",
							$search_text3
						)
					);
				} else {
					$album_data[0]->wp_caption = '';
				}

				// Gets NextGEN album set up link.
				$album_page_url = get_permalink( $album_data[0]->pageid );
				if ( $album_page_url !== false ) {
					$album_data[0]->page_url = $album_page_url;
				} else {
					$album_data[0]->page_url = '';
				}
				$all_data[] = $album_data[0];
			}
		}

		if ( ! empty( $album_ids ) ) {
			return geo2_maps( $all_data, $options );
		} elseif ( $options['auto_include'] === 'all_auto' ) {
			return geo2_maps_search( $content, $options );
		} else {
			return '';
		}
	} else {
		return geo2_maps_search( $content, $options );
	}
}

/**
 * Searches the content and shows galleries.
 *
 * @since  2.0.0
 * @since  2.0.7 $content passed to function argument. $options['search'] removed. Empty return value added. Function geo2_maps_get_id() returns an array now.
 *
 * @see    function geo2_maps_check_content()
 * @param  string  $content WP page content.
 * @param  mixed[] $options An array of options.
 * @return string Javascript code creating a desired map.
 */
function geo2_maps_search( $content, $options ) {
	$gallery_ids_array = geo2_maps_get_id( $content );

	if ( ! empty( $gallery_ids_array ) && $gallery_ids_array[0] !== '' ) {
		foreach ( $gallery_ids_array as $str ) {
			if ( is_numeric( $str ) ) {
				$options['id'] .= $str . ',';
			}
		}
		$options['id'] = rtrim( $options['id'], ',' );

		return geo2_maps_data( $options );
	} else {
		return '';
	}
}

/**
 * Gets data via sql, used for geo2 id= shortcode
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.5 Section "Searches in a page content for galleries if no map content provided" supplemented with a condition for no route path or Route Mode disabled.
 * @since  2.0.6 "wp_caption" default value defined.
 * @since  2.0.7 PHP json_decode() function replaced unserialize() to prevent exception.$_SERVER['DOCUMENT_ROOT'] replaced with ABSPATH. Function geo2_maps_get_id() returns an array now.
 * @since  2.0.8 Gallery sorting added. Exclude is working again.
 * @since  2.0.9 Thumbnails full filename acquired from meta_data.
 *
 * @see    function geo2_maps_data_show(), function geo2_maps_shortcodes(), function geo2_maps_search()
 * @param  array $options Optional. An array of options.
 * @return string|object[] Javascript code creating a map|Array of pictures data for Ajax or Worldmap
 */
function geo2_maps_data( $options = null ) {
	// Checks if function is run in Ajax mode.
	// TODO: Run this directly. Not through this function.
	if ( $options['load_ajax'] === 1 && $options['ajax'] === 1 ) {
		return geo2_maps( 'geo2_ajax_mode', $options );
	}

	// Searches in a page content for galleries if no map content provided.
	if ( ! isset( $options['id'] ) && ! ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) ) {
		$content           = get_the_content();
		$gallery_ids_array = geo2_maps_get_id( $content );
	} else {
		/*
		* String may contain duplicates if the id was listed twice by mistake.
		* String may also contain tag names because in NextGEN Gallery 3.0.8 the same phrase is used for gallery ids and tags.
		* This is not a problem because tag names in search return nothing.
		*/
		$gallery_ids_array = array_unique( explode( ',', $options['id'] ) );
	}
	$all_picture_list = array();
	// Gets database prefix.
	global $wpdb;
	$wpdb->ngg_gallery  = $wpdb->prefix . 'ngg_gallery';
	$wpdb->ngg_pictures = $wpdb->prefix . 'ngg_pictures';
	foreach ( $gallery_ids_array as $gid ) {
		// SQL: gets gallery data.
		$picture_list = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pid, path, filename, title, galdesc, gid, description, meta_data, alttext, exclude, sortorder
		FROM $wpdb->ngg_pictures, $wpdb->ngg_gallery
		WHERE $wpdb->ngg_pictures.galleryid = %d AND $wpdb->ngg_gallery.gid = %d",
				$gid,
				$gid
			),
			OBJECT
		);

		foreach ( $picture_list as $key => $picture ) {
			// Exclude?
			if ( $picture->exclude === '1' ) {
				continue;
			}
			$picture_list[ $key ]->meta_data  = json_decode( base64_decode( $picture->meta_data ), true );
			$picture_list[ $key ]->image_url  = site_url() . '/' . $picture->path . $picture->filename;
			$picture_list[ $key ]->thumb_url  = site_url() . '/' . $picture->path . 'thumbs/' . $picture_list[ $key ]->meta_data['thumbnail']['filename'];
			$picture_list[ $key ]->image_path = ABSPATH . '/' . $picture->path . $picture->filename;
			$picture_list[ $key ]->thumb_path = ABSPATH . '/' . $picture->path . 'thumbs/' . $picture_list[ $key ]->meta_data['thumbnail']['filename'];

			$picture_list[ $key ]->sortorder = $picture->sortorder;
			// Search in WP Media Library for Caption string ( post_excerpt in SQL database ).
			if ( $options['show_wp_caption'] === 1 ) {
				$search_text3                     = '%' . $wpdb->esc_like( $picture->filename );
				$picture_list[ $key ]->wp_caption = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_excerpt
					FROM $wpdb->posts 
					WHERE $wpdb->posts.guid LIKE %s",
						$search_text3
					)
				);
			} else {
				$picture_list[ $key ]->wp_caption = '';
			}
			// Adds to an array.
			$all_picture_list[] = $picture_list[ $key ];
		}
	}
	// Sorts gallery.
	usort(
		$all_picture_list,
		function ( $a, $b ) {
			return $a->sortorder - $b->sortorder;
		}
	);
	// Below statement is needed when this function is called from geo2_maps_lightbox_callback() from Worldmap.
	if ( $options['status'] !== 'worldmap' ) {
		if ( $options['ajax'] === 2 ) {
			// Returns data for Ajax.
			return $all_picture_list;
		}
		return geo2_maps( $all_picture_list, $options );
	} else {
		// For status worldmap.
		return $all_picture_list;
	}
}

/**
 * Gets single picture data via sql, used for geo2 pid= shortcode
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.6 "wp_caption" default value defined.
 * @since  2.0.7 PHP json_decode() function replaced unserialize() to prevent exception. $_SERVER['DOCUMENT_ROOT'] replaced with ABSPATH.
 * @since  2.0.9 Thumbnails full filename acquired from meta_data.
 *
 * @see    function geo2_maps_data_single_show(), function geo2_maps_shortcodes()
 * @param  array $options Optional. An array of options.
 * @return string Javascript code creating a map.
 * @todo   Check if it would better to use nggdb::find_image
 */
function geo2_maps_data_single( $options = null ) {
	// Checks if function is run in Ajax mode.
	if ( $options['load_ajax'] === 1 && $options['ajax'] === 1 ) {
		return geo2_maps( 'geo2_ajax_mode', $options );
	}

	$picture_ids_array = array_unique( explode( ',', $options['pid'] ) );
	$all_picture_list  = array();
	// Gets database prefix.
	global $wpdb;
	$wpdb->ngg_gallery  = $wpdb->prefix . 'ngg_gallery';
	$wpdb->ngg_pictures = $wpdb->prefix . 'ngg_pictures';

	foreach ( $picture_ids_array as $pid ) {
		// SQL: gets gallery data.
		$picture_list = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pid, path, filename, title, galdesc, gid, description, meta_data, alttext, exclude
			FROM $wpdb->ngg_pictures, $wpdb->ngg_gallery
			WHERE $wpdb->ngg_pictures.galleryid = $wpdb->ngg_gallery.gid AND $wpdb->ngg_pictures.pid = %d",
				$pid
			),
			OBJECT
		);

		foreach ( $picture_list as $key => $picture ) {
			$picture_list[ $key ]->meta_data  = json_decode( base64_decode( $picture->meta_data ), true );
			$picture_list[ $key ]->image_url  = site_url() . '/' . $picture->path . $picture->filename;
			$picture_list[ $key ]->thumb_url  = site_url() . '/' . $picture->path . 'thumbs/' . $picture_list[ $key ]->meta_data['thumbnail']['filename'];
			$picture_list[ $key ]->image_path = ABSPATH . '/' . $picture->path . $picture->filename;
			$picture_list[ $key ]->thumb_path = ABSPATH . '/' . $picture->path . 'thumbs/' . $picture_list[ $key ]->meta_data['thumbnail']['filename'];

			// Searches in WP Media Library for Caption string ( post_excerpt in SQL database ).
			if ( $options['show_wp_caption'] === 1 ) {
				$search_text3                     = '%' . $wpdb->esc_like( $picture->filename );
				$picture_list[ $key ]->wp_caption = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_excerpt
					FROM $wpdb->posts 
					WHERE $wpdb->posts.guid LIKE %s",
						$search_text3
					)
				);
			} else {
				$picture_list[ $key ]->wp_caption = '';
			}
			// Adds to an array.
			$all_picture_list[] = $picture_list[ $key ];
		}
	}
	return geo2_maps( $all_picture_list, $options );
}

/**
 * Gets data for the Worldmap via sql, used for geo2 worldmap= shortcode
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.6 "wp_caption" and "page_url" default empty value defined.
 * @since  2.0.7 $_SERVER['DOCUMENT_ROOT'] replaced with ABSPATH."page_url" for albums statement corrected.
 * @since  2.0.9 Thumbnails full filename acquired from meta_data.
 *
 * @see    function geo2_maps_data_worldmap_show(), function geo2_maps_shortcodes()
 * @param  array $options Optional. An array of options.
 * @return string Javascript code creating a map.
 */
function geo2_maps_data_worldmap( $options = null ) {
	// Checks if function is run in Ajax mode.
	if ( $options['load_ajax'] === 1 && $options['ajax'] === 1 ) {
		return geo2_maps( 'geo2_ajax_mode', $options );
	}

	global $wpdb;

	// Gets database prefix.
	$wpdb->ngg_gallery  = $wpdb->prefix . 'ngg_gallery';
	$wpdb->ngg_pictures = $wpdb->prefix . 'ngg_pictures';

	$all_data = array();

	// Searches for albums data when selected.
	if ( $options['include'] !== 'galleries' ) {
		// Gets album and gallery database prefixes.
		$wpdb->ngg_album = $wpdb->prefix . 'ngg_album';

		// SQL: gets data for all album id.
		$album_data = $wpdb->get_results(
			"SELECT albumdesc, id, name, filename, alttext, meta_data, galleryid, pid, slug, pageid
			FROM $wpdb->ngg_album, $wpdb->ngg_pictures
			WHERE $wpdb->ngg_album.previewpic = $wpdb->ngg_pictures.pid",
			OBJECT
		);

		foreach ( $album_data as $key => $data ) {
			$album_path = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT path
				FROM $wpdb->ngg_gallery
				WHERE $wpdb->ngg_gallery.gid = %d",
					$data->galleryid
				),
				OBJECT
			);
			// Added meta_data to prevent warning in Local test.
			$album_data[ $key ]->meta_data  = json_decode( base64_decode( $data->meta_data ), true );
			$album_data[ $key ]->path       = $album_path[0]->path;
			$album_data[ $key ]->title      = $data->name;
			$album_data[ $key ]->image_url  = site_url() . '/' . $data->path . $data->filename;
			$album_data[ $key ]->thumb_url  = site_url() . '/' . $data->path . 'thumbs/' . $album_data[ $key ]->meta_data['thumbnail']['filename'];
			$album_data[ $key ]->image_path = ABSPATH . '/' . $data->path . $data->filename;
			$album_data[ $key ]->thumb_path = ABSPATH . '/' . $data->path . 'thumbs/' . $album_data[ $key ]->meta_data['thumbnail']['filename'];

			// Search in WP Media Library for Caption string ( post_excerpt in SQL database ).
			if ( $options['show_wp_caption'] === 1 ) {
				$search_text3                   = '%' . $wpdb->esc_like( $data->filename );
				$album_data[ $key ]->wp_caption = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_excerpt 
					FROM $wpdb->posts 
					WHERE $wpdb->posts.guid LIKE %s",
						$search_text3
					)
				);
			} else {
				$album_data[ $key ]->wp_caption = '';
			}
			// Gets NextGEN album set up link.
			$album_page_url = get_permalink( $album_data[ $key ]->pageid );
			if ( $album_page_url !== false ) {
				$album_data[ $key ]->page_url = $album_page_url;
			} else {
				$album_data[ $key ]->page_url = '';
			}
			$all_data[] = $album_data[ $key ];
		}
	}
	// Search for galleries data when selected.
	if ( $options['include'] !== 'albums' ) {
		// SQL: get data.
		$gallery_data = $wpdb->get_results(
			"SELECT path, filename, title, galdesc, gid, description, alttext, meta_data, pid, slug, pageid
			FROM $wpdb->ngg_pictures, $wpdb->ngg_gallery
			WHERE $wpdb->ngg_pictures.pid = $wpdb->ngg_gallery.previewpic",
			OBJECT
		);

		foreach ( $gallery_data as $key => $data ) {
			// Added meta_data to prevent warning in Local test.
			$gallery_data[ $key ]->meta_data  = json_decode( base64_decode( $data->meta_data ), true );
			$gallery_data[ $key ]->image_url  = site_url() . '/' . $data->path . $data->filename;
			$gallery_data[ $key ]->thumb_url  = site_url() . '/' . $data->path . 'thumbs/' . $gallery_data[ $key ]->meta_data['thumbnail']['filename'];
			$gallery_data[ $key ]->image_path = ABSPATH . '/' . $data->path . $data->filename;
			$gallery_data[ $key ]->thumb_path = ABSPATH . '/' . $data->path . 'thumbs/' . $gallery_data[ $key ]->meta_data['thumbnail']['filename'];

			// Search in WP Media Library for Caption string ( post_excerpt in SQL database ).
			if ( $options['show_wp_caption'] === 1 ) {
				$search_text3                     = '%' . $wpdb->esc_like( $data->filename );
				$gallery_data[ $key ]->wp_caption = $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT post_excerpt 
					FROM $wpdb->posts 
					WHERE $wpdb->posts.guid LIKE %s",
						$search_text3
					)
				);
			} else {
				$gallery_data[ $key ]->wp_caption = '';
			}
			// Gets NextGEN Gallery set up link.
			if ( $options['open_lightbox'] === 0 ) {
				$gallery_data[ $key ]->page_url = get_permalink( $gallery_data[ $key ]->pageid );
			} else {
				$gallery_data[ $key ]->page_url = '';
			}
			$all_data[] = $gallery_data[ $key ];
		}
	}
	return geo2_maps( $all_data, $options );
}

// Functions to embed a map into a theme - for theme developers.

/**
 * Creates standard map using php function.
 *
 * Specify which galleries using $options['id']
 * Echoes Javascript code creating a map.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended name.
 *
 * @param  array $options An array of options.
 */
function geo2_maps_data_show( $options = null ) {
	echo geo2_maps_data( $options );
}

/**
 * Creates a single pic map using php function.
 *
 * Specify which pictures using $options['pid']
 * Echoes Javascript code creating a map.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended name.
 *
 * @param  array $options An array of options.
 */
function geo2_maps_data_single_show( $options = null ) {
	echo geo2_maps_data_single( $options );
}

/**
 * Creates a Worldmap using php function.
 *
 * Specify galleries or/and albums using $options['include'] = "galleries", "albums" or "all"
 * Echoes Javascript code creating a map.
 *
 * @since  2.0.0
 *
 * @param  array $options Optional. An array of options.
 */
function geo2_maps_data_worldmap_show( $options = null ) {
	echo geo2_maps_data_worldmap( $options );
}
