=== Geo2 Maps Add-on for NextGEN Gallery ===
Contributors: pablo2, frest.de
Tags: map, photos, gps, nextgen, gallery, exif, bing, images, gpx, lightbox, coordinates, geolocation
Donate link: https://www.paypal.com/PawelBlock
Requires at least: 3.0.1
Tested up to: 6.5.5
Requires PHP: 7.2+
Stable tag: 2.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

NGG Geo2 Maps Add-on is a flexible extension plugin for NextGEN Gallery, displaying maps with your photos, galleries or albums located using EXIF GPS data or geocoding.
The NextGEN Gallery plugin is required to use this extension.

== Description ==

!!! Please notice that “Ratings” do not represent the current plugin version quality - which was updated and corrected. Please check by yourself and leave feedback.

NGG Geo2 Maps Add-on is a flexible extension plugin for free or Pro NextGEN Gallery, displaying maps with your photos, galleries or albums located using EXIF GPS data or geocoding.

The goal of this plugin is to add to WordPress similar geolocation functionality as other web-based self-hosted photo manager apps have like Gallery 3, Piwigo, PhotoPrism or LibrePhotos.

The plugin is using Microsoft Bing Maps and requires a free Bing Maps API Key to function.

= Features =

* Maps with geo-located photos - Create maps with your photos, using their GPS data.
* Support for different representation of photos on a map: default BING maps pushpins, thumbnails (round, rectangular)
* Geocoding using gallery Title
* Include your maps by using Shortcodes
* Include maps automatically in every post with a gallery
* Open maps on requests (via Ajax)
* Create maps with specific photos only
* Create multiple maps on one page
* Itemized options panel: configure many available options
* Language support: users can create translations
* Route mode with GPX, XML, KMZ, KML, GeoRSS support: displays your travel route together with photos or any other data
* Worldmap mode: shows all or specific photos, galleries and albums on an overview map, open or preview galleries (via Ajax)
* Preview photos by clicking on a Pushpins/Thumbnails in an Infobox or one of 3 Lightboxes (Fancybox, Slimbox 2 or Fancybox 3)
* Customise Lightboxes: colours and controls
* Fancybox 3 Bottom Caption Panel with additional image EXIF and GPS info
* Enlarge maps to full screen
* Mini Map module
* Different visual styles for maps

== Installation ==

1.  Install NextGEN Gallery - This plugin can only work with it. Create some galleries.
2.  Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation.
3.  Acquire the Bing Maps API Key from the Bing Maps Dev Center at https://www.bingmapsportal.com (You can find a link on the Geocoding tab inside this plugin.)
4.  Go to the admin panel ( NextGEN Gallery -> Geo2 Maps ) and paste the Key to the corresponding field on the Geocoding tab.
5.  Configure any options as you require.

= Optional =

1.  Acquire free Mapquest API Key at https://developer.mapquest.com/ and input to the corresponding field on the Geocoding tab.
2.  Input a valid email to use Nominatim on the same tab. Please read their Usage Policy at https://operations.osmfoundation.org/policies/nominatim.
3.  Create a Shortcode and place it anywhere on your site.

== Frequently Asked Questions ==

**How can I use Geo2 Maps?**

There are three ways of using Geo2 Maps Add-on:

*1. Generate maps automatically*

The plugin searches for galleries in your posts and includes a map if there is geodata available. Easy to use, you don't need to change anything on your theme.

*2. Include maps using the shortcode [geo2]*

Simply use the shortcode [geo2] anywhere in your posts. You can define plenty of options.

*3. Use the php-function* (for theme development)

If you want to embed the plugin in your theme, you can use the following php-functions:
<? geo2_maps_show_map($options); ?> - specify which galleries using $options['id']
<? geo2_maps_show_single($options); ?> - specify which pictures using $options['pid']
<? geo2_maps_show_worldmap($options); ?> - specify galleries or/and albums using $options['include'] = "galleries", "albums" or "all"

If you don't define any gallery ids, the plugin will search for the gallery ID in the post.
If you don't define any picture ids or tag names, the plugin will show an empty map.
For Worldmap if you don't define any gallery or album ids, the plugin will show all galleries and albums. 
$options must be defined as an array, containing the same data as the shortcodes.
The parameters are optional. By delivering the IDs of the objects or tag names, this function can be used outside of The Loop.

Example:
$options = array( $options['map_width'] = 'auto', $options['map_height'] = '500px' );

**Shortcode? Which Shortcodes?**

There is only one Shortcode: [geo2] followed by options.

All available Shortcode options are described in the Geo2 Maps options panel. ( NextGEN Gallery -> Geo2 Maps )

Example: 
[geo2 id=18 map=road map_height=100px map_width=200px exif=1 minimap=1]
[geo2 worldmap=1 include=all map=road map_height=500px map_width=auto]
[geo2 pid=2,4,15 xmlurl=http://path_to_your_file_with_route.gpx]

**Can I use this plugin, even if there is no GPS data stored in the EXIF of my photos?**

Yes, to some extent. The plugin will try to place a gallery or an album on a map using geocoding even if no photos have GPS data. You can use different providers for geocoding, sometimes they generate differing results. Try to define a clear and unique Title corresponding to a location name.
Unfortunately, the plugin will not show photos on a map if they have no GPS coordinates! But don't worry. You can add them by using free "Geosetter" for example.

**What is the Ajax Mode?**

If you don't want maps to get loaded every time, you can use the Ajax mode. If activated, the plugin will display a button. Clicking on it will load this map using an Ajax request.

**What is the Worldmap Mode?**

The Worldmap mode gives you the possibility to show an overview of all or specific galleries and albums of your blog. If the preview picture has GPS coordinates gallery will be placed using it. Otherwise, the position will be geolocated using the title. You can include the Worldmap in your post by using the shortcode [geo2 worldmap=1].

**What is the Route Mode?**

You can use the Route mode, to display your travel route. Therefore, photos taken during this trip can be connected by it. The Route mode can be activated for all galleries using the options panel. Alternatively, you can activate the route mode for single maps using the shortcode.
If you have recorded your route to a file, you can upload it to the gallery folder. Place a path to it in a map Shortcode.

Example: [geo2 xmlurl=http://path_to_your_file_with_route.gpx]

Accepted are common geospatial XML file formats such as KML (Keyhole Markup Language), KMZ (compressed KML), GeoRSS, GML (Geography Markup Language, exposed via GeoRSS), and GPX (GPS Exchange Format).

You can show any information contained in these files on a map.

**Why I can't upload my route file to WordPress Media Library?**

For security reasons, WordPress is allowing to upload files only of a specific type. You can unlock uploading a specific file type by telling WordPress which MIME Type it should consider as safe. Some additional plugins can do this easily. I'm using Enhanced Media Library.

These are the MIME Types you may want to register:
    gpx => 'application/gpx+xml'
    xml => 'application/xml'
    kml => 'application/vnd.google-earth.kml+xml'
    kmz => 'application/vnd.google-earth.kmz'

**How can I define the map style?**

Some options like map-width and map-height can be changed in the options panel. The maps are using the div-class .geo2_maps_map, so you can use CSS, too. 

**Can I reset all parameters to default values without uninstalling the plugin?**

You. There is an option to do so at the bottom of the "General" tab. You will still need to deactivate and reactivate it after selecting this option.

== Screenshots ==
1. Pushpins with hover effect. Aerial map style.

2. The Infobox showing detailed information about a picture.

3. Rectangular Thumbnails with border in portrait or landscape orientation. Birds Eye View map style.

4. World Map with square Thumbnails. No map Dashboard.

5. Worldmap with different pins for albums and galleries. No map Dashboard.

6. Closed map in Ajax Mode shows enable bar. 

7. Opened map in Ajax Mode with round Thumbnails. Road map style.

8. Photo preview with Fancybox 3 Lightbox.

9. Route Mode displaying .kmz path. Road map style.


== Changelog ==

= V2.0.9 - 24.06.2024 =

* Update: Options text corrected.

* Bugfix: Thumbnails full filename acquired from pictures meta_data in database prevents incompatibility with older file naming

= V2.0.8 - 19.11.2023 =

* NEW: Pictures in galleries are now sorted in the same way as in the NextGEN Gallery

* Update: Recommended error handling added for Exif read data

* Bugfix: Excluding images in galleries works again

= V2.0.7 - 17.11.2023 =

* Update: AutoMap function now shows galleries on a map if no albums are found
* Update: Security improved
* Update: Code fully aligned with WordPress coding standards

* Bugfix: All PHP warnings resolved
* Bugfix: All reasonable WordPress standards check warnings resolved
* Bugfix: Several bugs found and resolved
* Bugfix: Infoboxes should not change location
* Bugfix: Worldmap pushpins or thumbs will not open a missing page

= V2.0.6 - 17.10.2022 =

* Bugfix: Many PHP warnings resolved
* Bugfix: array_key_exists() error resolution implemented

= V2.0.5 - 04.05.2022 =

* Update: How to create Gallery Map with a Shortcode added to the General tab
* Update: Map options description amended
* Update: Added compatibility with PHP 8.0

* Bugfix: Map created with a Shortcode with route path only searching unnecessarily for any galleries in a page content corrected
* Bugfix: CSS corrected

= V2.0.4 - 21.08.2021 =

* Update: New map options have been added to hide parts of map interface

* Bugfix: Shortcode parameters containing capital letters not working
* Bugfix: Loaded route files with shapes with balloon description not showing Infobox correctly

= V2.0.3 - 15.05.2021 =

* Update: Slimbox 2 code slightly improved, version updated to 2.06

* Bugfix: Plugin slug amended to match WordPress old plugin slug
* Bugfix: Slimbox image counter now shows numbers correctly

* Security fix : unserialize() function removed creating PHP object injection vulnerability

= V2.0.2 - 04.05.2021 =

* Bugfix: Reference to CSS file corrected
* Bugfix: Reference to removed examples.php removed

= V2.0.1 - 03.05.2021 =

* NEW: Bing Maps API key and MapQuest API key authentication process added with notification messages 
* NEW: Activation status added for Bing Maps and MapQuest services
* NEW: Upload media button added for the route file

* Changed: Bing Maps API key field moved to the "General" tab, description amended

* Update: Wp-color-picker-alpha Javascript updated to version 3.0.0

* Bugfix: Remains of the old code removed causing plugin to crash for some users
* Bugfix: Deactivation warning enabled

* Security fix : all user entered data is now validated

= V2.0.0 - 02.03.2021 by Pawel Block =

* NEW: Slimbox 2.04 (with a resize image box option) and Fancybox 3 Lightbox plugins to preview images
* NEW: Fancybox 3 Bottom Caption Panel with EXIF information
* NEW: Thumbnails customize options 
* NEW: Additional Auto Mode and Worldmap options
* NEW: Customise options for Lightboxes and Infobox
* NEW: Added support for GPX, XML, KMZ, KML, GeoRSS files
* NEW: Customise options for shape from GPX, XML, KMZ, KML or GeoRSS files
* NEW: Better plugin security
* NEW: Many new options to create and customize maps
* NEW: Create maps with specific photos
* NEW: Full screen button for maps

* Changed: Redesigned admin options panel
* Changed: Code updated to support Bing Maps API V8
* Changed: Thumbnails generated using HTML5 canvas
* Changed: Geocoding updated, Google geocoding removed to not violate Google API policy
* Changed: Mini Map module updated
* Changed: Preview Map removed on WordPress request

= V1.0 - 05.09.2012 by Frederic Stuhldreier =

* NEW: Shortcode system with several new options (map-size, map-style, thumbnails, single pictures, Ajax, etc.)
* NEW: Thumbnails styled using CSS 3 or phpThumb() class
* NEW: Activate and deactivate the maps dashboard, minimap and scalebar using the admin panel or shortcodes
* NEW: Change style of marked routes using the admin panel
* NEW: .GPX support for route-mode
* NEW: Include your maps using AJAX
* NEW: Set a map id for individual styling
* NEW: Lightbox integrated (Fancybox, Slimbox 2)
* NEW: Lightbox AJAX request for other photos (Worldmap mode)

* Changed: Admin panel sub-menu now located in NextGen Gallery menu
* Changed: Significantly reduced number of SQL queries
* Changed: >1 map / post now possible
* Changed: Use stored EXIF data, if available

* Bugfix: Example map don't work
* Bugfix: Save options bug

= V0.6 - 13.05.2012 =
* NEW: Use [geo2] shortcode everywhere by choosing the gallery-ID: [geo2 id=Gallery-ID]
* NEW: Worldmap-shortcode: [geo2 worldmap]
* NEW: Preview Map
* NEW: Choose between pushpins or thumbnails
* Changed: Worldmap works faster, coordinates bug fixed
* Changed: Easy gallery linking (Worldmap)
* Bugfix: Broken maps, wrong data on some servers

= V0.5 - 21.04.2012 =
* NEW: Show advanced EXIF data
* NEW: Album support
* Bugfix: Exif_read_data used URL instead of a path (don't work on a few servers)
* Bugfix: Wrong coordinates, fixed and improved
* Changed: Shortcode [geo2] works faster
* Changed: Include via function improved
* bugfix : Don't show shortcode [geo2]

= V0.4 - 13.03.2012 =
* NEW: I18n
* NEW: Added languages: german, english
* NEW: Disable geocoding (options panel)
* Changed: Auto-mode-> usage of filter ngg_gallery_output (much faster, no sql needed any more)
* Changed: New options panel
* Bugfix: Route-mode enabled
* Bugfix: Geocoding error (Nominatim)

= V0.35 - 05.03.2012 =
* Bugfix: Fixed some bugs (route-mode)