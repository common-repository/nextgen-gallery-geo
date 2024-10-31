/// <reference path="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0">
/// <reference path="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.2-vsdoc.js">

// currently the module only handles a single minimap for a single map
function MinimapModule(map, credentials, atStart, style, height, width, topOffset, sideOffset) {
	var module = this;
	this.ParentMap = null;
	this.Minimap = null;
	this.MinimapViewHandlerId = null;
	this.MapViewHandlerId = null;
	this.MinimapPolygonOptions = {
		strokeColor: 'rgba(0, 0, 0, 0.5)',
		//strokeColor: new Microsoft.Maps.Color(128, 0, 0, 255),
		strokeThickness: 1,
		fillColor: 'rgba(255, 255, 255, 0.5)'
		//fillColor: new Microsoft.Maps.Color(128, 200, 200, 255)
	};

	// when the main map moves, update the display
	this.UpdateMinimap = function() {
		if (!module.Minimap) {
			return;
		}
		var bounds = module.ParentMap.getBounds();

		// set the map view to contain it
		// module.Minimap.setView({ bounds: bounds, padding: 40 });
		module.Minimap.setView({
			center: map.getCenter(),
			zoom: (map.getZoom() - 3) < 1 ? 1 : (map.getZoom() - 3)
		});

		// make a rectangle with the corners
		var nw = bounds.getNorthwest();
		var se = bounds.getSoutheast();
		var ne = new Microsoft.Maps.Location(se.latitude, nw.longitude);
		var sw = new Microsoft.Maps.Location(nw.latitude, se.longitude);
		var corners = [nw, ne, se, sw, nw];
		var rect = new Microsoft.Maps.Polygon(corners, module.MinimapPolygonOptions);

		// display the rectangle
		module.Minimap.entities.clear();
		module.Minimap.entities.push(rect);
	};

	// update the parent map when the user moves the minimap view
	this.UpdateMapFromMinimap = function(e) {
		if (!module.Minimap) {
			return;
		}
		module.ParentMap.setView({
			center: module.Minimap.getCenter()
		});
	};


	this.Initialize = function(map, credentials, atStart, style, height, width, topOffset, sideOffset) {
		// jQuery("head").append('<link type="text/css" rel="Stylesheet" href="http://wpgallery.pasart.webd.pl/wp-content/plugins/ngg-geo2-maps/MinimapModule/MinimapModule.css" />');

		module.ParentMap = map;
		module.MapViewHandlerId = Microsoft.Maps.Events.addHandler(module.ParentMap,
			"viewchangeend", module.UpdateMinimap);
		var container = jQuery('<div class="minimap-container" style="position: absolute; width: ' + width + 'px; height: ' + height + 'px; top: ' + topOffset + 'px; left: ' + sideOffset + 'px; box-shadow: 2px 2px 4px rgba(0,0,0,.5);"/>')
			.appendTo(jQuery(map.getRootElement()).parent())
			.hide();

		// show button
		//.delegate( module.ParentMap, "mapresize", function(event) { event.preventDefault(); })
		// 				var opener = jQuery('<div class="minimap-glyph minimap-glyph-show">&raquo;</>')
		var opener = jQuery('<div class="minimap-glyph minimap-glyph-show">' +
				'<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" width="24" height="24">' +
				'<path d="M5 19L5 12L7 12L7 17L17 17L17 7.01L12 7L12 5.01L18.99 5L19 19L5 19ZM16 12L16 16L12 16L13.29 14.71L11.3 12.71L12.71 11.3L14.71 13.29L16 12ZM5 11L5 5L11 5L11 11L5 11Z" id="b1KeHlZMW4">' +
				'</path>' +
				'</svg>' +
				'<div/>')
			.appendTo(jQuery(map.getRootElement()).parent())
			.click(function() {
				jQuery(".geo2_fullscreen_icon").css("left", '0px');
				container
					.show(0)
					.css("width", width + 'px')
					.css("height", height + 'px');
				closer.delay(200).fadeIn();
				if (!module.Minimap) {
					closer.appendTo(container);
					jQuery(this).hide();
					module.Minimap = new Microsoft.Maps.Map(container[0], {
						credentials: credentials,
						mapTypeId: style,
						showDashboard: false,
						showCopyright: false,
						showScalebar: false,
						showLogo: false, // undocumented but it works
						disableZooming: true,
						fixedMapPosition: true
						// these are probably mot needed
						//showMapTypeSelector: false,
						//showLocateMeButton: false,
						//disableScrollWheelZoom: true,
					});
				} else {
					jQuery(this).delay(200).fadeOut();
				}
				module.MinimapViewHandlerId = Microsoft.Maps.Events.addHandler(module.Minimap, "viewchangeend", module.UpdateMapFromMinimap);
				module.UpdateMinimap();

			});

		// hide button
		// var closer = jQuery('<div class="minimap-glyph minimap-glyph-hide">&laquo;</>')
		var closer = jQuery('<div class="minimap-glyph minimap-glyph-hide" >' +
				'<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" width="24" height="24">' +
				'<path d="M7 12L7 17L17 17L17 7.01L12 7L12 5.01L18.99 5L19 19L5 19L5 12L5 12L7 12ZM12.29 13.71L11 15L11 11L15 11L13.71 12.29L15.7 14.29L14.29 15.7L14.29 15.7L12.29 13.71ZM11 5L11 11L5 11L5 5L5 5L11 5Z" id="a1BgVq7cK4"></path>' +
				'</svg>' +
				'<div/>')
			.appendTo(container)
			.click(function() {
				jQuery(".geo2_fullscreen_icon").css("left", '24px');
				Microsoft.Maps.Events.removeHandler(module.MinimapViewHandlerId);
				module.MinimapViewHandlerId = null;
				//module.Minimap.dispose();
				//module.Minimap = null;
				container
					.css("width", "24px")
					.css("height", "24px")
					.delay(200).hide(0);
				opener.delay(200).show(0);
			});

		if (atStart) {
			opener.click();
		}
	};

	this.Initialize(map, credentials, atStart, style, height, width, topOffset, sideOffset);
}

Microsoft.Maps.moduleLoaded('MinimapModule');