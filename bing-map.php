<?php
/**
 * Main function used to create a typical Bing map with images linked to a selected Lightbox.
 *
 * Outputs a Javascript code. Creates an embed map with pictures and a small map on
 * the Fancybox 3 side caption panel.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Functions
 * @since      2.0.0 Function geo2_maps() amended and moved from plugin.php.
 * @since      2.0.4 Function geo2_maps() amended.
 * @since      2.0.6 Function geo2_maps() amended.
 * @since      2.0.7 Function geo2_maps_lightbox_data() amended.
 * @since      2.0.8 Function geo2_maps() amended.

 * @author     Pawel Block &lt;pblock@op.pl&gt;
 * @copyright  Copyright (c) 2023, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

// Security Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main function used to create a typical Bing map with images linked to a selected Lightbox.
 *
 * @since  1.0.0
 * @since  2.0.0 Moved from plugin.php, amended and supplemented with additional code.
 * @since  2.0.4 Map options amended: showLocateMeButton, showTermsLink, showZoomButtons added, showCopyright, showLogo amended. JS function closeInfobox_...() condition added to load also for Route Mode
 * @since  2.0.6 Variable $worldmap_echo defined instead of referred to with "$worldmap_echo. =...". Enclosing bracket moved behind it. Command get_option removed.
 * @since  2.0.7 Unused parameter removed from function reference geo2_maps_lightbox_data(). The callback function is passed directly to the loadModule of the Minimap.JS function pushpinClicked_ and geo2_Infobox_ corrected.
 * @since  2.0.8 Exif data not acquired if not needed.
 *
 * @see    function geo2_maps_data(), function geo2_maps_data_single(), function geo2_maps_check_content() in functions.php.
 * @param  array|string $picture_list An array of pictures data|'geo2_ajax_mode'.
 * @param  array        $options Optional. An array of options.
 * @return string Javascript code.
 */
function geo2_maps( $picture_list, $options = null ) {
	// Creates a random number to make map parameters & functions unique. 'mid' must be a number.
	if ( empty( $options['mid'] ) || ! is_numeric( $options['mid'] ) ) {
		$options['mid'] = wp_rand( 0, 999 );
	}

	if ( $options['status'] === 'auto' ) {
		$stat_text = ' - Auto Map';
	} elseif ( $options['status'] === 'auto worldmap' ) {
		$stat_text = ' - Auto Worldmap';
	} elseif ( $options['status'] === 'worldmap' ) {
		$stat_text = ' - Worldmap';
	} elseif ( $options['status'] === 'pictures_map' ) {
		$stat_text = ' - Selected Pictures Map';
	} elseif ( $options['ajax'] === 1 && $options['ajax'] === 1 ) {
		$stat_text = ' - Map on Demand (Ajax Mode)';
	} else {
		$stat_text = '';
	}
	$map_output = "\n\n" . '<!-- Start NGG Geo2 Maps' . $stat_text . ' -->' . "\n" . '
<script type="text/javascript">' . "\n";

	$map_output .= '
var map_' . $options['mid'] . ',
    layer_' . $options['mid'] . ',
    hoverLayer_' . $options['mid'] . ',
    pins_' . $options['mid'] . ' = [],
    locs_' . $options['mid'] . ' = [],
    map_bounds_' . $options['mid'] . ',
    myCredentials = "' . $options['geo_bing_key'] . '",
    center_' . $options['mid'] . ';' . "\n";
	// Center is used to set up map to the correct clicked pin after full screen map is closed.

	// Custom map - not yet working - for potential future implementation.
	if ( strlen( $options['custom_map'] ) !== 0 ) {
		$map_output .= $options['custom_map'];
	}

	$map_output .= '
function nggGeo2Map_' . $options['mid'] . '( includedPinIds, clickedImageId) 
{
	map_' . $options['mid'] . ' = new Microsoft.Maps.Map( "#geo2_maps_map_' . $options['mid'] . '",
	{
		credentials: myCredentials,
		zoom: 9,
		mapTypeId: Microsoft.Maps.MapTypeId.' . $options['map'] . ',
		showDashboard: ' . geo2_maps_value( $options['dashboard'] ) . ',
		showLocateMeButton: ' . geo2_maps_value( $options['locate_me_button'] ) . ',
		showScalebar: ' . geo2_maps_value( $options['scalebar'] ) . ',
		showCopyright: ' . geo2_maps_value( $options['copyright'] ) . ',
		showTermsLink: ' . geo2_maps_value( $options['terms_link'] ) . ',
		showLogo: ' . geo2_maps_value( $options['logo'] ) . ' // undocumented but it works.
		// CustomMapStyle: myStyle
	} );
	if ( typeof layer_' . $options['mid'] . ' === "undefined" ) 
	{
		layer_' . $options['mid'] . ' = new Microsoft.Maps.Layer();
		hoverLayer_' . $options['mid'] . ' = new Microsoft.Maps.Layer();
	}
	map_' . $options['mid'] . '.layers.insert( layer_' . $options['mid'] . ' );
	map_' . $options['mid'] . '.layers.insert( hoverLayer_' . $options['mid'] . ' );';

	// Mini Map mode.
	if ( $options['minimap'] === 1 ) {
		// MinimapModule( map, credentials, atStart, style, height, width, topOffset, sideOffset.
		$map_output .= '
		Microsoft.Maps.registerModule( "MinimapModule", "' . GEO2_MAPS_DIR_URL . '/minimap-module/minimap-module.js", { styleURLs: ["' . GEO2_MAPS_DIR_URL . '/minimap-module/minimap-module.css"] } );
		Microsoft.Maps.loadModule( "MinimapModule",
			function () {
				new MinimapModule(
					map_' . $options['mid'] . ',
					myCredentials,
					' . geo2_maps_value( $options['minimap_show_at_start'] ) . ',
					Microsoft.Maps.MapTypeId.' . $options['minimap_type'] . ',
					"' . $options['minimap_height'] . '",
					"' . $options['minimap_width'] . '",
					"' . $options['minimap_top_offset'] . '",
					"' . $options['minimap_side_offset'] . '"
				);
			}
		);
	';
	}

	/*
		 * Adds MIME types to WP Media Settings:
		 * Gpx => 'application/gpx+xml'
		 * xml => 'application/xml'
		 * kml   =>  'application/vnd.google-earth.kml+xml'
		 * kmz   =>  'application/vnd.google-earth.kmz'
		 * You can use i.e. Enhanced Media Library plugin
		 *
		 * There are two ways to load data using GeoXml Module:
		 * GeoXml class with full control ( used below )
		 */
	if ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) {
		// Load the GeoXml module - GeoXml class.
		// For GeoXmlLayer Color override works with XML files and does not with KMZ. GeoXml class overrides both type.
		// readFromUrl( urlString: string, options: GeoXmlReadOptions, callback: ( data: GeoXmlDataSet ) => void) - backup code.
		$map_output .= '
	Microsoft.Maps.loadModule( \'Microsoft.Maps.GeoXml\', function() {
 		Microsoft.Maps.GeoXml.readFromUrl( "' . $options['xmlurl'] . '", null, function ( data ) {
					 geo2_maps_renderXml_' . $options['mid'] . '( data );
			 } );
	} );
	';
	}

	// Initiates empty variables.
	$geo2_maps_lightbox = '';
	$pins               = '';

	// Check if pins_'.$options['mid'].' is empty and only then create pins/thumbs.
	$map_output .= '
	if ( pins_' . $options['mid'] . '.length === 0 ) {';

	if ( $picture_list === 'geo2_ajax_mode' ) {
		if ( $options['thumb'] === 1 ) {
			$pins .= '{geo2_map_data}		} );';
		} else {
			$pins .= '{geo2_map_data}';
		}
		$geo2_maps_lightbox .= '{geo2_infobox_data}';
		$picture_nr_total    = 2;
	} else {
		// Counts pictures with geodata, $picture_nr_total counts all pictures, used for lightbox.
		$picture_nr       = 0;
		$picture_nr_total = 0;

		foreach ( $picture_list as $picture_data ) {
			// If not Worldmap.
			if ( $options['worldmap'] !== 1 ) {
				// Get exif information ( needed for old galleries, created before NGG stored meta_data ).
				if ( $options['exif'] === 1 && ( ( $options['lightbox'] === 'fancybox3' && $options['fancybox3_caption'] !== 'no' ) || $options['lightbox'] === 'infobox' ) ) {
					if ( empty( $picture_data->meta_data ) ) {
						$picture_data = geo2_maps_exif( $picture_data );
					} elseif ( ! empty( $picture_data->meta_data['created_timestamp'] ) ) {
						// NextGEN stores date-time as Unix timestamp using PHP function strtotime().
						if ( is_numeric( $picture_data->meta_data['created_timestamp'] ) ) {
							$picture_data->meta_data['created_timestamp'] = gmdate( 'Y-m-d H:i:s', $picture_data->meta_data['created_timestamp'] );
						}
					}
				}
				// Create info for lightbox.
				$geo2_maps_lightbox .= geo2_maps_lightbox_data( $picture_data, $options['lightbox'] );

				// Get exif-geolocation.
				$picture_data->gps = geo2_maps_coordinates( $picture_data->image_path );
				// Show only photos with gps - for these collect lightbox data and create pins.
				if ( is_array( $picture_data->gps ) ) {
					// Adds pin.
					$pins .= geo2_maps_add_pin( $picture_data, $picture_nr, $picture_nr_total, $options );
					++$picture_nr;
				}
				++$picture_nr_total;
			} else {
				$albums_not_found    = 'Galleries with no geolocation: ';
				$albums_not_found_nr = 0;

				// Get exif-geolocation from preview picture.
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
				} else {
					if ( $albums_not_found_nr > 0 ) {
						$albums_not_found .= ', ';
					}
					$albums_not_found .= $picture_data->title;
					++$albums_not_found_nr;
				}
				++$picture_nr_total;
			}
		}
		if ( $options['worldmap'] !== 1 && $options['lightbox'] === 'slimbox2' ) {
			$geo2_maps_lightbox = rtrim( $geo2_maps_lightbox, ', ' );
		}
	}

	if ( $options['thumb'] === 1 ) {
		$execute     = '
			locationRectFromLocations_' . $options['mid'] . '( map_' . $options['mid'] . ' );';
		$execute    .= '
			layer_' . $options['mid'] . '.add( pins_' . $options['mid'] . ' );' . "\n";
		$pos         = strrpos( $pins, '		} );' );
		$map_output .= substr_replace( $pins, $execute, $pos, 0 );
	} else {
		$map_output .= $pins;
	}

	// Push pins to map.
	if ( $options['thumb'] !== 1 ) {
		$map_output .= '
		locationRectFromLocations_' . $options['mid'] . '( map_' . $options['mid'] . ' );';
		$map_output .= '
		layer_' . $options['mid'] . '.add( pins_' . $options['mid'] . ' );' . "\n";
	}

	// Create one or many Infoboxes.
	if ( $options['lightbox'] === 'infobox' || ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) ) {
		$map_output .= '
		infobox_' . $options['mid'] . ' = new Microsoft.Maps.Infobox(  map_' . $options['mid'] . '.getCenter(), {
					visible: false
		} );
		infobox_' . $options['mid'] . '.setMap( map_' . $options['mid'] . ' );
		';
	}

	$map_output .= '
	}
}' . "\n";

	$map_output .= '
function locationRectFromLocations_' . $options['mid'] . '( map )
{
	if ( pins_' . $options['mid'] . '.length === 0 ) {
		return;
	}
	map_bounds_' . $options['mid'] . ' = Microsoft.Maps.LocationRect.fromLocations( locs_' . $options['mid'] . ' );
	map.setView( { bounds: map_bounds_' . $options['mid'] . ', padding: 40 } );
	' . ( $options['bev'] === 1 ? geo2_maps_is_bev_available( $options['map'] ) : '' ) . '
}';

	// Bring forward pin/thumb when mouse is over an image or pin.
	$map_output .= '
function bringForwardOnHover_' . $options['mid'] . '( pin ) 
{
	Microsoft.Maps.Events.addHandler( pin, "mouseover", function ( e ) {';
	$map_output .= '
		layer_' . $options['mid'] . '.add( hoverLayer_' . $options['mid'] . '.getPrimitives() );
		layer_' . $options['mid'] . '.remove( pin );
		// Moves pin to hover layer.
		hoverLayer_' . $options['mid'] . '.clear();
		hoverLayer_' . $options['mid'] . '.add( pin );';
	$map_output .= '
	} );
}' . "\n";

	// Function which closes Infoboxes.
	if ( $options['lightbox'] === 'infobox' || ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) ) {
		$map_output .= '
function closeInfobox_' . $options['mid'] . '( pid ) {
	infobox_' . $options['mid'] . '.setOptions( { visible: false } );
}';
	}

	// Function which shows Infoboxes.
	if ( $options['lightbox'] === 'infobox' &&
		( ( $options['open_lightbox'] === 1 && $options['worldmap'] === 1 ) ||
			$options['worldmap'] === 0 ) || ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) )
	) {
		$map_output .= '
function geo2_Infobox_' . $options['mid'] . '( o ) {
	if ( o.primitive instanceof Microsoft.Maps.Pushpin ) {
		var infoboxLocation = o.target.getLocation();
	} else {
		var infoboxLocation = o.location;
	}
			';
		if ( $options['worldmap'] === 0 ) {
			$map_output .= '
	var pid = o.target.metadata.pid;';
		} else {
			$map_output .= '
	var pid = ( o.target.metadata.aid ? o.target.metadata.aid : o.target.metadata.gid );';
		}

		$infobox = 'infobox_' . $options['mid'];

		$map_output .= '
	// Makes sure the infobox has metadata to display.
	if ( o.target.metadata ) {';
		if (
			$options['infobox_title_over'] === 1 ||
			empty( $options['infobox_width'] ) ||
			empty( $options['infobox_height'] )
		) {
			$map_output .= '
		var imgWidth = o.target.metadata.thumb_width,
		    imgHeight = o.target.metadata.thumb_height;' . ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ? '
		if (imgWidth == "256px" || imgWidth == "0") {
			var maxInfoboxWidth = "256px", // Default Microsoft max width for Infobox.
			    maxInfoboxHeight = "none",
			    maxDescHeight = "256px";
		} else if ( imgWidth == "auto" ) {
			var maxInfoboxWidth = "none",
			    maxInfoboxHeight = "none",
			    maxDescHeight = "256px";
		} else {' : '' ) . '
			// Portrait
			if ( imgWidth < imgHeight ) {' . ( empty( $options['infobox_width'] ) ? '
				var maxInfoboxWidth = imgWidth + imgHeight + "px";' : '' ) . ( empty( $options['infobox_height'] ) ? '
				var maxInfoboxHeight = imgHeight + "px",
						maxDescHeight = imgHeight + "px";' : '' ) . '
			// Landscape
			} else if ( imgWidth > imgHeight ) {' . ( empty( $options['infobox_width'] ) ? '
				var maxInfoboxWidth = imgWidth + "px";' : '' ) . ( empty( $options['infobox_height'] ) ? '
				var maxInfoboxHeight = imgWidth + imgHeight + "px",
						maxDescHeight = imgWidth + "px";' : '' );

			if ( $options['worldmap'] === 0 ) {
				$map_output .= '
			// Square
			} else {' . ( empty( $options['infobox_width'] ) ? '
				var maxInfoboxWidth = imgWidth + imgHeight + "px";' : '' ) . ( empty( $options['infobox_height'] ) ? '
				var maxInfoboxHeight = imgHeight + "px",
						maxDescHeight = imgHeight + "px";' : '' ) . '
			}';
			}
			if ( $options['worldmap'] === 1 ) {
				$map_output .= '
			// Square
			} else {' . ( empty( $options['infobox_width'] ) ? '
				var maxInfoboxWidth = imgWidth + "px";' : '' ) . ( empty( $options['infobox_height'] ) ? '
				var maxInfoboxHeight = imgWidth + imgHeight + "px",
						maxDescHeight = imgHeight + "px";' : '' ) . '
			}';
			}
			$map_output .= ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ? '
		}' : '' );
		}
		$map_output .= '
		' . $infobox . '.setOptions( {
				location: infoboxLocation,
				htmlContent: o.target.metadata.HTMLcontent' . ( empty( $options['infobox_width'] ) ? '.replace( \'{maxWidth}\', maxInfoboxWidth )' : '' ) . ( empty( $options['infobox_height'] ) ? '.replace( \'{maxHeight}\', maxInfoboxHeight ).replace( \'{maxDescHeight}\', maxDescHeight )' : '' ) . ( $options['infobox_title_over'] === 1 ? '.replace(\'{ratio}\', imgHeight/imgWidth ).replace( \'{imgWidth}\', imgWidth + "px" )' : '' ) . ',
				visible: true
		} );

		var mainBox = jQuery( "#geo2_InfoboxCustom_" + pid ).parent().parent().parent();
		mainBox.css( "top" , "0" );';
		if ( empty( $options['infobox_height'] ) ) {
			/*
			 * Chrome, Chromium Edge, FireFox and Opera browser
			 * do not anchor Infobox always in the same place.
			 * Bing Maps V8 online documentation is inconsistent
			 * but anchor is placed in the bottom-left corner of
			 * Infobox when HTMLContent is specified by offsetting it
			 * from the default top-left corner position.
			 * Mistake happens when HTML content is loaded alway
			 * for the first time and sometimes randomly later.
			 * IE & old Edge always correctly position Infobox.
			 */
			$map_output .= '
		// IE
		var isIE = /*@cc_on!@*/false || !!document.documentMode;
		//Edge 20+
		var isEdge = !isIE && !!window.StyleMedia;
		if ( ( isEdge != true ) && ( isIE != true ) ) {
			// Checks if anchor is in correct place
			var DescHeight = Math.round( jQuery("#geo2_infobox_desc_" + pid  ).innerHeight() );
			var anchorY = ' . $infobox . '.getAnchor().y;

			// Corrects landscape or square images only when anchor is in wrong place
			if ( imgHeight <' . ( $options['worldmap'] === 1 ? '=' : '' ) . ' imgWidth ) {
				var InfoboxHeight = imgHeight + DescHeight;
				if ( anchorY != InfoboxHeight ) 
				{
					var offset = mainBox.offset().top - imgHeight;
					mainBox.offset( { top: offset } );
				} else {
					mainBox.offset( { top: "0px" } );
				}
			} else {
				var InfoboxHeight = imgHeight;
				if ( DescHeight != InfoboxHeight && anchorY != InfoboxHeight ) 
				{
					var offset = mainBox.offset().top + DescHeight - imgHeight;
					mainBox.offset( { top: offset } );
				} else {
					mainBox.offset( { top: "0px" } );
				}
			}
		}';
		}

		if ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) {
			$map_output .= '
		jQuery( "#infoboxImg_" + pid ).on( \'load\', function() {
				if ( jQuery( this ).attr("src").includes("empty.gif") ) {' . ( $options['infobox_title_over'] === 1 ? '
					var w = jQuery( "#geo2_infobox_title_" + pid + " .geo2_infobox_title_text" ).innerWidth();
					var h = jQuery( "#geo2_infobox_title_" + pid + " .geo2_infobox_title_text" ).innerHeight();
					var descWidth = jQuery( "#geo2_infobox_desc_" + pid ).innerWidth();
					if ( w < 256 || descWidth < 256 ) {
						if ( descWidth > w ) {
							w = descWidth;
						}
						jQuery( "#geo2_InfoboxCustom_" + pid  ).css( "max-width", w + 1 );
					}
					jQuery( this ).width( w );
					jQuery( this ).height( h );
					document.getElementById( "geo2_infobox_title_" + pid ).style.setProperty( "--ratio", h/w );'
				: '
					jQuery( this ).width( imgWidth );
					jQuery( this ).height( imgHeight );
					var w = jQuery( "#geo2_infobox_desc_" + pid ).innerWidth();
					jQuery( "#geo2_InfoboxCustom_" + pid  ).css( "max-width", w + 1 );' ) . '
				} else {
					if ( jQuery( "#geo2_InfoboxCustom_" + pid  ).css( "max-width" ) == "none" ) {
						var w = jQuery( this ).width();
						var h = jQuery( this ).height();
						if ( w >= h ) {
							jQuery( "#geo2_InfoboxCustom_" + pid  ).css( "max-width", w ) 
							jQuery( this ).css( "width", "auto" );
						} else {
							jQuery( "#geo2_infobox_title_" + pid ).parent().css( "max-width", w );
							jQuery( this ).css( "height", "auto" );
						}' . ( $options['infobox_title_over'] === 1 ? '
						document.getElementById( "geo2_infobox_title_" + pid ).style.setProperty( "--ratio", h/w );' : '' ) . '
					}
				}
				//imgWidth = jQuery( this ).width();  // this is not doing anything?!
				//imgHeight = jQuery( this ).height();
		} );';
		}

		$map_output .= '
	}
}';
	}

	// Code executes when pushpin is clicked - shows Infobox or Lightbox with data.
	$map_output .= '
function pushpinClicked_' . $options['mid'] . '( o ) {
	if ( o.target.metadata.aid ) {
		var pushpin = "album";
	} else {
		var pushpin = "gallery";
	}
	var pageURL = o.target.metadata.pageURL,
			homeURL = window.location.href,
			slug = o.target.metadata.slug
			error_message = "Specify a page link for \""+o.target.metadata.title+"\" in this NextGEN "+pushpin+" settings.";';
	// Links to a gallery or album - open in the same page.
	// TODO: Check why pageURL permalink is sometimes like: ...?page_id=137.
	if ( $options['open_lightbox'] === 0 && $options['worldmap'] === 1 ) {
		$map_output .= '
	if ( homeURL != pageURL ) {
		if ( !homeURL.endsWith( slug ) ) {
			window.location = pageURL;
		}
		// Window.open( url ); to open in new window
	} else {
		windowURL = jQuery( ".ngg-album-link > a[href$=\'"+slug+"\']" ).attr( "href" );
		if ( windowURL ) {
			window.location = windowURL;
		} else {
			console.log( error_message )
		}
	}';
	}
	// Shows Lightbox.
	if ( $options['open_lightbox'] === 1 && $options['lightbox'] !== 'infobox' && $options['worldmap'] === 1 ) {
		$map_output .= '
	if ( o.target.metadata.aid )
	{
		if ( homeURL != pageURL )
		{
			window.location = pageURL; // window.open( url ); to open in new window
		} else {
			windowURL = jQuery( ".ngg-album-link > a[href$=\'"+slug+"\']" ).attr( "href" );
			if ( windowURL ) {
				window.location = windowURL;
			} else {
			console.log( error_message )
		}
		}
	} else {
		geo2_maps_lightbox_ajax( o.target.metadata.nonce );
	}';
	}
	// Shows Infobox.
	if ( $options['open_lightbox'] === 1 && $options['lightbox'] === 'infobox' && $options['worldmap'] === 1 ) {
		$map_output .= '
	geo2_Infobox_' . $options['mid'] . '( o );';
	}
	if ( $options['worldmap'] === 0 ) {
		if ( $options['lightbox'] === 'infobox' ) {
			$map_output .= '
	geo2_Infobox_' . $options['mid'] . '( o );';
		} else {
			$map_output .= '
	geo2_maps_lightbox_' . $options['mid'] . '( o.target.metadata.picture_nr );';
		}
	}
	$map_output .= '
}' . "\n";

	// Set the URL of the geo XML file as the data source of the layer.
	if ( $options['route'] === 1 && ! empty( $options['xmlurl'] ) ) {
		$path_options             = $options;
		$path_options['lightbox'] = 'infobox';
		$path_options['status']   = 'route';
		// 'title' and 'galdesc' is not needed for route mode. Added to avoid warning "undefined".
		$path_picture_data = (object) array(
			'title'       => '',
			'galdesc'     => '',
			'alttext'     => '{title}',
			'description' => '{description}',
			'pid'         => '{path_id}',
			'thumb_url'   => '{src}',
		);
		$map_output       .= '
function geo2_maps_renderXml_' . $options['mid'] . '( data ) {
	// Adds all shapes that are not in layers to the map
	if ( data.shapes ) {
		for ( var i = 0, len = data.shapes.length; i < len; i++ ) {
			if ( data.shapes[i] instanceof Microsoft.Maps.Pushpin ) {
				data.shapes[i].setOptions( {'
			. ( empty( $options['pin_color'] ) ? '' : 'color: "' . $options['pin_color'] . '"' ) . '} );
			} else if ( data.shapes[i] instanceof Microsoft.Maps.Polyline ) {
				data.shapes[i].setOptions( { 
										strokeColor: "' . $options['route_color'] . '",
										strokeThickness: ' . $options['route_width'] . '} );
			} else if ( data.shapes[i] instanceof Microsoft.Maps.Polygon ) {
				data.shapes[i].setOptions( { 
										fillColor: "' . $options['route_polygon_fillcolor'] . '",
										strokeColor: "' . $options['route_polygon_color'] . '",
										strokeThickness: ' . $options['route_polygon_width'] . '} );
			}
		}
		var l = new Microsoft.Maps.Layer();
		l.add( data.shapes );
		Microsoft.Maps.Events.addHandler( l, \'click\', geo2_maps_shapeClicked_' . $options['mid'] . ' );
		map_' . $options['mid'] . '.layers.insert( l );
	}
	if ( data.layers ) {
			for ( var i = 0, len = data.layers.length; i < len; i++ ) {
					if ( data.layers[i] instanceof Microsoft.Maps.Layer ) {
							Microsoft.Maps.Events.addHandler( data.layers[i], \'click\', geo2_maps_shapeClicked_' . $options['mid'] . ' );
					}
					map_' . $options['mid'] . '.layers.insert( data.layers[i] );
			}
	}
	if ( data.screenOverlays ) {
			for ( var i = 0, len = data.screenOverlays.length; i < len; i++ ) {
					map_' . $options['mid'] . '.layers.insert( data.screenOverlays[i] );
			}
	}
	if ( data.summary && data.summary.bounds ) {
			if ( map_bounds_' . $options['mid'] . ' ) {
					map_bounds_' . $options['mid'] . ' = Microsoft.Maps.LocationRect.merge( map_bounds_' . $options['mid'] . ', data.summary.bounds );
			} else {
					map_bounds_' . $options['mid'] . ' = data.summary.bounds;
			}
			map_' . $options['mid'] . '.setView( { bounds: map_bounds_' . $options['mid'] . ', padding: 40 } );
	}
}

function geo2_maps_shapeMetadata_' . $options['mid'] . '( shape, id, title, description ) {
	var HTMLobject = jQuery( description );
	amended_description = description.replace(/<img(.+?)\/>/, "").replace(/(<br>){1,}/, "").replace(/(<br>){1,}$/, "");
	var src = HTMLobject.filter("img:first").attr("src");
	var w;
	var h;
	if ( !src ) {
		src = "' . GEO2_MAPS_DIR_URL . '/img/empty.gif";' . ( $options['infobox_title_over'] === 1 ? '
		w = "256px";
		h = "auto";' : '
		w = "0";
		h = "0";' ) . '
	} else {
		w = "auto";
		h = "auto";
	}
	var height = ' . ( ! empty( $options['infobox_height'] ) ? $options['infobox_height'] : 'h' ) . ';
	var width = ' . ( ! empty( $options['infobox_width'] ) ? $options['infobox_width'] : 'w' ) . ';
	var HTMLstring = \'' . geo2_maps_pin_desc( $path_picture_data, $path_options ) . '\';
	var HTMLcontent = HTMLstring.replace( /{title}/g, title ).replace( /{description}/g, amended_description ).replace( /{path_id}/g, "path_" + id ).replace( /{src}/g, src );
	shape.metadata = {
				title: title,
				thumb_height: height,
				thumb_width: width,
				description: description,
				HTMLcontent: HTMLcontent,
				src: src,
				pid: "path_" + id,
				gid: "path_" + id
	};
	return shape;
}

function geo2_maps_shapeClicked_' . $options['mid'] . '( o ) {
	var shape = o.primitive;
	var title = shape.metadata.title || \'\';
	var description = shape.metadata.balloonDescription || shape.metadata.description || \'\';
	if ( title !== \'\' || description !== \'\' ) {
		o.primitive = geo2_maps_shapeMetadata_' . $options['mid'] . '( shape, i, title, description );
		geo2_Infobox_' . $options['mid'] . '( o );
	}
}
';
	}

	// Function for Thumbs and Pushpins.
	$map_output .= '
' . geo2_maps_thumbnail_function( $options, $picture_nr_total );

	// Show div box & start map.
	$map_output .= '
</script>
	<div id="geo2_map_' . $options['mid'] . '_placeholder">
		<div class="geo2_maps_map" style="width: ' . $options['map_width'] . '; height: ' . $options['map_height'] . ';">
			<div id="geo2_fs_' . $options['mid'] . '" class="geo2_fullscreen_icon geo2_fs_out">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
					<path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"></path>
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
					<path d="M5 16h3v3h2v-5H5zm3-8H5v2h5V5H8zm6 11h2v-3h3v-2h-5zm2-11V5h-2v5h5V8z"></path>
				</svg>
			</div>
			<div id="geo2_maps_map_' . $options['mid'] . '"></div>
		</div>
	</div>
<script>' . "\n \n";

	// Adds lightbox effect. Not needed for Worldmap.
	if ( $options['worldmap'] === 0 && $options['lightbox'] !== 'no' ) {
		$map_output .= 'function geo2_maps_lightbox_' . $options['mid'] . '( indexNr ) {' . "\n";

		// Fancybox 1.3.4 used in NextGEN (uses old jQuery).
		if ( $options['lightbox'] === 'fancybox' ) {
			include_once 'geo2-fancybox.php';
			$map_output .= geo2_maps_fancybox_options( $geo2_maps_lightbox, $options );
		}

		// Slimbox 2.
		if ( $options['lightbox'] === 'slimbox2' ) {
			include_once 'geo2-slimbox2.php';
			$map_output .= geo2_maps_slimbox2_options( $geo2_maps_lightbox, $options );
		}

		// Fancybox3 - its recommended to use the latest jquery with it.
		if ( $options['lightbox'] === 'fancybox3' ) {
			include_once 'geo2-fancybox3.php';
			$map_output .= geo2_maps_fancybox3_options( $geo2_maps_lightbox, $picture_list, $options );
		}

		$map_output .= "\n" . '}' . "\n";
	}
	// Ajax: don't start map.
	if ( $options['ajax'] !== 0 || ! $options['ajax'] ) {
		$map_output .= '
		jQuery( window ).on(\'load\', function() {
		nggGeo2Map_' . $options['mid'] . '( "", "" );
		} );';
	}
	// Script to enable full screen
	// There are two versions: one for map on a main page and one for map on the F3 side panel run/executed when Fancybox 3 is created ( onInit ).
	$map_output .= '
		jQuery( document ).ready( function( $ )
		{';
	$type        = $options['lightbox'];
	// Different Lightboxes require different z-index value.
	if ( $type === 'fancybox3' ) {
		$z_index = '9999';
	} else {
		$z_index = '999';
	}
	$map_output .= geo2_maps_fullscreen( $options['mid'], $z_index );
	$map_output .= '
		} );';

	$map_output .= "\n" . '</script>
<!-- End NGG Geo2 Maps' . $stat_text . ' -->' . "\n\n";

	if ( $options['worldmap'] === 1 && $albums_not_found_nr > 0 ) {
		$worldmap_echo  = $albums_not_found . '<br/>';
		$worldmap_echo .= 'Geocodig: ' . $options['geocoding_provider'] . '<br/>Try a different geocoding provider for a better result.';
		echo esc_html( $worldmap_echo );
	}
	unset( $options['worldmap'] );
	return $map_output;
}
