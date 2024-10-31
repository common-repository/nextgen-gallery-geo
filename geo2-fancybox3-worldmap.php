<?php
/**
 * Function used to show the Fancybox 3 Lightbox in the Worldmap mode.
 *
 * Outputs a Javascript code amending Fancybox 3 default functionality
 * and graphics according to the settings on the Geo2 admin page.
 * There are small differences between this file and "geo2-fancybox3.php".
 * Changes include:
 * - variable $options['mid2'] changed to ['mid']
 * - removed parameter "includedPinIds" when function nggGeo2Map_.. is invoked
 * - if ( !clickedPin ) was removed.
 * - function geo2_maps_lightbox_ was added  to centre on a clicked pin on the map on the side panel
 *
 * @see        bing-map-worldmap.php
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Lightbox Functions
 * @since      2.0.0
 * @since      2.0.4 Function geo2_maps_slimbox2_options() amended.
 * @since      2.0.6 Function geo2_maps_fancybox3_options_worldmap() amended.
 * @since      2.0.7 Function geo2_maps_slimbox2_options() amended.
 * @author     Pawel Block &lt;pblock@op.pl&gt;
 * @copyright  Copyright ( c) 2019, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

// Security: Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function used to show the Fancybox 3 Lightbox in the Worldmap mode.
 *
 * Similar to geo2_fancybox3_options() in geo2-fancybox3.php.
 *
 * @since  2.0.0
 * @since  2.0.4 Capital letters removed from plugin $options keys.
 * @since  2.0.6 Amended.
 * @since  2.0.7 Incorrect "closeBtn" option corrected to "smallBtn".
 *
 * @param  string $geo2_maps_lightbox Collected pictures info. Refer to geo2_maps_lightbox_data() in bing_map_function.php.
 * @param  array  $picture_list An array of pictures data.
 * @param  array  $options Optional. An array of options.
 * @return string Javascript code.
 */
function geo2_maps_fancybox3_options_worldmap( $geo2_maps_lightbox, $picture_list, $options = null ) {
			$map_output = '( function( $ ) {';
			// Removes last comma.
			$geo2_maps_lightbox = rtrim( $geo2_maps_lightbox, ',' );

			$map_output .= '
		jQuery.fancybox3.open([ ' . $geo2_maps_lightbox . ' ], {';
	if ( $options['fancybox3_loop'] === 1 ) {
		$map_output .= '
			loop : true,'; }
		// Should allow caption to overlap the content ( do not display regardless of settings when side bar is active ).
	if ( $options['fancybox3_prevent_caption_overlap'] === 0 ) {
		$map_output .= '
			preventCaptionOverlap: false,'; }
		// Should display navigation arrows - not needed for mobile devices.
	if ( $options['fancybox3_arrows'] === 0 ) {
		$map_output .= '
			arrows: false, '; }
		// Should display counter at the top left corner ( do not display regardless of settings when side bar is active ).
	if ( $options['fancybox3_infobar'] === 0 ) {
		$map_output .= '
			infobar: false, '; }
		// Should display small close button over the content. Can be "true", "false", "auto". If "auto" - will be automatically enabled for "html", "inline" or "ajax" items.
	if ( $options['fancybox3_close_btn'] !== 'auto' ) {
		$map_output .= '
			smallBtn: ' . $options['fancybox3_close_btn'] . ','; }
		// Should display toolbar ( buttons at the top ). Can be "true", "false", "auto". If "auto" - will be automatically hidden if "closeBtn" is enabled.
	if ( $options['fancybox3_toolbar'] !== 'auto' ) {
		$map_output .= '
			toolbar: ' . $options['fancybox3_toolbar'] . ','; }
		$buttons = '';
	if ( $options['fancybox3_buttons_zoom'] === 1 ) {
		$buttons .= '
			"zoom",';
	}
	if ( $options['fancybox3_buttons_share'] === 1 ) {
		$buttons .= '
			"share",';
	}
	if ( $options['fancybox3_buttons_slideshow'] === 1 ) {
		$buttons .= '
			"slideShow",';
	}
	if ( $options['fancybox3_buttons_fullScreen'] === 1 ) {
		$buttons .= '
			"fullScreen",';
	}
	if ( $options['fancybox3_buttons_download'] === 1 ) {
		$buttons .= '
			"download",';
	}
	if ( $options['fancybox3_buttons_thumbs'] === 1 ) {
		$buttons .= '
			"thumbs",';
	}
	if ( $options['fancybox3_buttons_close'] === 1 ) {
		$buttons .= '
			"close",';
	}
	if ( strlen( $buttons ) !== 0 ) {
		// Removes last comma.
		$buttons     = rtrim( $buttons, ',' );
		$map_output .= '
				buttons: ['
			. $buttons . '
				],'; }
		// Disable right-click and use simple image protection for images.
	if ( $options['fancybox3_protect'] === 1 ) {
		$map_output .= '
 			protect: true,'; }
		// Data about images needs to be transferred to Fancybox 3 somehow. I'm using the same function which is creating content for infobox but translating it into javascript array and inserting into caption.
	$pic_desc = array();
	foreach ( $picture_list as $picture_data ) {
		$f3_options             = $options;
		$f3_options['lightbox'] = 'fancybox3';
		$pic_desc[]             = geo2_maps_pin_desc( $picture_data, $f3_options );
	}
	$pic_desc = wp_json_encode( $pic_desc );
	// Instance must be passed for caption.
	if ( $options['fancybox3_caption'] === 'bottom' ) {
		$map_output .= '
			idleTime : 0,
			
			caption : function( instance, item ) {
				var pic_desc = ' . $pic_desc . ';
				for ( var i = 0; i < pic_desc.length; i++ ) 
				{ 
					if ( Object.keys( pic_desc[i] )[0] === item.opts.imageId )
 					{
 						return pic_desc[i][item.opts.imageId];
 					}
				}
  		},';
	}
		$map_output .= '
			thumbs : {
				autoStart : ' . geo2_maps_value( $options['fancybox3_thumbs_autostart'] ) . ',
				axis: "y"
			},';

	if ( $options['fancybox3_slideshow_speed'] !== '3000' || $options['fancybox3_slideshow_autostart'] === 1 ) {
		$map_output .= '
				slideShow: {';
		if ( $options['fancybox3_slideshow_speed'] !== '3000' ) {
			if ( $options['fancybox3_slideshow_autostart'] === 1 ) {
				$map_output .= '
						autoStart: true,';
			}
			$map_output .= '
						speed: ' . $options['fancybox3_slideshow_speed'] . '';
		} else {
			$map_output .= '
						autoStart: true';
		}
		$map_output .= '
				},';
	}
	if ( $options['fancybox3_fullscreen_autostart'] === 1 ) {
		$map_output .= '
			fullScreen: {
				autoStart: true
			},'; }
	if ( $options['fancybox3_lang'] !== 'en' ) {
		$map_output .= '
			lang: "' . $options['fancybox3_lang'] . '",
			i18n: {';
		if ( $options['fancybox3_lang'] === 'pl' ) {
			$map_output .= '
					pl: {
						CLOSE: "Zamknij",
						NEXT: "Następne",
						PREV: "Poprzednie",
						ERROR: "Żądana treść nie może zostać załadowana. <br/> Proszę spróbuj później.",
						PLAY_START: "Rozpocznij pokaz slajdów",
						PLAY_STOP: "Zatrzymaj pokaz slajdów",
						FULL_SCREEN: "Pełny ekran",
						THUMBS: "Ikony",
						DOWNLOAD: "Ściągnij",
						SHARE: "Udostępnij",
						ZOOM: "Powiększ"
					}'; }
		if ( $options['fancybox3_lang'] === 'tr' ) {
			$map_output .= '
					tr: {
						CLOSE: "' . esc_html_e( 'Close', 'ngg-geo2-maps' ) . '",
						NEXT: "' . esc_html_e( 'Next', 'ngg-geo2-maps' ) . '",
						PREV: "' . esc_html_e( 'Previous', 'ngg-geo2-maps' ) . '",
						ERROR: "' . esc_html_e( 'The requested content cannot be loaded.', 'ngg-geo2-maps' ) . ' <br/> ' . esc_html_e( 'Please try again later.', 'ngg-geo2-maps' ) . '",
						PLAY_START: "' . esc_html_e( 'Start slideshow', 'ngg-geo2-maps' ) . '",
						PLAY_STOP: "' . esc_html_e( 'Pause slideshow', 'ngg-geo2-maps' ) . '",
						FULL_SCREEN: "' . esc_html_e( 'Full screen', 'ngg-geo2-maps' ) . '",
						THUMBS: "' . esc_html_e( 'Thumbnails', 'ngg-geo2-maps' ) . '",
						DOWNLOAD: "' . esc_html_e( 'Download', 'ngg-geo2-maps' ) . '",
						SHARE: "' . esc_html_e( 'Share', 'ngg-geo2-maps' ) . '",
						ZOOM: "' . esc_html_e( 'Zoom', 'ngg-geo2-maps' ) . '"
					}'; }
		$map_output .= '
				},';
	}
			$map_output .= '
				hash : true, // browser back button is closing the window
				index : indexNr
			} );' . "\n";

			// Enable thumbs horizontal scrolling.
			$fancybox3_thumbs_scroll = '
			var current = 0;
			// Pixel increment you wish on each wheel spin.
			var ScrollX_pixelPer = 10;
			jQuery(\'.fancybox3-thumbs-x\' ).on( "DOMMouseScroll mousewheel", function ( e ) {
				e.preventDefault();
				var maxScrollLeft = this.scrollWidth - this.clientWidth;
				// Get the scroll wheel value
				if ( e.type == \'mousewheel\' ) {
				var delta = ( parseInt( e.originalEvent.wheelDelta * -0.3) );
				}
				if ( e.type == \'DOMMouseScroll\' ) {
				var delta = ScrollX_pixelPer*( parseInt( e.originalEvent.detail ) );
				}
				// Increment/decrement current.
				current += delta;
				if ( current < 0 ) {current = 0; } 
				else if ( current > maxScrollLeft ) {current = maxScrollLeft; }
				// Apply the new position.
				jQuery( this ).scrollLeft( current );
    	} );';
			// Adds event listener to scroll horizontal thumbs preview
			// Variable to store current X scrolled position.
	if ( $options['fancybox3_thumbs_autostart'] === 0 ) {
		$map_output .= '
			$(\'.fancybox3-button--thumbs\' ).click( function() {';
		$map_output .= $fancybox3_thumbs_scroll . '
			} );';
	} else {
		$map_output .= $fancybox3_thumbs_scroll;
	}
			$map_output .= '
	}( jQuery ) );
';

			// In F3 I want to be able to switch between different images by clicking on them in the map.
			$map_output .= '
function geo2_maps_lightbox_' . $options['mid'] . '( imageSrc ) 
{
	( function( $ ) {
		var instance = $.fancybox3.getInstance();

		for ( var i = 0; i < instance.group.length; i++ ) {
			if ( instance.group[i].src == imageSrc )
			{
				instance.jumpTo( i, ' . $options['fancybox3_transitionDuration'] . ' );
				continue;
			}
		}
	}( jQuery ) );
}';

	return $map_output;
}
