<?php
/**
 * Function used to show the Fancybox Lightbox with a typical Bing map or a map
 * in the Worldmap mode.
 *
 * Outputs a Javascript code amending Fancybox default functionality
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
 * Function used to show the Fancybox Lightbox with a typical Bing map or a map
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
function geo2_maps_fancybox_options( $geo2_maps_lightbox, $options ) {
	// Removes last comma.
	$geo2_maps_lightbox = rtrim( $geo2_maps_lightbox, ',' );
	$map_output         = '
( function( $ ) {
$.fancybox([' . $geo2_maps_lightbox . '],	{'
	. ( $options['fancybox_show_close_button'] !== 1 ? '\'showCloseButton\'	: 0, ' : '' )
	. ( $options['fancybox_show_nav_arrows'] !== 1 ? '\'showNavArrows\'	: 0, ' : '' )
	. ( $options['fancybox_padding'] !== 10 ? '\'padding\'	: ' . $options['fancybox_padding'] . ', ' : '' )
	. ( $options['fancybox_margin'] !== 20 ? '\'margin\'	: ' . $options['fancybox_margin'] . ', ' : '' )
	. ( $options['fancybox_overlay_opacity'] !== 0.3 ? '\'overlayOpacity\': ' . $options['fancybox_overlay_opacity'] . ', ' : '' )
	. ( $options['fancybox_overlay_color'] !== '#666' || $options['fancybox_overlay_color'] !== '#666666' ? '\'overlayColor\'	: \'' . $options['fancybox_overlay_color'] . '\', ' : '' )
	. ( $options['fancybox_auto_scale'] !== 1 ? '\'autoScale\'	: false, ' : '' )
	. ( $options['fancybox_cyclic'] !== 1 ? '\'cyclic\' :  false, ' : '' )
	. ( $options['fancybox_title_show'] !== 1 ? '\'titleShow\' :  false, ' : '' )
	. ( $options['fancybox_title_position'] !== 'outside' ? '\'titlePosition\' : \'' . $options['fancybox_title_position'] . '\', ' : '' ) . '
  \'titleFormat\' : function(title, currentArray, currentIndex, currentOpts) {
    return \'<span id="fancybox-title-over"><b>\' + title + \'</b>Image \' +  (currentIndex + 1) + \' / \' + currentArray.length + \'</span>\'; },
  \'onComplete\'	:	function() {
    $( "#fancybox-wrap" ).hover( function() {
      $( "#fancybox-title" ).show();
    }, function() {
      $( "#fancybox-title" ).hide();
    });
  },
  \'type\': \'image\',
  \'index\': indexNr
  });
  return false;
} (jQuery) );';
	return $map_output;
}
