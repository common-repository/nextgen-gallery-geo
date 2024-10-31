/*!
* File type: JavaScript Document
* Plugin: Geo2 Maps Add-on for NextGEN Gallery
* Description: Code for loading maps on demand and opening galleries in Worldmap Mode using Ajax
* Author: Pawel Block
* Version: 2.0.1
* Version: 2.0.2 Variables: words, lightbox_words, pic_desc_words correctly declared before use. Added "&& pic_desc_words" in "performs search/remove on response[2]" section.
* Version: 2.0.3 Security improved. Search for unsafe words corrected. 
*/


// Ajax loader animation
jQuery( document ).ready( function()
{
	 jQuery( this ).ajaxStart( function()
	 {
	   jQuery( 'body' ).append( '<div id=\'geo2_overlay\'><img src=\'' + geo2_Ajax.geo2url + '/css/ajax-loader.gif\' /></div>' );
	 });

	 jQuery( this ).ajaxStop( function()
	 {
	   jQuery( '#geo2_overlay' ).remove();
	 });
});

//Gets map options

// Ajax map slider
function geo2_maps_showmap_ajax( mid, geo2_map_code, geo2_map_approved_code )
{ 
	// Does if map is already loaded
	if ( jQuery( '#geo2_maps_map_' + mid ).length > 0 ) 
	{
		jQuery( '#geo2_maps_' + mid ).slideToggle( 'slow' );
		jQuery( '#geo2_slide_' + mid ).toggleClass( 'geo2_active' );
	} else {
		// Ajax request
		jQuery.post(
			geo2_Ajax.ajaxurl, 
			{
				'action':'geo2_maps_showmap',
				'options': geo2_options,
				'nonce': geo2_Ajax.nonce
			}, 
			// Showmap (first time)
			function( response )
			{
				// console.log( response );
				response = response.split('{split}');
				var res0 = response[0];
				var full_words_list = JSON.parse( atob( geo2_map_approved_code ) );
				var words, lightbox_words, pic_desc_words ;
				if ( full_words_list.indexOf( '{lbox}' ) != -1 ) 
				{ 
					var index = full_words_list.indexOf( '{lbox}');
					words = full_words_list.slice( 0, ( index ) );
					lightbox_words = full_words_list.slice( ( index + 1 ) );
					if ( lightbox_words.indexOf( '{pic_desc}' ) != -1 ) {
						var desc_index = lightbox_words.indexOf( '{pic_desc}' );
						pic_desc_words = lightbox_words.slice( ( desc_index + 1 ) );
						lightbox_words = lightbox_words.slice( 0, ( desc_index ) );
					}
					split_data( res0, words );
				} else {
					words = full_words_list;
					split_data( res0, words );
				}
				
// http://www.example.com/post.php?=alert('XSS')
// http://elgoog.im/search?q=moc.elgoog.www://ptth
// https://yourBankWebsite.com/account?id=<script>[alert('!')]</script>
// http://servername/index.php?search="><script>alert(0)</script>
				
				function geo2_maps_validate_data( res ) 
				{
					for (i = 0; i < res.length; ++i) {
						var warning_text = '';
						let resLower = res[i].toLowerCase();
						if ( res[i].includes( 'http' ) || res[i].includes( 'ftp' ) ) {
							if ( res[i].match(/^(http|https|ftp)\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|net|org|biz|info|pro|[a-z]{3,10}|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/) == null ) {
								warning_text = 'Validation error. URL not in the correct format.';
								console.log( res[i] );
								alert( warning_text );
								throw new Error( warning_text );
							}
						} else if ( res[i].match(/^[^\\<>"`]+$/) == null ) {
							warning_text = 'Validation error. One of these unsafe HTML characters was detected: " ^\\<>"` ". For security they are not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
							console.log( res[i] );
							alert( warning_text );
							throw new Error( warning_text );
						} else if (
							resLower.search(/javascript\b/) !== -1 ||
							resLower.search(/phpinfo\b/) !== -1 ||
							resLower.search(/html\b/) !== -1 ||
							resLower.search(/\bscript\b/) !== -1 ||
							resLower.search(/\bobject\b/) !== -1 ||
							resLower.search(/\bapplet\b/) !== -1 ||
							resLower.search(/\bembed\b/) !== -1 ||
							resLower.search(/phpsession\b/) !== -1 ||
							resLower.search(/iframe\b/) !== -1 )
						{
							warning_text = 'Validation error. One of these unsafe words was detected: "javascript", "phpinfo", "HTML" "SCRIPT", "OBJECT", "APPLET", "EMBED", "PHPSESSID", or "IFRAME" . For security they are not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
							console.log( res[i] );
							alert( warning_text );
							throw new Error( warning_text );
						} else if ( 
							res[i].match(/eval\s*\(/) != null ||
							res[i].match(/system\s*\(/) != null ||
							res[i].match(/alert\s*\(/) != null ||
							res[i].match(/void\s*\(/) != null ) 
						{
							warning_text = 'Validation error. One of these unsafe words was detected: "eval", "alert", "void" or "system" followed by "(". For security they are not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
							console.log( res[i] );
							alert( warning_text );
							throw new Error( warning_text );
						}  else if (
							res[i].search(/function\s+\w+\s*\(/g) !== -1 ||
							resLower.search(/function\s*\(/g) !== -1 || // Search for the Function constructor syntax
							res[i].search(/=\s*\w+\s*=>/g) !== -1 || // Search for the Arrow function syntax with one parameter without brackets.
							res[i].search(/\(\s*\)\s*=>/g) !== -1 || // Search for the Arrow function syntax with one parameter with brackets only.
							res[i].search(/\(.*\)\s*=>/g) !== -1 ) // Search for the Arrow function syntax with one parameter with brackets and any characters between them.
						{
							warning_text = 'Validation error. Javascript function syntax detected. For security this is not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
							console.log( res[i] );
							alert(warning_text);
							throw new Error(warning_text);
						}
					}
				}
				
				function split_data( res, words )
				{
					for (i = 0; i < words.length; ++i) {
						res = res.split( words[i] ).join(' ');
					}
					res = res.split(' ');
					for (i = 0; i < res.length; ++i) {
						if ( !isNaN(res[i]) ) {
							res.splice(i, 1);
							i--;
						}
					}
					geo2_maps_validate_data( res );
				}
				
				// performs search/remove on response[1] - Lightbox code
				if ( response[1].length > 0 ){
					var res1 = response[1];
					split_data( res1, lightbox_words );
				}
				
				// performs search/remove on response[2] - Fancybox 3 Bottom Caption code
				if ( response[2].length > 0 && pic_desc_words ) {
					var str = '';
					var res2 = JSON.parse( response[2] );
					for ( i = 0; i < res2.length; ++i ) {
						for ( const key in res2[i] ) {
							str += key +' ' + res2[i][key];
						}
					}
					split_data( str, pic_desc_words );
				}
				
				jQuery( '#geo2_maps_' + mid ).html( atob(geo2_map_code).replace( '{geo2_map_data}', response[0] ).replace( '{geo2_infobox_data}', response[1] ).replace( '{geo2_pin_desc_data}', response[2] ) );
				jQuery( '#geo2_maps_' + mid ).slideToggle( 'slow' );
				jQuery( '#geo2_slide_' + mid ).toggleClass( 'geo2_active' );
				eval( 'nggGeo2Map_' + mid + '( \'\',\'\' );' );
			}
		);
	}
	return false;
}
// Validates data
function geo2_maps_validate_lightbox_data( res ) 
{ 	
	// Fancybox existing function syntaxes.
	res = res.replace(/\(\s*function\(\s*\$\s*\)\s*\{\s*\$\.fancybox\(/g, "" );
	res = res.replace(/'titleFormat'\s*:\s*function\(\s*title, currentArray, currentIndex, currentOpts\s*\)\s*\{\s*r/g, "" );
	res = res.replace(/'onComplete'\s*:\s*function\(\s*\)\s*\{\s*\$\(\s*"#fancybox-wrap"/g, "" );
	res = res.replace(/function\(\)\s*\{\s*\$\(\s*"#fancybox-title"/g, "" );
	// Fancybox 3 existing function syntaxes.
	res = res.replace(/Object.keys\( pic_desc\[i\] \)\[0\]/g, "" );
	res = res.replace(/\( function\( \$ \) \{\s*jQuery\.fancybox3\.open\(\[/g, "" );
	res = res.replace(/caption : function\( instance, item \) \{/g, "" );
	res = res.replace(/\$\('\.fancybox3-button--thumbs' \)\.click\( function\(\)/g, "" );
	res = res.replace(/on\( "DOMMouseScroll mousewheel", function \( e \)/g, "" );
	res = res.replace(/function geo2_maps_lightbox_\d+\( imageSrc \)\s*\{\s*\( function/g, "" );
	let resLower = res.toLowerCase();

	if ( 
		resLower.search(/javascript\b/) !== -1 ||
		resLower.search(/phpinfo\b/) !== -1 ||
		resLower.search(/html\b/) !== -1 ||
		resLower.search(/\bscript\b/) !== -1 ||
		resLower.search(/\bobject\b/) !== -1 ||
		resLower.search(/\bapplet\b/) !== -1 ||
		resLower.search(/\bembed\b/) !== -1 ||
		resLower.search(/phpsession\b/) !== -1 ||
		resLower.search(/iframe\b/) !== -1 
	) {
		warning_text = 'Validation error. One of these unsafe words was detected: "javascript", "phpinfo", "HTML" "SCRIPT", "OBJECT", "APPLET", "EMBED", "PHPSESSID", or "IFRAME" . For security they are not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
		console.log( res );
		alert( warning_text );
		throw new Error( warning_text );
	} else if ( 
		res.match(/eval\s*\(/) != null ||
		res.match(/system\s*\(/) != null ||
		res.match(/alert\s*\(/) != null ||
		res.match(/void\s*\(/) != null ) 
	{
		warning_text = 'Validation error. One of these unsafe words was detected: "eval", "alert", "void" or "system" followed by "(". For security they are not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
		console.log( res );
		alert( warning_text );
		throw new Error( warning_text );
	} else if (
		res.search(/function\s+\w+\s*\(/g) !== -1 ||
		resLower.search(/function\s*\(/g) !== -1 || // Search for the Function constructor syntax
		res.search(/=\s*\w+\s*=>/g) !== -1 || // Search for the Arrow function syntax with one parameter without brackets.
		res.search(/\(\s*\)\s*=>/g) !== -1 || // Search for the Arrow function syntax with one parameter with brackets only.
		res.search(/\(.*\)\s*=>/g) !== -1 ) // Search for the Arrow function syntax with one parameter with brackets and any characters between them.
	{
		warning_text = 'Validation error. Javascript function syntax detected. For security this is not allowed anywhere in your file name, ALT and Title Text / Description. This is to prevent "Man In The Middle" attack on your connection with a server.';
		console.log( res );
		alert(warning_text);
		throw new Error(warning_text);
	}
	//return res;
}

// Ajax lightbox (used by Worldmap)
function geo2_maps_lightbox_ajax( gid )
{
	jQuery.post(
		geo2_Ajax.ajaxurl, 
		{ 
			'action': 'geo2_maps_lightbox',
			'gid': gid,
			'nonce': geo2_Ajax.nonce
		},
		function( response )
		{	
			if ( undefined !== response.success && false === response.success ) {
				return;
			}
			geo2_maps_validate_lightbox_data( response );
			eval( response );
		}
	);
}
