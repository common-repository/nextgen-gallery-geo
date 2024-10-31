<?php
/**
 * Various functions used by the plugin when Bing maps are created and loaded.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Functions
 * @since      2.0.0 Some functions amended and moved from functions.php.
 * @since      2.0.5 "If" statement amended in function geo2_maps_pin_desc().
 * @since      2.0.6 Functions amended: geo2_maps_add_pin(), geo2_maps_pin_desc(), geo2_maps_lightbox_callback(), geo2_maps_lightbox_data.
 * @since      2.0.7 Functions amended: geo2_maps_lightbox_data(), geo2_maps_pin_desc(), geo2_maps_lightbox_callback.
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
 * Convert number 1/0 to a string true/false
 *
 * Used in bing-map.php, geo2-fancybox3.php and all similar versions.
 *
 * @since  1.0.0
 * @since  2.0.0 Renamed
 *
 * @param  int $val Always 0 or 1.
 * @return string
 */
function geo2_maps_value( $val ) {
	if ( $val === 1 ) {
		$var = 'true';
	} else {
		$var = 'false'; }
	return $var;
}

/**
 * Creates code for pins or thumbs which will be placed on a map.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.6 Variable $pin defined instead of referred to with "$pin. =...".
 * @since  2.0.7 Undefined variables corrected $picture_data->gid and $picture_data->id
 *
 * @see    function geo2_maps(), function geo2_maps_worldmap()
 * @param  object $picture_data Data of a specific picture.
 * @param  int    $picture_nr Picture number corresponding to its position in the $picture_list.
 * @param  int    $picture_nr_total Total number of pictures in the $picture_list.
 * @param  array  $options An array of options.
 * @return string Javascript code.
 *
 * @todo   Check if Ajax for Worldmap is working.
 */
function geo2_maps_add_pin( $picture_data, $picture_nr, $picture_nr_total, $options ) {
	// Use thumb title? Shows image or gallery Title below thumbs or pushpins.
	$title = null;

	if ( ! isset( $picture_data->gid ) ) {
		$picture_data->gid = '';
	}
	if ( ! isset( $picture_data->id ) ) {
		$picture_data->id = '';
	}

	if ( $options['thumb_title'] === 1 ) {
		// Map ( picture alttext ).
		if ( $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' ) {
			$title = $picture_data->alttext;
			// Worldmap (gallery title ).
		} else {
			$title = $picture_data->title;
		}
	}

	// Create nonce with a specific Lightbox type.
	if ( $options['worldmap'] === 1 || $options['status'] !== 'auto worldmap' && $options['open_lightbox'] === 1 ) {
		if ( isset( $picture_data->gid ) ) {
			$nid = $picture_data->gid;
		} else {
			$nid = $picture_data->id;
		}
		$nonce = $nid . ',' . wp_create_nonce( 'myajax-lightbox-nonce-' . $nid );
	}

	// Defines pin location.
	$pin = '
		var loc_' . $picture_nr . ' = new Microsoft.Maps.Location( ' . $picture_data->gps['latitude'] . ',' . $picture_data->gps['longitude'] . ' );
		locs_' . $options['mid'] . '.push( loc_' . $picture_nr . ' );';

	// Defines pin - uses a custom icon or the pushpin.
	if ( $options['thumb'] === 1 ) {
		// geo2_thumbnail( loc_111, title, pin_desc, caption, thumb_url, thumb_height, thumb_width, thumb_radius, pid, gid, aid, slug, page_url, nonce, image_url, picture_nr, callback ).
		$pin .= '
		var pin_' . $picture_nr . ' = geo2_thumbnail_' . $options['mid'] . '( loc_' . $picture_nr;
		$pin .= ', "' . $title . '"';
		// Enables pin desc for infobox only.
		if ( $options['lightbox'] === 'infobox' && $options['status'] !== 'route' ) {
			$pin .= ', \'' . geo2_maps_pin_desc( $picture_data, $options ) . '\'';
		} else {
			$pin .= ', ""';
		}
		$pin .= ', "' . $picture_data->wp_caption . '"';
		$pin .= ', "' . $picture_data->thumb_url . '", ' . $options['thumb_height'] . ', ' . $options['thumb_width'] . ', ' . $options['thumb_radius'];
		$pin .= ', "' . $picture_data->pid . '"';
		if ( $options['worldmap'] === 1 ) {
			$pin .= ', "' . $picture_data->gid . '"';
			$pin .= ', "' . $picture_data->id . '"';
			$pin .= ', "' . $picture_data->slug . '"';
			$pin .= ', "' . $picture_data->page_url . '"';
		}
		// Adds individual Ajax nonce ( used by Worldmap ).
		if ( $options['worldmap'] === 1 && $options['open_lightbox'] === 1 ) {
			$pin .= ', "' . $nonce . '"';
		}
		$pin .= ', "' . $picture_data->image_url . '"';
		$pin .= ', "' . $picture_nr . '"';
		$pin .= ', function( pin_' . $picture_nr . ' ) {';
		$pin .= '
			pins_' . $options['mid'] . '.push( pin_' . $picture_nr . ' );';
		$pin .= '
		} );';
	} else {
		// geo2_thumbnail( loc_111, title, caption, aid ).
		$pin .= '
		var pin_' . $picture_nr . ' = geo2_thumbnail_' . $options['mid'] . '( loc_' . $picture_nr;
		$pin .= ', "' . $title . '"';
		$pin .= ', "' . $picture_data->wp_caption . '"';
		if ( $options['worldmap'] === 1 ) {
			$pin .= ', "' . $picture_data->id . '"';
		}
		$pin .= ' );' . "\n";

		// Pin metadata.
		if ( $options['lightbox'] === 'infobox' ) {
			list( $thumb_width, $thumb_height ) = getimagesize( $picture_data->thumb_url );
		}
		// if ( $options['lightbox'] === 'infobox' ) {
		// $thumb_width  = $picture_data->meta_data['thumbnail']['width'];
		// $thumb_height = $picture_data->meta_data['thumbnail']['height'];
		// }

		$pin .= '
		pin_' . $picture_nr . '.metadata = {';
		// Adds individual Ajax nonce ( used by worldmap ) not working probably not needed.
		if ( $options['worldmap'] === 1 && $options['open_lightbox'] === 1 ) {
			$pin .= '
			nonce: "' . $nonce . '",';
		}
		$pin .= '
			title: "' . $picture_data->title . '",' . ( $options['lightbox'] === 'infobox' ? 'thumb_width: ' . $thumb_width . ',
			thumb_height: ' . $thumb_height . ',' : '' ) . '
			pid: "' . $picture_data->pid . '",' . ( $options['worldmap'] === 1 ? '
			gid: "' . $picture_data->gid . '",
			aid: "' . $picture_data->id . '",
			slug: "' . $picture_data->slug . '",
			page_url: "' . $picture_data->page_url . '",' : '' ) . '
			src: "' . $picture_data->image_url . '",' . ( $options['lightbox'] === 'infobox' && $options['status'] !== 'route' ? '
			HTMLcontent: \'' . geo2_maps_pin_desc( $picture_data, $options ) . '\',' : '' ) . '
			picture_nr: "' . $picture_nr . '"
		};' . "\n";

		// Adds a click event handler.
		$pin .= '
		var pin_' . $picture_data->pid . '_HandlerId = Microsoft.Maps.Events.addHandler( pin_' . $picture_nr . ', "click", pushpinClicked_' . $options['mid'] . ' );' . "\n";
		// Hover effect is not needed for one pin.
		if ( $picture_nr_total > 1 ) {
			$pin .= '
		bringForwardOnHover_' . $options['mid'] . '( pin_' . $picture_nr . ' );';
		}

		// For Pushpins ( and other then Thumbs ).
		$pin .= '
		pins_' . $options['mid'] . '.push( pin_' . $picture_nr . ' );' . "\n";

		// Hover Style.
		$pin .= '
		pin_' . $picture_nr . '.setOptions( { enableHoverStyle: true, enableClickedStyle: false } );' . "\n";
	}

	return $pin;
}

/**
 * Checks if Birdseye view is available.
 *
 * @since 2.0.0
 *
 * @see    function geo2_maps(), function geo2_maps_worldmap()
 * @param  string $map_type A type of a map to use when BEV is not available.
 * @return string Javascript code.
 */
function geo2_maps_is_bev_available( $map_type ) {
	return 'mapCenter = map.getCenter();
	Microsoft.Maps.getIsBirdseyeAvailable( mapCenter, Microsoft.Maps.Heading.north, function( isAvailable ) {
		if ( isAvailable )
		{
			map.setView( {
				mapTypeId: Microsoft.Maps.MapTypeId.birdseye
			} );
		} else {
			map.setView( {
				mapTypeId: Microsoft.Maps.MapTypeId.' . $map_type . '
			} );
		}
	} );
';
}

/**
 * Builds a thumbnail or a pushpin function.
 *
 * @since  2.0.0
 *
 * @see    function geo2_maps(), function geo2_maps_worldmap()
 * @param  array $options An array of options.
 * @param  int   $picture_nr_total Total number of pictures in the $picture_list.
 * @return string Javascript code.
 *
 * @todo   Width, height, radius can be removed from a function and replaced by option parameter.
 */
function geo2_maps_thumbnail_function( $options, $picture_nr_total ) {
	// Canvas thumbnails.
	if ( $options['thumb'] === 1 ) {
		$thumb  = 'function geo2_thumbnail_' . $options['mid'] . '( location, title, pin_desc, wp_caption, url, thumb_height, thumb_width, thumb_radius, pid,' . ( $options['worldmap'] === 1 ? ' gid, aid, slug, pageURL,' : '' ) . ( $options['worldmap'] === 1 && $options['open_lightbox'] === 1 ? ' nonce,' : '' ) . ' src, picture_nr, callback ) 
{
	var img = new Image(),' . ( $options['lightbox'] === 'infobox' ? '
	    img_width,
	    img_height,' : '' ) . '
	    pin;
	img.onload = function() {' . ( $options['lightbox'] === 'infobox' ? '
		var img_width = img.width,
		    img_height = img.height;' : '' );
		$thumb .= geo2_maps_thumbnail_canvas_function( $options );

		// Metadata is used to identify clicked pin and in Infobox.
		$thumb .= '
		pin = new Microsoft.Maps.Pushpin( location, {
			// Generates a base64 image URL from the canvas.
			icon: c.toDataURL(),
			// Anchor to center of image.
			anchor: new Microsoft.Maps.Point(c.width / 2, c.height / 2' . ( $options['thumb_shape'] === 'round' ? ' - border_width / 2' : '' ) . ' ),
			' . ( $options['thumb_shape'] === 'round' ? 'roundClickableArea: true,' : '' ) . '
			title: title,
			subTitle: wp_caption
		} );
		pin.metadata = {';
		// Adds individual Ajax nonce ( used by Worldmap ).
		if ( $options['worldmap'] === 1 && $options['open_lightbox'] === 1 ) {
			$thumb .=
			'nonce: nonce,';
		}
		$thumb .= '
			title:  title,' . ( $options['lightbox'] === 'infobox' ? '
			thumb_width: img_width,
			thumb_height: img_height,' : '' ) . '
			HTMLcontent: pin_desc,
			pid: pid,' . ( $options['worldmap'] === 1 ? '
			gid: gid,
			aid: aid,
			slug: slug,
			pageURL: pageURL,' : '' ) . '
			src: src,
			picture_nr: picture_nr
		};';
		// Adds on click event to thumbs.
		$thumb .= '
		var pinHandlerId = Microsoft.Maps.Events.addHandler( pin, "click", pushpinClicked_' . $options['mid'] . ' );' . ( $picture_nr_total > 1 ? '
		bringForwardOnHover_' . $options['mid'] . '( pin );' : '' );
		$thumb .= '
		if (callback ) {
			callback( pin );
		}
	};
	// Allow cross domain image editing.
	img.crossOrigin = "anonymous"; 
	img.src = url;
}' . "\n";
		return $thumb;
		// Pushpins.
	} else {
		$thumb = 'function geo2_thumbnail_' . $options['mid'] . '( location, title, wp_caption' . ( $options['worldmap'] === 1 ? ', aid' : '' ) . ' ) {';

		// Amends pin colors and links for the Worldmap.
		if ( $options['worldmap'] === 1 ) {
			$thumb .= '
	var color;
	if ( aid ) 
	{
		color = "' . $options['pin_alb_color'] . '";
	} else {
		color = "' . $options['pin_gal_color'] . '";
	}';
		} else {
			$thumb .= '
	var color = "' . $options['pin_color'] . '";';
		}

		$thumb .= '
	var pin = new Microsoft.Maps.Pushpin( location,
		{
			title: title,
			subTitle: wp_caption,
			color: color
		}
	);
	return pin;
}' . "\n \n";
		return $thumb;
	}
}

/**
 * Code to create canvas thumbnail used by geo2_maps_thumbnail_function()
 *
 * @since 2.0.0
 *
 * @see    function geo2_maps_thumbnail_function()
 * @param  array $options An array of options.
 * @return string Javascript code.
 */
function geo2_maps_thumbnail_canvas_function( $options ) {
	$pin_hover = '
 		if ( img.width > img.height ) { 
 			thumb_height = Math.round( thumb_height * ( img.height/img.width ) );
 		} else if  ( img.width < img.height ) {
 			thumb_width = Math.round( thumb_height * ( img.width/img.height ) );
 		}
 		' . ( $options['thumb_border'] !== 0 ? '
 		var border_width = ' . $options['thumb_border'] . ',
 		    border_color = "' . $options['thumb_border_color'] . '";
 		if ( border_width > thumb_width/4 || border_width > thumb_height/4 ) {
 			border_width = Math.round( thumb_width > thumb_height ? thumb_height/4 : thumb_width/4 ); }' : '' ) . '
 		
 		var c = document.createElement( "canvas" );';

		// Create thumbs.
	if ( $options['thumb_shape'] === 'rect' ) {
		// Image size.
		$pin_hover .= '
		c.width = thumb_width;
		c.height = thumb_height;
		var ctx = c.getContext( "2d" );
		// Move to the center of the canvas.
		ctx.translate( thumb_width / 2, thumb_height / 2 );
		// Draw the image
		ctx.drawImage( img, -thumb_width / 2, -thumb_height / 2, thumb_width, thumb_height );';
	} elseif ( $options['thumb_shape'] === 'round' ) {
		$pin_hover .= '
		var diameter = thumb_radius * 2;
		c.width = diameter;
		c.height = diameter;
		var ctx = c.getContext( "2d" );
		// Draw a circle which can be used to clip the image.
		ctx.beginPath();
		ctx.arc( thumb_radius, thumb_radius, thumb_radius, 0, 2 * Math.PI, false );
		ctx.fill();
		// Use the circle to clip.
		ctx.clip();
		// Draw the image icon
		ctx.drawImage( img, 0, 0, diameter, diameter );';
	}

	if ( $options['thumb_border'] > 0 ) {
		if ( $options['thumb_shape'] === 'rect' ) {
			$pin_hover .= '
		// Draw rectangle
		ctx.beginPath();
		ctx.rect( -( thumb_width - border_width )/2, -( thumb_height - border_width )/2, thumb_width - border_width, thumb_height - border_width );
		ctx.lineWidth = border_width;
		ctx.strokeStyle = border_color;
		ctx.stroke();';
		} elseif ( $options['thumb_shape'] === 'round' ) {
			$pin_hover .= '
		// Draw a circle for a border.
		var radius = thumb_radius - ( border_width/2 );
		ctx.beginPath();
		ctx.arc( thumb_radius, thumb_radius, radius, 0, 2 * Math.PI, false );
		ctx.lineWidth = border_width;
		ctx.strokeStyle = border_color;
		ctx.stroke();';
		}
	}
	return $pin_hover;
}

/**
 *  Function to trim all <br /> from start or <br /> from the end of a string used in the pin description function geo2_maps_pin_desc().
 *
 * @since 2.0.0
 *
 * @see    function geo2_maps_pin_desc()
 * @param  string $text Code to trim.
 * @return string HTML code.
 */
function geo2_maps_remove_br( $text ) {
	// $pin_desc .= trim( $pin_text, '<br />' ); - not working. removes <b> as well.
	if ( strpos( $text, '<br />' ) === 0 ) {
		$text = substr( $text, strlen( '<br />' ) );
		if ( strpos( $text, '<br />' ) === 0 ) {
			$text = substr( $text, strlen( '<br />' ) );
		}
	}
	if ( substr( $text, -( strlen( '<br />' ) ) ) === '<br />' ) {
		$text = substr( $text, 0, -( strlen( '<br />' ) ) );
		if ( substr( $text, -( strlen( '<br />' ) ) ) === '<br />' ) {
			$text = substr( $text, 0, -( strlen( '<br />' ) ) );
		}
	}
	return $text;
}

/**
 * Creates pin/image description.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.5 If statement amended for section "Infobox" to remove "GPS coordinates" and "Exif" section description when in Route mode.
 * @since  2.0.6 Variable $pin_text defined before it's used.
 * @since  2.0.7 Error control operator partly removed. Undefined variables corrected $picture_data->name, title, galdesc, albumdesc.
 *
 * @see    function geo2_maps_add_pin(), function geo2_fancybox3_options(), function geo2_fancybox3_options_worldmap()
 * @param  object $picture_data Data of a specific picture.
 * @param  array  $options An array of options.
 * @return string[]|string HTML code.
 */
function geo2_maps_pin_desc( $picture_data, $options ) {
	/*
	For worldmap with albums and galleries $main_title and $main_desc comes from Album.
	 * $title and &desc comes from gallery.
	 * For normal gallery with pictures $main_title and $main_desc comes from gallery.
	 * $title and &desc comes from the picture.
	 */

	// Map ( picture alttext ).
	if ( $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' ) {
		// Gallery title.
		$main_title = $picture_data->title;
		// Gallery description.
		$main_desc = $picture_data->galdesc;
		$title     = $picture_data->alttext;
		$desc      = $picture_data->description;
		$id        = $picture_data->pid;
		// Worldmap ( gallery title ).
	} else {
		// Album name or gallery title.
		$main_title = ( isset( $picture_data->name ) ? $picture_data->name : $picture_data->title );
		// Album or gallery description.
		$main_desc = ( isset( $picture_data->albumdesc ) ? $picture_data->albumdesc : $picture_data->galdesc );
		$id        = ( strlen( $picture_data->id ) === 0 ? $picture_data->gid : $picture_data->id );
	}
	// Defines variable.
	$pin_desc = '';

	// ISO array check.
	// Check is_array was added when in one photo for some reason ISO value was saved twice as an array which was causing an error).
	if ( ( ( $options['lightbox'] === 'fancybox3' ) || ( $options['lightbox'] === 'infobox' && $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' && $picture_data->alttext !== '{title}' ) ) &&
		$options['exif'] === 1 &&
		is_array( $picture_data->meta_data['iso'] )
		&& $picture_data->meta_data['iso'][0] > 0 ) {
		$picture_data->meta_data['iso'] = $picture_data->meta_data['iso'][0];
	}

	// Infobox.
	if ( $options['lightbox'] === 'infobox' ) {
		// Outer Infobox div.
		$pin_desc .= '<div id="geo2_InfoboxCustom_' . $id . '" class="geo2_InfoboxCustom" style="' . ( empty( $options['infobox_width'] ) ? 'max-width: {maxWidth};' : '' ) . ( empty( $options['infobox_height'] ) ? 'max-height: {maxHeight};' : '' ) . '">';
		// Inserts image. Can't be bigger then {src} used with route module.
		if ( strlen( $picture_data->thumb_url ) > 3 ) {
			$pin_desc .= '<img id="infoboxImg_' . $id . '" src="' . $picture_data->thumb_url . '" align="left" style="margin:0px;"/>';
		}
		// Code for close button.
		$pin_desc .= '<div id="geo2_close_' . $id . '" class="geo2_close_icon" onclick="closeInfobox_' . $options['mid'] . '( \\\'' . $id . '\\\' )"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" width="24" height="24"><path d="M5 7.5L9.5 12L5 16.5L5 19L7.5 19L12 14.5L16.5 19L19 19L19 16.5L14.5 12L19 7.51L19 5L16.5 5L12 9.5L7.5 5L5 5L5 7.5Z" id="a2GhEW8Onw"></path></svg></div>';

		// Places gallery title in separate div placed on top of the image.
		if ( $options['infobox_title_over'] === 1 ) {
			// Code description in separate div to control overflow.
			$pin_desc .= '<div class="geo2_infobox_title_wrap" style="max-width: {imgWidth};"><div id="geo2_infobox_title_' . $id . '" class="geo2_infobox_title geo2_scrollbar_style" style="--ratio:{ratio};"><div class="geo2_infobox_title_cont"><div class="geo2_infobox_title_text">';
			$pin_title = '';
			// Shows album or gallery title and description always for Worldmap.
			if ( $options['gallery_title'] === 1 || $options['status'] === 'worldmap' || $options['status'] === 'auto worldmap' ) {
				if ( strlen( $main_title ) > 0 ) {
					$pin_title = '<h3><b>' . htmlspecialchars( $main_title, ENT_QUOTES ) . '</b></h3>';
				}
				// Album name or gallery description.
				if ( strlen( $main_desc ) > 0 ) {
					$pin_title .= htmlspecialchars( $main_desc, ENT_QUOTES ) . '<br />';
				}
			}

			if ( $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' ) {
				if ( $options['gallery_title'] === 1 && strlen( $title ) > 0 ) {
					// Picture title ( alttext ) only in bold.
					$pin_title .= '<b>' . htmlspecialchars( $title, ENT_QUOTES ) . '</b><br />';
				} elseif ( strlen( $title ) > 0 ) {
					// Picture title ( alttext ) as a header.
					$pin_title = '<h3>' . htmlspecialchars( $title, ENT_QUOTES ) . '</h3>';
				}
			}

			$pin_desc .= $pin_title; // geo2_maps_remove_br( $pin_title ); -old.
			$pin_desc .= '</div></div></div></div>';
		}

			// Code description in separate div to control overflow.
			$pin_desc .= '<div id="geo2_infobox_desc_' . $id . '" class="geo2_infobox_desc geo2_scrollbar_style" ' . ( empty( $options['infobox_height'] ) ? 'style="max-height: {maxDescHeight};' : '' ) . '">';

		// Place gallery title in separate div placed on top of the image.
		if ( $options['infobox_title_over'] === 0 ) {
			$pin_text = '';
			// Show gallery title and description always for Worldmap.
			if ( $options['gallery_title'] === 1 || $options['status'] === 'worldmap' || $options['status'] === 'auto worldmap' ) {
				if ( strlen( $main_title ) > 0 ) {
					$pin_text .= '<h3><b>' . htmlspecialchars( $main_title, ENT_QUOTES ) . '</b></h3>';
				}
				// Gallery description.
				if ( strlen( $main_desc ) > 0 ) {
					$pin_text .= htmlspecialchars( $main_desc, ENT_QUOTES ) . '<br />';
				}
			}
			// Adds additional space when no description with title or after description.
			if ( strlen( $main_title ) > 0 || strlen( $main_desc ) > 0 ) {
				$pin_text .= '<br />';
			}

			if ( $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' ) {
				// Picture title ( alttext ).
				if ( strlen( $title ) > 0 ) {
					$pin_text .= '<b>' . htmlspecialchars( $title, ENT_QUOTES ) . '</b><br />';
				}
				// Picture description.
				if ( strlen( $desc ) > 0 ) {
					$pin_text .= htmlspecialchars( $desc, ENT_QUOTES ) . '<br />';
				}
				// Adds additional space when no description with title or after description.
				if ( strlen( $title ) > 0 || strlen( $desc ) > 0 ) {
					$pin_text .= '<br />';
				}
			}
		} else {
			$pin_text = '';
			if ( $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' ) {
				// Picture description.
				if ( strlen( $desc ) > 0 ) {
					$pin_text .= htmlspecialchars( $desc, ENT_QUOTES ) . '<br />';
				}
				// Adds additional space when no description with title or after description.
				if ( strlen( $desc ) > 0 ) {
					$pin_text .= '<br />';
				}
			}
		}

		// Adds additional Exif description only if not in Worldmap mode and if not evaluated by the object clicked on a path with Route mode activated (see bing-map.php: 560 function "geo2_maps_shapeMetadata_").
		if ( $options['status'] !== 'worldmap' && $options['status'] !== 'auto worldmap' && $picture_data->alttext !== '{title}' ) {
			// GPS coordinates.
			if ( $options['gps'] === 1 ) {
				if ( isset( $picture_data->gps['longitude_format'] ) ) {
					$pin_text .= '<b>' . esc_html__( 'GPS coordinates', 'ngg-geo2-maps' ) . '</b><br /><span class="fancybox3-caption-exif-param">'
						. esc_html__( 'Longitude', 'ngg-geo2-maps' ) . ':</span>  ' . $picture_data->gps['longitude_format'] . '<br /><span class="fancybox3-caption-exif-param">'
						. esc_html__( 'Latitude', 'ngg-geo2-maps' ) . ':</span>  ' . $picture_data->gps['latitude_format'] . '<br /><br />';
				}
			}
			// EXIF data.
			if ( $options['exif'] === 1 ) {
				// @ needed, otherwise "Notice: Undefined property".
				if ( strlen( $picture_data->meta_data['created_timestamp'] ) > 2 || strlen( $picture_data->meta_data['camera'] ) > 2 || strlen( $picture_data->meta_data['aperture'] ) > 2 || strlen( $picture_data->meta_data['iso'] > 0 ) ) {
					$pin_text .= '<b>EXIF</b><br />'; }

				if ( strlen( $picture_data->meta_data['created_timestamp'] ) > 2 ) {
					$pin_text .= '<span class="fancybox3-caption-exif-param">' . esc_html__( 'Date', 'ngg-geo2-maps' ) . ':</span>  ' . $picture_data->meta_data['created_timestamp'] . '<br />'; }
				if ( strlen( $picture_data->meta_data['camera'] ) > 2 ) {
					$pin_text .= '<span class="fancybox3-caption-exif-param">' . esc_html__( 'Camera', 'ngg-geo2-maps' ) . ':</span>  ' . $picture_data->meta_data['camera'] . '<br />'; }
				$lens_info = geo2_maps_exif_camera( $picture_data );
				if ( strlen( $lens_info ) > 2 ) {
					$pin_text .= $lens_info; }
				if ( strlen( $picture_data->meta_data['aperture'] ) > 2 && strlen( $picture_data->meta_data['focal_length'] ) > 2 ) {
					$pin_text .= '<span class="fancybox3-caption-exif-param">' . esc_html__( 'Aperture', 'ngg-geo2-maps' ) . ':</span> ' . $picture_data->meta_data['aperture'] .
						'<br /><span class="fancybox3-caption-exif-param">' . esc_html__( 'Focal length', 'ngg-geo2-maps' ) . ':</span> ' . $picture_data->meta_data['focal_length'] . '<br />'; }
				if ( strlen( $picture_data->meta_data['iso'] ) > 0 && strlen( $picture_data->meta_data['shutter_speed'] ) > 2 ) {
					$pin_text .= '<span class="fancybox3-caption-exif-param">' . esc_html__( 'ISO', 'ngg-geo2-maps' ) . ':</span> ' . $picture_data->meta_data['iso'] .
						'<br /><span class="fancybox3-caption-exif-param">' . esc_html__( 'Shutter speed', 'ngg-geo2-maps' ) . ':</span> ' . $picture_data->meta_data['shutter_speed'] . '<br /><br />';
				}
			}
		}

		$pin_desc .= geo2_maps_remove_br( $pin_text );
		// Close desc in separate div to control overflow.
		$pin_desc .= '</div></div>';

		return $pin_desc;
	}

	if ( $options['lightbox'] === 'fancybox3' ) {
		if ( $options['fancybox3_caption'] === 'bottom' ) {
			$pin_desc .= '		<div class="fancybox3-caption-row">
				<div class="fancybox3-caption-column fancybox3-caption-column-left">';

			// Show gallery title and description.
			if ( $options['gallery_title'] === 1 ) {
				if ( strlen( $picture_data->title ) > 0 ) {
					$pin_desc .= '<h3><b>' . htmlspecialchars( $picture_data->title, ENT_QUOTES ) . '</b></h3>';
				}
				// Separator.
				if ( strlen( $picture_data->title ) > 0 && strlen( $picture_data->galdesc ) > 0 ) {
					$pin_desc .= '<h3> | </h3>';
				}
				// Gallery description.
				if ( strlen( $picture_data->galdesc ) > 0 ) {
					$pin_desc .= htmlspecialchars( $picture_data->galdesc, ENT_QUOTES );
				}
			}

			// Adds additional space when no gallery title.
			if ( ( strlen( $picture_data->title ) > 0 || strlen( $picture_data->galdesc ) > 0 ) &&
					( strlen( $picture_data->alttext ) > 0 || strlen( $picture_data->description ) > 0 ) ) {
				$pin_desc .= '<div style="height:4px;font-size:1px;">&nbsp;</div>';
			}

			// Picture title ( alttext ).
			if ( strlen( $picture_data->alttext ) > 0 ) {
				$pin_desc .= '<b>' . htmlspecialchars( $picture_data->alttext, ENT_QUOTES ) . '</b>';
			}
			// Separator.
			if ( strlen( $picture_data->alttext ) > 0 && strlen( $picture_data->description ) > 0 ) {
				$pin_desc .= ' | ';
			}
			// Picture description.
			if ( strlen( $picture_data->description ) > 2 ) {
				$pin_desc .= htmlspecialchars( $picture_data->description, ENT_QUOTES );
			}

			$pin_desc .= '		<div id="fancybox3-caption-bottom-counter" class="fancybox3-caption-exif-param">' . esc_html__( 'Image', 'ngg-geo2-maps' ) . ' <span data-fancybox3-index></span> ' . esc_html__( 'of', 'ngg-geo2-maps' ) . ' <span data-fancybox3-count></span></div>
			</div>
				<div class="fancybox3-caption-column fancybox3-caption-column-right">';

			// Adds additional space.
			if ( ( strlen( $picture_data->title ) > 0 || strlen( $picture_data->alttext ) > 0 ) && isset( $picture_data->gps['longitude_format'] ) ) {
				$pin_desc .= '<div style="height:4px;font-size:1px;">&nbsp;</div>';
			}

			// GPS coordinates.
			if ( $options['gps'] === 1 ) {
				if ( isset( $picture_data->gps['longitude_format'] ) ) {
					$longitude       = esc_html__( 'Longitude', 'ngg-geo2-maps' );
					$longitude_no    = str_replace( ' ', '&nbsp;', $picture_data->gps['longitude_format'] );
					$latitude        = esc_html__( 'Latitude', 'ngg-geo2-maps' );
					$latitude_no     = str_replace( ' ', '&nbsp;', $picture_data->gps['latitude_format'] );
					$gps_coordinates = esc_html__( 'GPS coordinates', 'ngg-geo2-maps' );

					$gps       = '
					<div class="fancybox3-caption-header">| <b>' . str_replace( ' ', '&nbsp;', $gps_coordinates ) . '</b></div>
					<span class="fancybox3-caption-exif-param">'
						. $longitude . ':</span>&nbsp;' . $longitude_no . '&nbsp;| <span class="fancybox3-caption-exif-param">'
						. $latitude . ':</span>&nbsp;' . $latitude_no . '&nbsp;|';
					$pin_desc .= $gps;
				} else {
					$gps = '';
				}
			}

			// EXIF data.
			if ( $options['exif'] === 1 ) {
				if ( strlen( $picture_data->meta_data['created_timestamp'] ) > 2 ||
						strlen( $picture_data->meta_data['camera'] ) > 2 ||
						strlen( $picture_data->meta_data['aperture'] ) > 2 ||
						strlen( $picture_data->meta_data['focal_length'] ) > 2 ||
						strlen( $picture_data->meta_data['shutter_speed'] ) > 2 ||
						strlen( $picture_data->meta_data['iso'] > 0 )
					) {
					$exif = array();
					// Adds additional space.
					if ( strlen( $gps ) > 0 ) {
						$pin_desc .= '<div style="height:4px;font-size:1px;">&nbsp;</div>';
					}
					$pin_desc .= '<div class="fancybox3-caption-header">&nbsp|| <b>EXIF</b></div>';

					// Date.
					if ( strlen( $picture_data->meta_data['created_timestamp'] ) > 2 ) {
						$date   = esc_html__( 'Date', 'ngg-geo2-maps' );
						$exif[] = '<span class="fancybox3-caption-exif-param">' . $date . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['created_timestamp'] );
					}
					// Camera.
					if ( strlen( $picture_data->meta_data['camera'] ) > 2 ) {
						$camera = esc_html__( 'Camera', 'ngg-geo2-maps' );
						$exif[] = '<span class="fancybox3-caption-exif-param">' . $camera . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['camera'] );
					}
					// Lens.
					$lens_info = geo2_maps_exif_camera( $picture_data );
					if ( strlen( $lens_info ) > 2 ) {
						$exif[] = $lens_info;
					}
					// Aperture & Focal length.
					if ( strlen( $picture_data->meta_data['aperture'] ) > 2 ) {
						$aperture = esc_html__( 'Aperture', 'ngg-geo2-maps' );
						$exif[]   = '<span class="fancybox3-caption-exif-param">' . $aperture . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['aperture'] );
					}
					if ( strlen( $picture_data->meta_data['focal_length'] ) > 2 ) {
						$focal_length = esc_html__( 'Focal length', 'ngg-geo2-maps' );
						$exif[]       = '<span class="fancybox3-caption-exif-param">' . str_replace( ' ', '&nbsp;', $focal_length ) . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['focal_length'] );
					}
					// Shutter speed.
					if ( strlen( $picture_data->meta_data['shutter_speed'] ) > 2 ) {
						$shutter_speed = esc_html__( 'Shutter speed', 'ngg-geo2-maps' );
						$exif[]        = '<span class="fancybox3-caption-exif-param">' . str_replace( ' ', '&nbsp;', $shutter_speed ) . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['shutter_speed'] );
					}
					// ISO ( Check is_array was added when in one photo for some reason ISO value was saved twice as an array which was causing an error).
					if ( is_array( $picture_data->meta_data['iso'] ) && $picture_data->meta_data['iso'][0] > 0 ) {
						$iso    = esc_html__( 'ISO', 'ngg-geo2-maps' );
						$exif[] = '<span class="fancybox3-caption-exif-param">' . $iso . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['iso'][0] );
					} elseif ( ! is_array( $picture_data->meta_data['iso'] ) && strlen( $picture_data->meta_data['iso'] ) > 0 ) {
						$iso    = esc_html__( 'ISO', 'ngg-geo2-maps' );
						$exif[] = '<span class="fancybox3-caption-exif-param">' . $iso . ':</span>&nbsp;' . str_replace( ' ', '&nbsp;', $picture_data->meta_data['iso'] );
					}
					$pin_desc .= implode( ' |&nbsp;', $exif );
				}
			}
			$pin_desc .= '			</div>
			</div>';
		}
		return array( $picture_data->pid => $pin_desc );
	}
}

/**
 * Lightbox Ajax callback via sql for Worldmap.
 * Echoes Javascript Lightbox code (can include Fancybox 3 side panel map).
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.6 Whole array $options passed to functions.
 * @since  2.0.7 Warnings text amended. Function geo2_maps_convert_to_int() added.
 *
 * @see      function geo2_maps_init()
 */
function geo2_maps_lightbox_callback() {
	// Nonce security.
	check_ajax_referer( 'geo2-ajax-nonce', 'nonce' );

	$data  = $_POST['gid'];
	$post  = explode( ',', $data );
	$gid   = sanitize_key( $post[0] );
	$nonce = sanitize_key( $post[1] );

	// Gets default options.
	$options             = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) );
	$options['worldmap'] = 1;
	$options['status']   = 'worldmap';

	// Validates returned data.
	if ( ! is_numeric( $gid ) ) {
		die( 'Security check failed! Nonce not numeric.' ); }
	if ( ! wp_verify_nonce( $nonce, 'myajax-lightbox-nonce-' . $gid ) ) {
		die( 'Nonce verification check failed!' ); }
	// Gets data using sql.
	$options['id'] = $gid;
	$picture_list  = geo2_maps_data( $options );

	include_once 'bing-map-worldmap.php';
	$lightbox = geo2_maps_worldmap( $picture_list, $options );
	// There is no escape function for Javascript code which would not make the code malformed. Output is validate on the browser side. Code in geo2-ajax.js.
	echo $lightbox;
	/* https://github.com/WordPress/WordPress-Coding-Standards/issues/1270 */
	exit;
}

/**
 * Collects Lightbox info.
 *
 * @since  1.0.0
 * @since  2.0.0 Amended.
 * @since  2.0.7 Function amended to return null for $type === 'infobox', unused parameter removed. Undefined variables corrected $picture_data->gid and $picture_data->id
 *
 * @see      function geo2_maps(), function geo2_maps_worldmap()
 * @param  object $picture_data Data of a specific picture.
 * @param  string $type Lightbox type name.
 * @return string|null Javascript Lightbox code.
 */
function geo2_maps_lightbox_data( $picture_data, $type ) {
	if ( $type === 'no' || $type === 'infobox' ) {
		return;
	}

	if ( ! isset( $picture_data->gid ) ) {
		$picture_data->gid = '';
	}
	if ( ! isset( $picture_data->id ) ) {
		$picture_data->id = '';
	}

	// Fancybox.
	/* {\'href\' : "http://...", \'title\' : "title",\'content\' : "content" } */
	if ( $type === 'fancybox' ) {
		$data = '{
	\'href\' : "' . $picture_data->image_url . '",	
	\'title\'	: "' . ( strlen( $picture_data->alttext ) > 1 ? $picture_data->alttext . '</br>' : '' ) . ( strlen( $picture_data->description ) > 1 ? $picture_data->description . '</br>' : '' ) . '"
	},';
	}
	// Fancybox 3.
	/* { src : "URL", opts : { caption : "description", thumb: "URL" }}, */
	if ( $type === 'fancybox3' ) {
		$data = '    {
				src  : "' . $picture_data->image_url . '",
				opts : {
					imageId : "' . $picture_data->pid . '",
					gid  : "' . $picture_data->gid . '",
					aid  : "' . $picture_data->id . '",
					thumb: "' . $picture_data->thumb_url . '"
				}
			},';
	}
	// Slimbox 2.
	// Code for 1 picture: "URL","description"
	// Code for >1 picture: ["URL","description"],
	// Backup code end.
	if ( $type === 'slimbox2' ) {
		$data = ' ["' . $picture_data->image_url . '", "' . $picture_data->description . '"], ';
	}
	return $data;
}

/**
 * Collects Lightbox info.
 *
 * @since  2.0.0
 *
 * @see      function geo2_maps(), function geo2_fancybox3_options_worldmap()
 * @param  int $mid The number (random) drawn for the parent map.
 * @param  int $z_index Z-index for fancybox 3.
 * @return string Javascript Lightbox code.
 */
function geo2_maps_fullscreen( $mid, $z_index ) {
	// Checks Z-index value. Fancybox 3 > 99990 > Other Lightboxes.
	if ( $z_index > 99990 ) {
		$key = 'keyup';
	} else {
		$key = 'keydown';
	}
	$full_screen = '
			var zoom, new_zoom, height, width, hash;
			// parameter center is set outside this function
			function esc() {
				$( ".geo2_maps_map > #geo2_maps_map_' . $mid . '" ).parent().appendTo( "#geo2_map_' . $mid . '_placeholder" );
				$( ".geo2_maps_map > #geo2_maps_map_' . $mid . '" ).parent().removeClass( "geo2_map_fullscreen" ).css( "z-index", "" );
				$( "#geo2_fs_' . $mid . '" ).removeClass( "geo2_fs" ).addClass( "geo2_fs_out" );
				$( ".geo2_maps_map > #geo2_maps_map_' . $mid . '" ).parent().css( {
							position: "relative",
							width: width,
							height: height
				} );
				$("body").css("overflow", "auto");
				
				$( document ).unbind( "' . $key . '" );
				map_' . $mid . '.setView( { zoom: zoom, center: center_' . $mid . ' } );
			}
			function historyBack() {
				window.history.go(-1 );
			}
			function hashChangeEvent( e ) {
				if ( !location.hash.endsWith( "full_screen_map" ) ) {
						window.onhashchange = null;
						esc();
				}
			}
			$( "#geo2_fs_' . $mid . '" ).click( function()
			{
				if ( $( "#geo2_fs_' . $mid . '" ).hasClass( "geo2_fs_out" ) )
				{
					// update webpage with hash
					if ( location.hash.length > 0 ) {
						location.hash += "-full_screen_map";
						hash = "-full_screen_map";
					} else {
						location.hash += "full_screen_map";
						hash = "full_screen_map";
					}
					window.onhashchange = hashChangeEvent;
					
					// Get map size and location so it can be restored later.
					zoom = map_' . $mid . '.getZoom();
					center_' . $mid . ' = map_' . $mid . '.getCenter();
					height = map_' . $mid . '.getHeight();
					width = map_' . $mid . '.getWidth();
					var screen_width = $( window ).width();
					var screen_height = $( window ).height();
					// Calculate the new zoom level ( other ways - bounds, new LocationRect did not work )
					if ( width*2 >= screen_width && height*2 >= screen_height ) {new_zoom = zoom; } 
					if ( width*2 < screen_width && height*2 < screen_height ) {new_zoom = zoom + 1; } 
					if ( width*4 < screen_width && height*4 < screen_height ) {new_zoom += 1; }
	
					$( ".geo2_maps_map > #geo2_maps_map_' . $mid . '" ).parent().appendTo( "body" );
					$( ".geo2_maps_map > #geo2_maps_map_' . $mid . '" ).parent().addClass( "geo2_map_fullscreen" ).css( "z-index", ' . $z_index . ' );
					$( ".geo2_maps_map > #geo2_maps_map_' . $mid . '" ).parent().css( {
								position: "fixed",
								width: "100%",
								height: "100%"
					} );

					// keyup fires after action. to stop action use keydown
					$( document ).' . $key . '( function( event ) {
						if ( !location.hash.startsWith( "#full_screen_map-" ) ) {
							var code;
							if ( event.key !== undefined) {
								code = event.key;
							} else if ( event.keyIdentifier !== undefined) {
								code = event.keyIdentifier;
							} else if ( event.keyCode !== undefined) {
								code = event.keyCode;
							}
							if (code === "Escape" || code === "Esc" || code === 27) {
								historyBack();
							}

							// Update location center when images are switched with arrows
							if (code === "ArrowLeft" || code === "ArrowUp" ||
								code === 37 || code === 38 || code === "ArrowRight" || code === "ArrowDown" || 
								code === 39 || code === 40 ) {
								// map was jumping without preventDefault even tho it is in Fancybox3 js as well
								event.preventDefault();
								center_' . $mid . ' = map_' . $mid . '.getCenter();
  						}
						}
					} );
					$( "#geo2_fs_' . $mid . '" ).removeClass( "geo2_fs_out" ).addClass( "geo2_fs" );
					$( "body" ).css( "overflow", "hidden" );
					map_' . $mid . '.setView( { zoom: new_zoom } );
				} else {
					historyBack();
				}
			} );';
	return $full_screen;
}
