<?php
/**
 * Various functions and actions used by the plugin on the admin settings page.
 *
 * @package    Geo2 Maps Add-on for NextGEN Gallery
 * @subpackage Administration
 * @since      1.0.0
 * @since      2.0.0 Amended and supplemented with additional code and functions.
 * @since      2.0.1 Amended functions: geo2_maps_deactivation_warning(), geo2_maps_options_page().
 * @since      2.0.3 Amended functions: geo2_maps_deactivation_warning(), geo2_maps_options_page().
 * @since      2.0.4 Amended function: geo2_maps_options_page().
 * @since      2.0.5 Amended function: geo2_maps_options_page().
 * @since      2.0.7 Amended function: geo2_maps_plugin_admin_scripts(), geo2_maps_deactivation_script(), geo2_maps_options_page().
 * @since      2.0.8 Amended function:geo2_maps_options_page().
 * @since      2.0.9 Amended function:geo2_maps_options_page().
 * @author     Pawel Block &lt;pblock@op.pl&gt;
 * @copyright  Copyright (c) 2023, Pawel Block
 * @link       http://www.geo2maps.plus
 * @license    https://www.gnu.org/licenses/gpl-2.0.html
 */

// Security: Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display the validation errors and update messages.
add_action( 'admin_notices', 'geo2_maps_admin_notices' );
/**
 * Shows messages to users about settings validation problems.
 *
 * Messages created during the settings validation process using
 * add_settings_error() WordPress function.
 *
 * @since 2.0.0
 * @since 2.0.7 "Settings saved" message added.
 *
 * @see   function geo2_maps_options_validate($options) in plugin.php
 */
function geo2_maps_admin_notices() {
	// Shows a message if settings were saved. Code from: https://digwp.com/2016/05/wordpress-admin-notices/.
	if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification

		echo '<div id="message" class="notice notice-success is-dismissible">
	<p><strong> ' . esc_html__( 'Settings saved.', 'ngg-geo2-maps' ) . ' </strong></p>
</div>';
	} elseif ( isset( $_GET['settings-error'] ) && ! $_GET['settings-error'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div id="message" class="notice notice-error is-dismissible">
		<p><strong>' . esc_html__( 'An error occurred when saving ! ', 'ngg-geo2-maps' ) . '</strong></p>
</div>';
	}
	settings_errors( 'plugin_geo2_maps' );
}

// Checks if dependent plugin NextGEN Gallery is activated.
add_action( 'plugins_loaded', 'geo2_maps_dependency' );
/**
 * Checks if NextGEN Gallery is activated and only then continue.
 *
 * Geo2 Maps can only work with the NextGEN Gallery.
 *
 * @since 2.0.0
 */
function geo2_maps_dependency() {
	if ( ! defined( 'NGG_PLUGIN_VERSION' ) ) {
		add_action(
			'all_admin_notices',
			function () {
				echo '<div class="updated error"><p>';
				echo esc_html__( 'Please install the NextGEN Gallery plugin before activation of the Geo2 Maps Add-on.', 'ngg-geo2-maps' );
				echo '</p></div>';
			},
			10,
			2
		);
	}
}

add_action( 'admin_init', 'geo2_maps_options_init' );
/**
 * Init plugin options.
 *
 * Registers and validates options using register_setting() function.
 *
 * @since 1.0.0
 * @since 2.0.0 Amended name, values, added security.
 *
 * @see   function geo2_maps_options_validate($options) in plugin.php
 * @link  https://developer.wordpress.org/reference/functions/register_setting
 */
function geo2_maps_options_init() {
	// Security: Refer to https://premium.wpmudev.org/blog/activate-deactivate-uninstall-hooks.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Checks if there is a paid version Geo2 Maps Plus.
	if ( is_plugin_active( GEO2_MAPS_PLUGINS_DIR_URL . '/ngg-geo2-maps-plus/plugin.php' ) ) {
		// Deactivates Geo2 Maps PLUS plugin.
		deactivate_plugins(
			GEO2_MAPS_PLUGINS_DIR_URL . '/ngg-geo2-maps-plus/plugin.php'
		);

		add_settings_error(
			'plugin_geo2_maps',
			'plugin_deactivated',
			esc_html__( 'Geo2 Maps Plus Add-on was deactivated.', 'ngg-geo2-maps' ),
			'error'
		);
	}

	// Registers settings.
	register_setting(
		'plugin_geo2_maps',
		'plugin_geo2_maps_options',
		array(
			'sanitize_callback' => 'geo2_maps_options_validate',
		)
	);
}

add_action( 'admin_menu', 'geo2_maps_add_page', 99 );
/**
 * Adds admin settings page.
 *
 * Page will be placed as a sub menu page of the NextGEN Gallery menu with a name Geo2 Maps.
 *
 * @since 1.0.0
 * @since 2.0.0 Amended name, values, added security.
 *
 * @see   function geo2_maps_options_page()
 */
function geo2_maps_add_page() {
	$geo2_slug = plugin_basename( __FILE__ );
	add_submenu_page(
		// NextGEN Gallery constant.
		NGGFOLDER,
		__( 'Geo2 Maps', 'ngg-geo2-maps' ),
		__( 'Geo2 Maps', 'ngg-geo2-maps' ),
		'manage_options',
		$geo2_slug,
		'geo2_maps_options_page'
	);
}

add_action( 'admin_enqueue_scripts', 'geo2_maps_plugin_admin_scripts' );
/**
 * Adds scripts required by Geo2 admin settings page.
 *
 * This includes:
 * - css styles file
 * - color picker script
 * - admin tabs script
 * - Fancybox script used by NextGEn Gallery
 * It must be loaded on admin page because it's loaded by NGG only on pages with its galleries.
 *
 * @since 2.0.0
 * @since 2.0.3 Page domain adjusted to match WordPress slug.
 * @since 2.0.7 $hook comparison updated to avoid translation issues. Enqueued scripts version added and argument to load in the footer. admin-style.css version updated.Fancybox enqueued scripts and style moved to functions.php.
 *
 * @link  https://github.com/kallookoo/wp-color-picker-alpha
 * @see   function geo2_maps_admin_tabs_script().
 * @param string $hook Admin page domain name.
 */
function geo2_maps_plugin_admin_scripts( $hook ) {
	// Admin web page domain changes when translated.
	// English starts with is: nextgen-gallery.
	// Polish/Spanish starts with is: galeria-nextgen.
	// To avoid this issue only last unique part of the slug is compared.
	if ( ! str_ends_with( $hook, '_page_nextgen-gallery-geo/administration' ) ) {
		return;
	}

	// Adds NGG Geo2 Maps css.
	wp_enqueue_style( 'geo2_admin_styles', GEO2_MAPS_DIR_URL . '/css/admin-style.css', array(), '2.0.2', 'all' );

	// Adds Color picker.
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker-alpha', GEO2_MAPS_DIR_URL . '/js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), '3.0.0', true );
	wp_add_inline_script(
		'wp-color-picker-alpha',
		'jQuery( function() { jQuery( ".color-picker" ).wpColorPicker(); } );'
	);
	wp_enqueue_script( 'wp-color-picker-alpha' );

	// Adds image of pushpin icon from WordPress media library.
	wp_enqueue_script( 'upload_media_img_js', GEO2_MAPS_DIR_URL . '/js/upload-media-img.js', array( 'jquery' ), '2.0.1', true );
	add_action( 'admin_print_footer_scripts', 'geo2_maps_admin_tabs_script' );

	// Allows switching between tabs.
	add_action( 'admin_print_footer_scripts', 'geo2_maps_admin_tabs_script' );
}

/**
 * Allows switching between tabs.
 *
 * @since 2.0.0
 */
function geo2_maps_admin_tabs_script() {
	?>
	<!-- NextGEN Gallery Geo2 plugin -->
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var geo2_active_tab = localStorage.getItem("geo2_active_tab");
			if (geo2_active_tab) {
				var i, geo2_tab_content, geo2_tab_links;
				geo2_tab_content = document.getElementsByClassName("geo2_tab_content");
				for (i = 0; i < geo2_tab_content.length; i++) {
					geo2_tab_content[i].style.display = "none";
				}
				geo2_tab_links = document.getElementsByClassName("geo2_tab_links");
				for (i = 0; i < geo2_tab_links.length; i++) {
					geo2_tab_links[i].className = geo2_tab_links[i].className.replace("geo2_active", "");
				}
				document.getElementById(geo2_active_tab).style.display = "block";
				$('button[name="' + geo2_active_tab + '"]').addClass("geo2_active");
			} else {
				$('button[name="geo2_general"]').addClass("geo2_active");
			}

			var box_content, box_minimap, box_override, box_input, value;
			box_content = document.getElementsByClassName("box_to_close");
			for (var i = 0; i < box_content.length; i++) {
				box_content[i].style.display = "none";
			}
			box_input = document.getElementsByName("plugin_geo2_maps_options[lightbox]");
			for (var i = 0; i < box_input.length; i++) {
				if (box_input[i].checked === true) {
					value = box_input[i].value;
					document.getElementById(value).style.display = "block";
				}
			}

			box_minimap = document.getElementsByName("plugin_geo2_maps_options[minimap]");
			if (box_minimap[0].checked === true) {
				document.getElementById("minimap").style.display = "block";
			} else {
				document.getElementById("minimap").style.display = "none";
			}
		});

		function geo2_maps_openTab(evt, tagName) {
			localStorage.setItem('geo2_active_tab', jQuery(evt.currentTarget).attr('name'));
			var geo2_tab_content, geo2_tab_links;
			geo2_tab_content = document.getElementsByClassName("geo2_tab_content");
			for (var i = 0; i < geo2_tab_content.length; i++) {
				geo2_tab_content[i].style.display = "none";
			}
			geo2_tab_links = document.getElementsByClassName("geo2_tab_links");
			for (var i = 0; i < geo2_tab_links.length; i++) {
				geo2_tab_links[i].className = geo2_tab_links[i].className.replace(" geo2_active", "");
			}
			document.getElementById(tagName).style.display = "block";
			evt.currentTarget.className += " geo2_active";
		}

		function geo2_maps_openBox(evt, boxName) {
			var box_content = document.getElementsByClassName("box_to_close");
			for (var i = 0; i < box_content.length; i++) {
				box_content[i].style.display = "none";
			}
			document.getElementById(boxName).style.display = "block";
		}

		function geo2_maps_closeBoxes() {
			var box_content = document.getElementsByClassName("box_to_close");
			for (var i = 0; i < box_content.length; i++) {
				box_content[i].style.display = "none";
			}
		}

		function geo2_maps_openCheckBox(evt, boxName) {
			var box_content = document.getElementById(boxName);
			if (box_content.style.display === "none") {
				document.getElementById(boxName).style.display = "block";
			} else {
				document.getElementById(boxName).style.display = "none";
			}
		}
	</script>
	<!-- NextGEN Gallery Geo2 plugin -->
	<?php
}

add_action( 'admin_enqueue_scripts', 'geo2_maps_deactivation_script' );
/**
 * Adds deactivation warning only on the plugins page.
 *
 * Description.
 *
 * @since 2.0.0
 * @since 2.0.7  Style version added.
 *
 * @param string $hook WordPress page name.
 */
function geo2_maps_deactivation_script( $hook ) {
	// Checks if it is the plugins page.
	if ( $hook !== 'plugins.php' ) {
		return;
	}

	$raw_options = get_option( 'plugin_geo2_maps_options' );
	$options     = geo2_maps_convert_to_int( $raw_options );

	// Checks if user wants to restore default settings on deactivation.
	if ( $options['restore_defaults'] === 1 ) {
		// Adds NGG Geo2 Maps css.
		wp_enqueue_style( 'geo2_deactivate_modal_style', GEO2_MAPS_DIR_URL . '/css/deactivate-modal-style.css', array(), '2.0.0', 'screen' );

		add_action( 'admin_print_footer_scripts', 'geo2_maps_deactivation_warning' );
	}
}
/**
 * Creates deactivation warning code.
 *
 * @since 2.0.0
 * @since 2.0.1 jQuery selector for $deactivateLink amended
 * @since 2.0.3 Slug in jQuery selector for $deactivateLink updated
 */
function geo2_maps_deactivation_warning() {
	?>
	<div id="geo2_defaults_reset" class="geo2_modal" title="Reset default settings?">
		<div class="geo2_modal-dialog">
			<div class="geo2_modal-header">
				<h4><?php esc_html_e( 'Warning', 'ngg-geo2-maps' ); ?></h4>
			</div>
			<div class="geo2_modal-body">
				<div class="geo2_modal-panel active">
					<h3><?php esc_html_e( 'Default settings will be restored. Do you really want to do this?', 'ngg-geo2-maps' ); ?></h3>
					<?php esc_html_e( 'Cancel and uncheck the "Restore default settings" checkbox in the Geo2 Maps options to deactivate without losing your settings.', 'ngg-geo2-maps' ); ?>
				</div>
			</div>
			<div class="geo2_modal-footer">
				<a href="#" class="button button-secondary allow-deactivate"><?php esc_html_e( 'Deactivate', 'ngg-geo2-maps' ); ?></a>
				<a href="#" class="button button-primary button-close"><?php esc_html_e( 'Cancel', 'ngg-geo2-maps' ); ?></a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var $deactivateLink = $('[data-plugin*="nextgen-gallery-geo/plugin.php"].deactivate a'),
				$modal = $('#geo2_defaults_reset');

			geo2_maps_registerEventHandlers();

			function geo2_maps_registerEventHandlers() {
				$deactivateLink.click(function(evt) {
					evt.preventDefault();
					geo2_maps_showModal();
				});

				// Deactivate the plugin.
				$modal.on('click', '.geo2_modal-footer .allow-deactivate', function(evt) {
					window.location.href = $deactivateLink.attr('href');
					return;
				});

				// If the user has clicked outside the window, cancel it.
				$modal.on('click', function(evt) {
					var $target = $(evt.target);
					// If the user has clicked anywhere in the modal dialog, just return.
					if ($target.hasClass('geo2_modal-body') || $target.hasClass('geo2_modal-footer')) return;
					// If the user has not clicked the close button and the clicked element is inside the modal dialog, just return.
					if (!$target.hasClass('button-close') && ($target.parents('.geo2_modal-body').length > 0 || $target.parents('.geo2_modal-footer').length > 0)) return;

					geo2_maps_closeModal();
					return false;
				});
			}

			function geo2_maps_showModal() {
				// Display the dialog box.
				$modal.addClass('geo2_warning');
				$('body').addClass('has-geo2_modal');
			}

			function geo2_maps_closeModal() {
				$modal.removeClass('geo2_warning');
				$('body').removeClass('has-geo2_modal');
			}
		});
	</script>
	<?php
}

/**
 * Creates the options page.
 *
 * Contain mainly HTML code to create checkboxes, text inputs and other option selectors.
 *
 * @since 1.0.0
 * @since 2.0.0 Amended name, added code to create many additional options grouped in tabs.
 * @since 2.0.1 Bing API key moved, ACTIVATED signs and additional description added. Upload media button added for the route file.
 * @since 2.0.3 Unnecessary HTML code for close button for "Settings saved" message  removed
 * @since 2.0.4 Map options added (Locate Me button, Copyright, Terms link, Logo), capital letters removed from plugin $options keys. Route Mode description amended.
 * @since 2.0.5 Missing Shortcode to crate a Gallery Map added. Map options description amended. Fancybox 3 Color Options alignment corrected by adding brake lines.
 * @since 2.0.7 Added: escape functions, missing translations, translator comments and brake line. "Settings saved" message removed. Fancybox 3 options order changed.
 * @since 2.0.8 Add-on Basic and Plus version comparison updated.
 * @since 2.0.9 JIG description corrected. Header and info encapsulated in "div".
 */
function geo2_maps_options_page() {
	wp_enqueue_media();

	?>
	<div class="wrap geo2_wrap">
		
		<div class="geo2_donate">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick" />
				<input type="hidden" name="hosted_button_id" value="7JWUJ2J9RXWYU" />
				<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
				<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
			</form>
		</div>
		<h2><?php esc_html_e( 'Geo2 Maps Add-on for NextGEN Gallery', 'ngg-geo2-maps' ); ?></h2>
		<br />
		<?php
		printf(
			/* translators: 1: HTML link opening tag. 2: HTML link closing tag. */
			esc_html__( 'This plugin is an add-on for the %1$sNextGEN Gallery%2$s plugin.', 'ngg-geo2-maps' ),
			'<a href="http://wordpress.org/extend/plugins/nextgen-gallery/" title="NextGEN-Gallery Plugin" target="_blank">',
			'</a>'
		);
		?>
		<br />
		<?php
		printf(
			/* translators: 1: HTML link opening tag. 2: HTML link closing tag. 3: HTML link opening tag. 4: HTML link closing tag. 5: HTML link opening tag. 6: HTML link closing tag. */
			esc_html__( '%1$sNextGEN Gallery Geo%2$s was first written by Frederic Stuhldreier in 2012 and updated independently by %3$sPawel Block%4$s in 2021 to %5$sGeo2 Maps Add-on%6$s. Please help me to improve it by sending your translation or donation.', 'ngg-geo2-maps' ),
			'<a href="http://wordpress.org/plugins/nextgen-gallery-geo/" title="NextGEN Gallery Geo" target="_blank">',
			'</a>',
			'<a href="http://www.geo2maps.plus" title="www.geo2maps.plus" target="_blank">',
			'</a>',
			'<a href="http://wordpress.org/plugins/nextgen-gallery-geo/" title="Geo2 Maps Add-on" target="_blank">',
			'</a>'
		);
		?>
		<br /><br /><br />

		<div class="geo2_tab">
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'geo2_general' )" name="geo2_general"><?php esc_html_e( 'General', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'geocoding' )" name="geocoding"><?php esc_html_e( 'Geocoding', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'maps' )" name="maps"><?php esc_html_e( 'Maps', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'thumbnails' )" name="thumbnails"><?php esc_html_e( 'Thumbnails', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'pushpins' )" name="pushpins"><?php esc_html_e( 'Pushpins', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'infobox-lightbox' )" name="infobox-lightbox"><?php esc_html_e( 'Infobox', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'routes' )" name="routes"><?php esc_html_e( 'Routes', 'ngg-geo2-maps' ); ?></button>
			<button class="geo2_tab_links" onclick="geo2_maps_openTab( event, 'geo2_plus' )" name="geo2_plus"><b><?php esc_html_e( 'Geo2 Maps Plus', 'ngg-geo2-maps' ); ?></b></button>
		</div>

		<form action="options.php" method="post">
			<div class="geo2_float_save_button">
				<p class="submit geo2_submit_button">
					<input type="submit" class="geo2_save_button button-primary" value="<?php esc_html_e( 'Save Changes' ); ?>" />
				</p>
			</div>

			<?php settings_fields( 'plugin_geo2_maps' ); ?>
			<?php $options = geo2_maps_convert_to_int( get_option( 'plugin_geo2_maps_options' ) ); ?>

			<div id="geo2_general" class="postbox geo2_tab_content">
				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Map Service Provider', 'ngg-geo2-maps' ); ?></p>
				</h3>
				<div class="inside">
					<h3>&ensp;&ensp;&ensp;&ensp;Bing Maps Service -
						<?php
						// Checks if plugin's Bing Maps service is activated.
						if ( $options['geo_bing_auth_status'] === 0 ) {
							echo '<span class="geo2_key_not_activated">' . esc_html__( 'NOT ACTIVATED', 'ngg-geo2-maps' ) . '</span></h3><br />
								<span class="description">';
							printf(
								/* translators: 1: HTML link opening tag. 2: HTML link closing tag. 3: HTML link opening tag. 4: HTML link closing tag. */
								esc_html__( 'Get the Bing Maps Key by following the instruction in %1$shere%2$s or go directly to: %3$sBing Maps Dev Center%4$s.', 'ngg-geo2-maps' ),
								'<a href="https://msdn.microsoft.com/en-us/library/ff428642.aspx title="Getting a Bing Maps Key" target="_blank">',
								'</a> ',
								'<a href="https://www.bingmapsportal.com/" title="Bing Maps Dev Center" target="_blank">',
								'</a>.</span>'
							);
						} else {
							echo '<span class="geo2_key_activated">' . esc_html__( 'ACTIVATED', 'ngg-geo2-maps' ) . '</h3></span>';
						}
						?>
						<h4><?php esc_html_e( 'Bing Maps API Key', 'ngg-geo2-maps' ); ?></h4>

						<input type="text" name="plugin_geo2_maps_options[geo_bing_key]" value="<?php echo esc_textarea( $options['geo_bing_key'] ); ?>" style='min-width:25em' size='<?php echo esc_textarea( ( strlen( $options['geo_bing_key'] ) + 16 ) ); ?>' /><br />
						<p><span class="description">
								<?php
								printf(
									/* translators: 1: HTML link opening tag. 2: HTML link closing tag. 3: HTML link opening tag. 4: HTML link closing tag. 5: HTML link opening tag. 6: HTML link closing tag. */
									esc_html__( 'Bing Maps API platform is free to use with some limitations. For more information refer to: %1$sTerms Of Use%2$s, %3$sFAQ%4$s, %5$sLicensing Options chart%6$s.', 'ngg-geo2-maps' ),
									'<a href="https://www.microsoft.com/en-us/maps/product" title="Bing Maps API Terms of Use" target="_blank">',
									'</a>',
									'<a href="https://www.microsoft.com/en-us/maps/faq" title="Bing Maps API PAQ" target="_blank">',
									'</a>',
									'<a href="https://www.microsoft.com/en-us/maps/licensing/licensing-options" title="Bing API Licensing Chart" target="_blank">',
									'</a>'
								);
								?>
							</span></p>
				</div>

				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Thumbnails or Pushpins', 'ngg-geo2-maps' ); ?></p>
				</h3>
				<div class="inside">
					<p><input type="radio" name="plugin_geo2_maps_options[thumb]" value="1" <?php checked( $options['thumb'], '1', 1 ); ?>> <?php esc_html_e( 'Canvas image thumbnails', 'ngg-geo2-maps' ); ?></p>
					<p><input type="radio" name="plugin_geo2_maps_options[thumb]" value="2" <?php checked( $options['thumb'], '2', 1 ); ?>> <?php esc_html_e( 'Pushpins', 'ngg-geo2-maps' ); ?></p>
					<span class="description"><?php esc_html_e( 'You can show rectangular or round thumbnail images by choosing a desired option on the Thumbnails tab.', 'ngg-geo2-maps' ); ?></span></p>
					<p><b><code>Shortcode: [geo2 thumb=1 (canvas) / 2 (pushpins) ]</code></b></p>
					<br />

					<h3>
						<p><?php esc_html_e( 'Thumbnails and Pushpins common options', 'ngg-geo2-maps' ); ?> </p>
					</h3>
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[thumb_title]" value="1" <?php checked( $options['thumb_title'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show image or gallery title below thumbs or pushpins', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 thumb_title=1 / 0]</code></b></p>
					<span class="description"><?php esc_html_e( 'The style of this text can not be changed.', 'ngg-geo2-maps' ); ?></span><br />
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[show_wp_caption]" value="1" <?php checked( $options['show_wp_caption'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show thumbnail Caption (subtitle) below the Title', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 show_wp_caption=1 / 0]</code></b></p>
					<span class="description"><?php esc_html_e( 'This option shows the Caption text from the WP Media Library for the corresponding image to the NextGEN image if it exists there. Requires the Title label to be set and enabled above.', 'ngg-geo2-maps' ); ?></span><br /><br />
				</div>


				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Auto Mode', 'ngg-geo2-maps' ); ?></p>
				</h3>

				<div class="inside">
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[auto_mode]" value="1" <?php checked( $options['auto_mode'], 1 ); ?>>&ensp;<?php esc_html_e( 'Insert maps automatically when a gallery or an album is used', 'ngg-geo2-maps' ); ?></b></p>
					<h4><?php esc_html_e( 'Auto Mode option creates a map automatically based on the content of the NextGEN Albums or Galleries placed on a page without a need to use a shortcode.', 'ngg-geo2-maps' ); ?></h4>
					<?php esc_html_e( 'Select a desired option below to create a map for:', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[auto_include]" value="albums" <?php checked( $options['auto_include'], 'albums', 1 ); ?>>&ensp;<?php esc_html_e( 'albums contained in albums', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[auto_include]" value="galleries" <?php checked( $options['auto_include'], 'galleries', 1 ); ?>>&ensp;<?php esc_html_e( 'galleries contained in albums', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[auto_include]" value="all_albums" <?php checked( $options['auto_include'], 'all_albums', 1 ); ?>>&ensp;<?php esc_html_e( 'albums and galleries contained in albums', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[auto_include]" value="images" <?php checked( $options['auto_include'], 'images', 1 ); ?>>&ensp;<?php esc_html_e( 'images contained in galleries', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[auto_include]" value="all_auto" <?php checked( $options['auto_include'], 'all_auto', 1 ); ?>>&ensp;<?php esc_html_e( 'albums and galleries contained in albums and if not found for images contained in galleries', 'ngg-geo2-maps' ); ?><br /><br />
					<span class="description"><?php esc_html_e( 'For the first 3 options, if there are no albums, any found galleries will be placed on a map.', 'ngg-geo2-maps' ); ?></span><br />

					<h4><?php esc_html_e( 'Automatic map placement location on a page', 'ngg-geo2-maps' ); ?></h4>
					<input type="radio" name="plugin_geo2_maps_options[top_bottom]" value="0" <?php checked( 0, $options['top_bottom'], 1 ); ?>>
					<?php esc_html_e( 'Top of the page', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[top_bottom]" value="1" <?php checked( 1, $options['top_bottom'], 1 ); ?>>
					<?php esc_html_e( 'Bottom of the page', 'ngg-geo2-maps' ); ?><br /><br />
				</div>

				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Worldmap', 'ngg-geo2-maps' ); ?> </p>
				</h3>
				<div class="inside">
					<p><b><?php esc_html_e( 'Worldmap option creates a map with all NextGEN albums and/or galleries.', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 worldmap=1]</code></b></p>

					<?php esc_html_e( 'Select a desired option below to include:', 'ngg-geo2-maps' ); ?><br />

					<input type="radio" name="plugin_geo2_maps_options[include]" value="albums" <?php checked( $options['include'], 'albums', 1 ); ?>>&ensp;<?php esc_html_e( 'all albums', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[include]" value="galleries" <?php checked( $options['include'], 'galleries', 1 ); ?>>&ensp;<?php esc_html_e( 'all galleries', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[include]" value="all" <?php checked( $options['include'], 'all', 1 ); ?>>&ensp;<?php esc_html_e( 'all albums and galleries', 'ngg-geo2-maps' ); ?><br /><br />
					<p><b><code>Shortcode: [geo2 worldmap=1 include=albums / galleries / all ]</code></b></p>
					<h4><input type="checkbox" name="plugin_geo2_maps_options[open_lightbox]" value="1" <?php checked( $options['open_lightbox'], 1 ); ?>>&ensp;<?php esc_html_e( 'Enable Infobox/Lightbox for galleries and Infobox for albums', 'ngg-geo2-maps' ); ?></h4>
					<p><b><code>Shortcode: [geo2 worldmap=1 open_lightbox=1 / 0]</code></b></p>
					<span class="description"><?php esc_html_e( 'When this option is enabled the whole content of the gallery will be downloaded (using Ajax) and viewed in the selected Lightbox. The default behaviour will link to a page set up in the NextGEN Gallery options or if the gallery is placed on the same page will load/open this gallery.', 'ngg-geo2-maps' ); ?></span>
				</div>
				<br />

				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Load map on demand', 'ngg-geo2-maps' ); ?> </p>
				</h3>
				<div class="inside">
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[load_ajax]" value="1" <?php checked( $options['load_ajax'], 1 ); ?>>&ensp;<?php esc_html_e( 'Enable Ajax shortcode', 'ngg-geo2-maps' ); ?></b></p>

					<span class="description"><?php esc_html_e( 'Loading maps may significantly increase a page opening time. This option allows to load them only on a visitor request.', 'ngg-geo2-maps' ); ?></span><br /><br />
					<span class="description"><?php esc_html_e( 'When the Ajax shortcode option above is enabled and the shortcode below is placed in a map shortcode, a map won\'t be initiated when a page opens. A placeholder will be created in the place of this map. Clicking on it will load and open it.', 'ngg-geo2-maps' ); ?></span>
					<p><b><code>Shortcode: [geo2 ajax=1]</code></b></p>
				</div>
				<br />

				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Gallery Map', 'ngg-geo2-maps' ); ?> </p>
				</h3>
				<div class="inside">
					<p><b><?php esc_html_e( 'Create a map with selected NextGEN galleries by specifying their ids.', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 id=1,6,12]</code></b></p>
				</div>

				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Image Map', 'ngg-geo2-maps' ); ?> </p>
				</h3>
				<div class="inside">
					<p><b><?php esc_html_e( 'Create a map with selected images by specifying image ids.', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 pid=2,4,15]</code></b></p>
				</div>

				<div class="geo2_defaults">
					<h3>
						<p><?php esc_html_e( 'Restore default settings', 'ngg-geo2-maps' ); ?> </p>
					</h3>
					<input type="checkbox" name="plugin_geo2_maps_options[restore_defaults]" value="1" <?php checked( $options['restore_defaults'], 1 ); ?>>&ensp;<?php esc_html_e( 'Enable only if you want to restore default settings on deactivation/activation of this plugin.', 'ngg-geo2-maps' ); ?>
				</div>
			</div>

			<div id="geocoding" class="postbox geo2_tab_content">
				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Geocoding Provider', 'ngg-geo2-maps' ); ?> </p>
				</h3>
				<div class="inside">

					<input type="radio" name="plugin_geo2_maps_options[geocoding_provider]" value="bing" <?php checked( $options['geocoding_provider'], 'bing', 1 ); ?>> Bing Maps -
					<?php
					// Checks if plugin's Bing Maps service is activated.
					if ( $options['geo_bing_auth_status'] === 0 ) {
						echo '<span class="geo2_key_not_activated">' . esc_html__( 'NOT ACTIVATED', 'ngg-geo2-maps' ) . '</span><br />';
					} else {
						echo '<span class="geo2_key_activated">' . esc_html__( 'ACTIVATED', 'ngg-geo2-maps' ) . '</span><br />';
					}
					?>
					<br />
					<input type="radio" name="plugin_geo2_maps_options[geocoding_provider]" value="mapquest" <?php checked( $options['geocoding_provider'], 'mapquest', 1 ); ?>> MapQuest -
					<?php
					// Checks if plugin's MapQuest service is activated.
					if ( $options['mapquest_auth_status'] === 0 ) {
						echo '<span class="geo2_key_not_activated">' . esc_html__( 'NOT ACTIVATED', 'ngg-geo2-maps' ) . '</span><br />';
					} else {
						echo '<span class="geo2_key_activated">' . esc_html__( 'ACTIVATED', 'ngg-geo2-maps' ) . '</span><br />';
					}
					?>
					<br />
					<input type="radio" name="plugin_geo2_maps_options[geocoding_provider]" value="openstreetmaps" <?php checked( $options['geocoding_provider'], 'openstreetmaps', 1 ); ?>> OpenStreetMap Nominatim<br /><br />

					<input type="radio" name="plugin_geo2_maps_options[geocoding_provider]" value="no" <?php checked( $options['geocoding_provider'], 'no', 1 ); ?>><?php esc_html_e( ' Disable geocoding', 'ngg-geo2-maps' ); ?> <br /><br />
					<span class="description"><?php esc_html_e( 'The geocoding function tries to find the location of a gallery using it\'s Title, if there are no coordinates available.', 'ngg-geo2-maps' ); ?></span>

					<h4><?php esc_html_e( 'MapQuest API Key', 'ngg-geo2-maps' ); ?></h4>
					<input type="text" name="plugin_geo2_maps_options[mapquest_key]" value="<?php echo esc_attr( $options['mapquest_key'] ); ?>" style='min-width:25em' size='<?php echo ( strlen( $options['mapquest_key'] ) + 10 ); ?>' /><br /><span class="description">
						<?php
						printf(
							/* translators: 1: HTML link opening tag. 2: HTML link closing tag. */
							esc_html__( 'Get the MapQuest API Key by following this %1$slink%2$s.', 'ngg-geo2-maps' ),
							'<a href="https://developer.mapquest.com" title="Map Quest API Key" target="_blank">',
							'</a>'
						);
						?>
					</span>
					<h4><?php esc_html_e( 'E-mail for Open Street Maps Nominatim', 'ngg-geo2-maps' ); ?></h4>
					<input type="text" class="regular-text" name="plugin_geo2_maps_options[user_email]" value="<?php echo esc_attr( $options['user_email'] ); ?>" /><br /><span class="description">
						<?php
						printf(
							/* translators: 1: HTML link opening tag. 2: HTML link closing tag. 3: HTML link opening tag. 4: HTML link closing tag. */
							esc_html__( 'Please read %1$sNominatim Usage Policy%2$s before using this service. Geocoding of larger amounts of data is not encouraged (more then 1/s). It is required to provide a valid e-mail address in the request string to do so. E-mail will be kept confidential and only used to contact you in the event of a problem. More information can be found %3$shere%4$s.', 'ngg-geo2-maps' ),
							'<a href="https://operations.osmfoundation.org/policies/nominatim/" title="Nominatim Usage Policy" target="_blank">',
							'</a>',
							'<a href="https://wiki.openstreetmap.org/wiki/Nominatim" title="Nominatim Usage Policy" target="_blank">',
							'</a>'
						);

						?>
					</span><br />
					<h4><code>Shortcode: [geo2 geocoding_provider=bing / mapquest / openstreetmaps / no]</code></h4>
				</div>
			</div>


			<div id="maps" class="postbox geo2_tab_content">
				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Map Options', 'ngg-geo2-maps' ); ?></p>
				</h3>

				<div class="inside">
					<b><?php esc_html_e( 'Zoom Level', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 zoom=1-19]</code></b><br />
					<input type="text" class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[zoom]" value="<?php echo (int) $options['zoom']; ?>" /><br />
					<span class="description"><?php esc_html_e( 'Which Zoom Level should be used? (maps with several pins are focused automatically)', 'ngg-geo2-maps' ); ?></span><br /><br />
					<b><?php esc_html_e( 'Map Height', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 map_height=100px]</code></b><br />
					<input type="text" class="code geo2_margin_top" name="plugin_geo2_maps_options[map_height]" value="<?php echo esc_attr( $options['map_height'] ); ?>" /><br /><br />
					<b><?php esc_html_e( 'Map Width', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 map_width=200px]</code></b><br />
					<input type="text" class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[map_width]" value="<?php echo esc_attr( $options['map_width'] ); ?>" /><br />
					<span class="description"><?php esc_html_e( 'You can use something like "235px", "auto" or "78%". Number only "235" will be changed to "235px" automatically. "auto" does not work with the Map Height. <br /> Height and width of the maps can be changed directly by using CSS. The class is named <code>geo2_maps_map</code>', 'ngg-geo2-maps' ); ?></span><br />
					<h4><?php esc_html_e( 'Map Style', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 map=road / aerial / canvasLight / canvasDark / grayscale / ordnanceSurvey]</code></h4>
					<input type="radio" name="plugin_geo2_maps_options[map]" value="road" <?php checked( $options['map'], 'road', 1 ); ?>> <?php esc_html_e( 'Road', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[map]" value="aerial" <?php checked( $options['map'], 'aerial', 1 ); ?>> <?php esc_html_e( 'Aerial', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[map]" value="canvasLight" <?php checked( $options['map'], 'canvasLight', 1 ); ?>> <?php esc_html_e( 'Canvas Light', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[map]" value="canvasDark" <?php checked( $options['map'], 'canvasDark', 1 ); ?>> <?php esc_html_e( 'Canvas Dark', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[map]" value="grayscale" <?php checked( $options['map'], 'grayscale', 1 ); ?>> <?php esc_html_e( 'Grayscale', 'ngg-geo2-maps' ); ?><br />
					<input type="radio" name="plugin_geo2_maps_options[map]" value="ordnanceSurvey" <?php checked( $options['map'], 'ordnanceSurvey', 1 ); ?>> <?php esc_html_e( 'Ordnance Survey', 'ngg-geo2-maps' ); ?><br /><br />
					<h4><input type="checkbox" name="plugin_geo2_maps_options[bev]" value="1" <?php checked( $options['bev'], 1 ); ?>>&ensp;<?php esc_html_e( "Use Bird's-eye view", 'ngg-geo2-maps' ); ?>
						<code style="margin-left:30px;">Shortcode: [geo2 bev=1 / 0]</code>
					</h4><span class="description"><?php esc_html_e( "Should the Bird's-eye view be used, if available? (a Bing maps feature)", 'ngg-geo2-maps' ); ?></span><br /><br />
					<h4><?php esc_html_e( 'Which elements should be displayed?', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 dashboard=1 / 0 locate_me_button=1 / 0 scalebar=1 / 0 copyright=1 / 0 terms_link=1 / 0 logo=1 / 0 minimap=1 / 0]</code></h4>
					<input type="checkbox" name="plugin_geo2_maps_options[dashboard]" value="1" <?php checked( $options['dashboard'], 1 ); ?>>&ensp;<?php esc_html_e( 'Dashboard with map navigation controls', 'ngg-geo2-maps' ); ?><br />
					<input type="checkbox" name="plugin_geo2_maps_options[locate_me_button]" value="1" <?php checked( $options['locate_me_button'], 1 ); ?>>&ensp;<?php esc_html_e( 'Locate Me button (dependent on Dashboard visibility)', 'ngg-geo2-maps' ); ?><br />
					<input type="checkbox" name="plugin_geo2_maps_options[scalebar]" value="1" <?php checked( $options['scalebar'], 1 ); ?>>&ensp;<?php esc_html_e( 'Scalebar', 'ngg-geo2-maps' ); ?><br />
					<input type="checkbox" name="plugin_geo2_maps_options[copyright]" value="1" <?php checked( $options['copyright'], 1 ); ?>>&ensp;<?php esc_html_e( 'Copyright text', 'ngg-geo2-maps' ); ?><br />
					<span style="margin-left:26px;" class="description">
						<?php
						printf(
							/* translators: 1: HTML link opening tag. 2: HTML link closing tag. */
							esc_html__( 'Officially undocumented option. Disabling may likely breach the %1$sBing Maps Platform API’s Terms of Use%2$s!', 'ngg-geo2-maps' ),
							'<a href="https://www.microsoft.com/maps/product/terms.html" title="Terms of Use" target="_blank">',
							'</a>'
						);
						?>
					</span><br />
					<input type="checkbox" name="plugin_geo2_maps_options[terms_link]" value="1" <?php checked( $options['terms_link'], 1 ); ?>>&ensp;<?php esc_html_e( 'Terms link next to the Copyright text (dependent on Copyright text visibility)', 'ngg-geo2-maps' ); ?><br />
					<span style="margin-left:26px;" class="description">
						<?php
						printf(
							/* translators: 1: HTML link opening tag. 2: HTML link closing tag. */
							esc_html__( ' The %1$sBing Maps Platform API’s Terms of Use%2$s state that a hypertext link to the Bing Maps TOU must be provided in specific situations.', 'ngg-geo2-maps' ),
							'<a href="https://www.microsoft.com/maps/product/terms.html" title="Terms of Use" target="_blank">',
							'</a>'
						);
						?>
					</span><br />
					<input type="checkbox" name="plugin_geo2_maps_options[logo]" value="1" <?php checked( $options['logo'], 1 ); ?>>&ensp;<?php esc_html_e( 'Bing logo', 'ngg-geo2-maps' ); ?><br />
					<span style="margin-left:26px;" class="description">
						<?php
						printf(
							/* translators: 1: HTML link opening tag. 2: HTML link closing tag. */
							esc_html__( 'Officially undocumented option. Disabling may likely breach the %1$sBing Maps Platform API’s Terms of Use%2$s!', 'ngg-geo2-maps' ),
							'<a href="https://www.microsoft.com/maps/product/terms.html" title="Terms of Use" target="_blank">',
							'</a>'
						);
						?>
					</span><br />
					<input type="checkbox" onclick="geo2_maps_openCheckBox( event, 'minimap' )" name="plugin_geo2_maps_options[minimap]" value="1" <?php checked( $options['minimap'], 1 ); ?>>&ensp;<?php esc_html_e( 'Mini Map', 'ngg-geo2-maps' ); ?><br />
					<span style="margin-left:26px;" class="description"><?php esc_html_e( 'Creates a small map in a corner with a smaller zoom. Enable to see Mini Map options.', 'ngg-geo2-maps' ); ?></span><br />
					<div id="minimap">
						<h4><?php esc_html_e( 'Mini Map Style', 'ngg-geo2-maps' ); ?> <code style="margin-left:30px;">Shortcode: [geo2 minimap_type=same / road / aerial / canvasLight / canvasDark / grayscale / ordnanceSurvey]</code></h4>
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="same" <?php checked( $options['minimap_type'], 'same', 1 ); ?>> <?php esc_html_e( 'Style of the main map', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="road" <?php checked( $options['minimap_type'], 'road', 1 ); ?>> <?php esc_html_e( 'Road', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="aerial" <?php checked( $options['minimap_type'], 'aerial', 1 ); ?>> <?php esc_html_e( 'Aerial', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="canvasLight" <?php checked( $options['minimap_type'], 'canvasLight', 1 ); ?>> <?php esc_html_e( 'Canvas Light', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="canvasDark" <?php checked( $options['minimap_type'], 'canvasDark', 1 ); ?>> <?php esc_html_e( 'Canvas Dark', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="grayscale" <?php checked( $options['minimap_type'], 'grayscale', 1 ); ?>> <?php esc_html_e( 'Grayscale', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[minimap_type]" value="ordnanceSurvey" <?php checked( $options['minimap_type'], 'ordnanceSurvey', 1 ); ?>> <?php esc_html_e( 'Ordnance Survey', 'ngg-geo2-maps' ); ?><br /><br />
						<input type="checkbox" name="plugin_geo2_maps_options[minimap_show_at_start]" value="1" <?php checked( $options['minimap_show_at_start'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show open at start', 'ngg-geo2-maps' ); ?>
						<code style="margin-left:30px;">Shortcode: [geo2 minimap_show_at_start=0 / 1]</code><br />
						<h4><?php esc_html_e( 'Mini Map size and offset', 'ngg-geo2-maps' ); ?></h4>
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[minimap_height]" value="<?php echo (int) $options['minimap_height']; ?>" /> <?php esc_html_e( 'Hight', 'ngg-geo2-maps' ); ?>
						<code style="margin-left:30px;">Shortcode: [geo2 minimap_height=150]</code><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[minimap_width]" value="<?php echo (int) $options['minimap_width']; ?>" /> <?php esc_html_e( 'Width', 'ngg-geo2-maps' ); ?>
						<code style="margin-left:30px;">Shortcode: [geo2 minimap_width=150]</code><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[minimap_top_offset]" value="<?php echo floatval( $options['minimap_top_offset'] ); ?>" /> <?php esc_html_e( 'Top offset', 'ngg-geo2-maps' ); ?>
						<code style="margin-left:30px;">Shortcode: [geo2 minimap_top_offset=0]</code><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[minimap_side_offset]" value="<?php echo floatval( $options['minimap_side_offset'] ); ?>" /> <?php esc_html_e( 'Left side offset', 'ngg-geo2-maps' ); ?>
						<code style="margin-left:30px;">Shortcode: [geo2 minimap_side_offset=0]</code><br /><br />
					</div>
				</div>
			</div>


			<div id="thumbnails" class="postbox geo2_tab_content">

				<div class="inside">

					<h3>
						<p><?php esc_html_e( 'Thumbnail options', 'ngg-geo2-maps' ); ?></p>
					</h3>

					<p><input type="radio" name="plugin_geo2_maps_options[thumb_shape]" value="rect" <?php checked( $options['thumb_shape'], 'rect', 1 ); ?>> <?php esc_html_e( 'Rectangular thumbnails', 'ngg-geo2-maps' ); ?></p>
					<p><input type="radio" name="plugin_geo2_maps_options[thumb_shape]" value="round" <?php checked( $options['thumb_shape'], 'round', 1 ); ?>> <?php esc_html_e( 'Round thumbnails', 'ngg-geo2-maps' ); ?></p>
					<br />

					<b><?php esc_html_e( 'Thumbnail height', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 thumb_height=40]</code></b><br />
					<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[thumb_height]" value="<?php echo (int) $options['thumb_height']; ?>" /><br />
					<br />
					<b><?php esc_html_e( 'Thumbnail width', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 thumb_width=40]</code></b><br />
					<input type="text" size='5' class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[thumb_width]" value="<?php echo (int) $options['thumb_width']; ?>" /><br />
					<span class="description"><?php esc_html_e( 'Thumbnails will retain proportions of thumbnails in NextGEN Gallery. Above dimensions are maximum dimensions in px.', 'ngg-geo2-maps' ); ?></span><br />
					<br />
					<b><?php esc_html_e( 'Thumbnail radius', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 thumb_radius=20]</code></b><br />
					<input type="text" size='5' class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[thumb_radius]" value="<?php echo floatval( $options['thumb_radius'] ); ?>" /><br />
					<span class="description"><?php esc_html_e( 'Too big corner radius will be automatically reduced to half of the thumb size.', 'ngg-geo2-maps' ); ?></span><br />
					<br />
					<b><?php esc_html_e( 'Thumbnail border (frame) width', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 thumb_border=4]</code></b><br />
					<input type="text" size='5' class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[thumb_border]" value="<?php echo floatval( $options['thumb_border'] ); ?>" /><br />
					<span class="description"><?php esc_html_e( 'Too big thickness will be automatically reduced to 1/4 of the thumb size. For 0 border will not be created.', 'ngg-geo2-maps' ); ?></span><br />
					<br />
					<b><?php esc_html_e( 'Thumbnail border color', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 thumb_border_color="rgba(255,255,255,1)"]</code></b>
					<div class="geo2_margin_top">
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(255,255,255,1)" name="plugin_geo2_maps_options[thumb_border_color]" value="<?php echo esc_attr( $options['thumb_border_color'] ); ?>" />
					</div>
				</div>
			</div>


			<div id="pushpins" class="postbox geo2_tab_content">
				<div class="inside">
					<h3>
						<p><?php esc_html_e( 'Pushpin Color for Images', 'ngg-geo2-maps' ); ?></p>
					</h3>
					<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(0,255,0,1)" name="plugin_geo2_maps_options[pin_color]" value="<?php echo esc_attr( $options['pin_color'] ); ?>" />
					<p><b><code>Shortcode: [geo2 pin_color="rgba(255,0,0,1)"]</code></b></p><br />
					<h3>
						<p><?php esc_html_e( 'Pushpin Color for Galleries', 'ngg-geo2-maps' ); ?></p>
					</h3>
					<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(255,0,0,1)" name="plugin_geo2_maps_options[pin_gal_color]" value="<?php echo esc_attr( $options['pin_gal_color'] ); ?>" />
					<p><b><code>Shortcode: [geo2 pin_gal_color="rgba(255,0,0,1)"]</code></b></p><br />
					<h3>
						<p><?php esc_html_e( 'Pushpin Color for Albums', 'ngg-geo2-maps' ); ?></p>
					</h3>
					<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(255,0,0,1)" name="plugin_geo2_maps_options[pin_alb_color]" value="<?php echo esc_attr( $options['pin_alb_color'] ); ?>" />
					<p><b><code>Shortcode: [geo2 pin_alb_color="rgba(255,0,0,1)"]</code></b></p>
				</div>
			</div>


			<div id="infobox-lightbox" class="postbox geo2_tab_content">
				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'General Infobox/Lightbox options', 'ngg-geo2-maps' ); ?></p>
				</h3>
				<div class="inside">
					<p><b><?php esc_html_e( 'Infobox / Lightbox Type', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 lightbox=fancybox / fancybox3 / slimbox2 / infobox / no]</code></b></p>
					<span class="description"><?php esc_html_e( 'You can choose between three different Lightbox plugins. These plugins are already included. Fancybox (used by NextGEN) and Slimbox 2 are free for personal and commercial usage. Fancybox 3 is only free for personal use.', 'ngg-geo2-maps' ); ?></span><br />
					<div class="geo2_margin_top">
						<input type="radio" onclick="geo2_maps_openBox( event, 'infobox' )" name="plugin_geo2_maps_options[lightbox]" value="infobox" <?php checked( $options['lightbox'], 'infobox', 1 ); ?>> <?php esc_html_e( 'Infobox', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" onclick="geo2_maps_openBox( event, 'fancybox' )" name="plugin_geo2_maps_options[lightbox]" value="fancybox" <?php checked( $options['lightbox'], 'fancybox', 1 ); ?>> Fancybox <span class="description">
							<?php
							printf(
								/* translators: 1: HTML underline opening tag 2: HTML underline closing tag */
								esc_html__( 'This is ver. 1.3.4 used in NextGEN Gallery but with expanded functionality. Set also Fancybox as a main Lightbox in %1$sNextGEN Gallery > Other Options%2$s.', 'ngg-geo2-maps-plus' ),
								'<u>',
								'</u>'
							);
							?>
						</span><br />
						<input type="radio" onclick="geo2_maps_openBox( event, 'fancybox3' )" name="plugin_geo2_maps_options[lightbox]" value="fancybox3" <?php checked( $options['lightbox'], 'fancybox3', 1 ); ?>> Fancybox 3<br />
						<input type="radio" onclick="geo2_maps_openBox( event, 'slimbox2' )" name="plugin_geo2_maps_options[lightbox]" value="slimbox2" <?php checked( $options['lightbox'], 'slimbox2', 1 ); ?>> Slimbox 2<br />
						<input type="radio" onclick="geo2_maps_closeBoxes()" name="plugin_geo2_maps_options[lightbox]" value="no" <?php checked( $options['lightbox'], 'no', 1 ); ?>> <?php esc_html_e( 'no Lightbox plugin, no Infobox', 'ngg-geo2-maps' ); ?><br />
					</div>

					<h4><?php esc_html_e( 'General Options', 'ngg-geo2-maps' ); ?></h4>
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[gallery_title]" value="1" <?php checked( $options['gallery_title'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show gallery title and description', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 gallery_title=1 / 0]</code></b></p>
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[exif]" value="1" <?php checked( $options['exif'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show EXIF information: Created Date, Camera, Aperture, Focal length, ISO, Shutter Speed', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 exif=1 / 0]</code></b></p>
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[gps]" value="1" <?php checked( $options['gps'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show GPS coordinates', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 gps=1 / 0]</code></b></p>
					<br />
				</div>


				<div id="infobox" class="box_to_close">
					<h3>
						<p>&ensp;&ensp;<?php esc_html_e( 'Infobox Options', 'ngg-geo2-maps' ); ?></p>
					</h3>
					<div class="inside">

						<p><b><?php esc_html_e( 'Graphical Options', 'ngg-geo2-maps' ); ?></b></p>

						<p><input type="checkbox" name="plugin_geo2_maps_options[infobox_title_over]" value="1" <?php checked( $options['infobox_title_over'], 1 ); ?>>&ensp;<?php esc_html_e( 'Overlap image title and description on top of Infobox thumbnail', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 infobox_title_over=1 / 0]</code></b></p>
						<span class="description"><?php esc_html_e( 'This option will place gallery title, gallery description, image title and image description on top of the Infobox thumbnail image.', 'ngg-geo2-maps' ); ?></span><br />
						<br />
						<br />

						<?php esc_html_e( 'Infobox size override:', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 infobox_width=200 infobox_height=200]</code></b>
						<div class="geo2_margin_top geo2_margin_bottom">
							<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[infobox_width]" value="<?php echo (int) $options['infobox_width']; ?>" />&nbsp;<?php esc_html_e( 'Infobox width', 'ngg-geo2-maps' ); ?><br />
							<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[infobox_height]" value="<?php echo (int) $options['infobox_height']; ?>" />&nbsp;<?php esc_html_e( 'Infobox height', 'ngg-geo2-maps' ); ?><br />
						</div>
						<span class="description"><?php esc_html_e( 'Infobox automatically adjusts its orientation. For portrait images, a description is shown on the right side of the image. For landscape or square images, a description is shown below the image. Placing any dimensions will override default Infobox behaviour. Leave empty to restore automatization.', 'ngg-geo2-maps' ); ?></span><br />
						<br />
						<br />
						<?php esc_html_e( 'Infobox background color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 infobox_color="rgba(0,0,0,0.7)"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(0,0,0,0.7)" name="plugin_geo2_maps_options[infobox_color]" value="<?php echo esc_attr( $options['infobox_color'] ); ?>" /><br />
						<?php esc_html_e( 'Infobox text color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 infobox_text_color="#fff"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="#fff" name="plugin_geo2_maps_options[infobox_text_color]" value="<?php echo esc_attr( $options['infobox_text_color'] ); ?>" />
					</div>
				</div>

				<div id="fancybox3" class="box_to_close">
					<h3>
						<p>&ensp;&ensp;<?php esc_html_e( 'Fancybox 3 Options', 'ngg-geo2-maps' ); ?> </p>
					</h3>

					<div class="inside">

						<p><b><?php esc_html_e( 'Layout Options', 'ngg-geo2-maps' ); ?></b></p>

						<p><?php esc_html_e( 'Caption panel location and content', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_caption="no" / "bottom"]</code></b></p>
						<input type="radio" name="plugin_geo2_maps_options[fancybox3_caption]" value="no" <?php checked( $options['fancybox3_caption'], 'no', 1 ); ?>>&ensp;<?php esc_html_e( 'No caption', 'ngg-geo2-maps' ); ?><br />
						<input type="radio" name="plugin_geo2_maps_options[fancybox3_caption]" value="bottom" <?php checked( $options['fancybox3_caption'], 'bottom', 1 ); ?>>&ensp;<?php esc_html_e( 'Bottom caption panel', 'ngg-geo2-maps' ); ?><br />
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_prevent_caption_overlap]" value="1" <?php checked( $options['fancybox3_prevent_caption_overlap'], 1 ); ?>>&ensp;<?php esc_html_e( 'Prevent bottom caption to overlap the content', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_prevent_caption_overlap=1 / 0]</code></b></p>

						<h4><?php esc_html_e( 'Control Options', 'ngg-geo2-maps' ); ?></h4>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_thumbs_autostart]" value="1" <?php checked( $options['fancybox3_thumbs_autostart'], 1 ); ?>>&ensp;<b><?php esc_html_e( 'Display thumbnails preview on opening', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_thumbs_autostart=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_loop]" value="1" <?php checked( $options['fancybox3_loop'], 1 ); ?>>&ensp;<b><?php esc_html_e( 'Enable infinite gallery navigation', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_loop=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_fullscreen_autostart]" value="1" <?php checked( $options['fancybox3_fullscreen_autostart'], 1 ); ?>>&ensp;<b><?php esc_html_e( 'Autostart in fullscreen on opening', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_fullscreen_autostart=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_slideshow_autostart]" value="1" <?php checked( $options['fancybox3_slideshow_autostart'], 1 ); ?>>&ensp;<b><?php esc_html_e( 'Autostart slideshow on opening', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_slideshow_autostart=1 / 0]</code></b></p>
						<?php esc_html_e( 'Slideshow Speed in ms (1000ms = 1s)', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_slideshow_speed=3000]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[fancybox3_slideshow_speed]" value="<?php echo (int) $options['fancybox3_slideshow_speed']; ?>" /><br />

						<h4><?php esc_html_e( 'Buttons Display options', 'ngg-geo2-maps' ); ?></h4>

						<?php esc_html_e( 'Display options for "Close" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_close_btn="auto" / true / false]</code></b><br />
						<select class="geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[fancybox3_close_btn]">
							<option value="auto" <?php echo ( $options['fancybox3_close_btn'] === 'auto' ) ? 'selected' : ''; ?>>Enabled for "html", "inline" or "ajax" items</option>
							<option value="true" <?php echo ( $options['fancybox3_close_btn'] === 'true' ) ? 'selected' : ''; ?>>Display</option>
							<option value="false" <?php echo ( $options['fancybox3_close_btn'] === 'false' ) ? 'selected' : ''; ?>>Do not display</option>
						</select><br />
						<br />
						<?php esc_html_e( 'Display options for toolbar with buttons at the top', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_toolbar="auto" / true / false]</code></b><br />
						<select class="geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[fancybox3_toolbar]">
							<option value="auto" <?php echo ( $options['fancybox3_toolbar'] === 'auto' ) ? 'selected' : ''; ?>>Hidden for "html", "inline" or "ajax" items</option>
							<option value="true" <?php echo ( $options['fancybox3_toolbar'] === 'true' ) ? 'selected' : ''; ?>>Display</option>
							<option value="false" <?php echo ( $options['fancybox3_toolbar'] === 'false' ) ? 'selected' : ''; ?>>Do not display</option>
						</select><br />

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_arrows]" value="1" <?php checked( $options['fancybox3_arrows'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display navigation arrows', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_arrows=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_infobar]" value="1" <?php checked( $options['fancybox3_infobar'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display counter at the top left corner', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_infobar=1 / 0]</code></b></p>
						<h4><?php esc_html_e( 'Toolbar buttons:', 'ngg-geo2-maps' ); ?></h4>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_zoom]" value="1" <?php checked( $options['fancybox3_buttons_zoom'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Zoom" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_zoom=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_slideshow]" value="1" <?php checked( $options['fancybox3_buttons_slideshow'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Slideshow" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_slideshow=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_fullScreen]" value="1" <?php checked( $options['fancybox3_buttons_fullScreen'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Fullscreen" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_fullScreen=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_download]" value="1" <?php checked( $options['fancybox3_buttons_download'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Download" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_download=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_thumbs]" value="1" <?php checked( $options['fancybox3_buttons_thumbs'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Thumbs preview" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_thumbs=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_close]" value="1" <?php checked( $options['fancybox3_buttons_close'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Close" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_close=1 / 0]</code></b></p>
						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_buttons_share]" value="1" <?php checked( $options['fancybox3_buttons_share'], 1 ); ?>>&ensp;<?php esc_html_e( 'Display "Share" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_share=1 / 0]</code></b></p>
						<span class="description"><?php esc_html_e( 'If no toolbar button is checked above, default buttons: "zoom", "slideshow", "thumbs" and "close" will be visible.', 'ngg-geo2-maps' ); ?></span>

						<h4><?php esc_html_e( 'Color Options', 'ngg-geo2-maps' ); ?></h4>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox3_colors_override]" value="1" <?php checked( $options['fancybox3_colors_override'], 1 ); ?>>&ensp;<?php esc_html_e( 'Override Fancybox 3 colors with the values below', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_colors_override=1 / 0]</code></b></p><br />
						<?php esc_html_e( 'Background color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_background="rgba(30,30,30,0.9)"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(30,30,30,0.9)" name="plugin_geo2_maps_options[fancybox3_background]" value="<?php echo esc_attr( $options['fancybox3_background'] ); ?>" /><br />
						<?php esc_html_e( 'Caption text color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_caption_text_color="#eee"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="#eee" name="plugin_geo2_maps_options[fancybox3_caption_text_color]" value="<?php echo esc_attr( $options['fancybox3_caption_text_color'] ); ?>" /><br />
						<?php esc_html_e( 'Thumbs preview background color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_thumbs_background="rgba(0,0,0,0.3)"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(0,0,0,0.3)" name="plugin_geo2_maps_options[fancybox3_thumbs_background]" value="<?php echo esc_attr( $options['fancybox3_thumbs_background'] ); ?>" /><br />
						<?php esc_html_e( 'Buttons background color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_background="rgba(30,30,30,0.6)"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(30,30,30,0.6)" name="plugin_geo2_maps_options[fancybox3_buttons_background]" value="<?php echo esc_attr( $options['fancybox3_buttons_background'] ); ?>" /><br />
						<?php esc_html_e( 'Buttons sign color', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_color="#cccccc"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="#cccccc" name="plugin_geo2_maps_options[fancybox3_buttons_color]" value="<?php echo esc_attr( $options['fancybox3_buttons_color'] ); ?>" /><br />
						<?php esc_html_e( 'Buttons sign color on hover', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_buttons_color_hover="#ffffff"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="#ffffff" name="plugin_geo2_maps_options[fancybox3_buttons_color_hover]" value="<?php echo esc_attr( $options['fancybox3_buttons_color_hover'] ); ?>" /><br />
						<?php esc_html_e( 'Current image thumb\'s preview border colors', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox3_thumbs_active_border_color="#52bfff"]</code></b><br />
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="#52bfff" name="plugin_geo2_maps_options[fancybox3_thumbs_active_border_color]" value="<?php echo esc_attr( $options['fancybox3_thumbs_active_border_color'] ); ?>" />

						<p><b><?php esc_html_e( 'How to enable Fancybox 3 for Justified Image Grid plugin.', 'ngg-geo2-maps' ); ?></b></p>
						<span class="description">
							<?php
							echo nl2br(
								esc_html__(
									'1. In JIG settings select "Custom" option for "Lightbox Type" on "Lightboxes" tab.
									2. Set "Custom attribute name" to "data-fancybox3".
									3. Set "Custom attribute value" to "gallery[*instance*]".
									&ensp;&ensp;(omit quotation marks when entering values.)',
									'ngg-geo2-maps'
								)
							);
							?>
						</span><br />
					</div>
				</div>

				<div id="fancybox" class="box_to_close">

					<h3>
						<p>&ensp;&ensp;<?php esc_html_e( 'Fancybox Options', 'ngg-geo2-maps' ); ?></p>
					</h3>
					<div class="inside">

						<p><b><?php esc_html_e( 'Control Options', 'ngg-geo2-maps' ); ?></b></p>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox_auto_scale]" value="1" <?php checked( $options['fancybox_auto_scale'], 1 ); ?>>&ensp;<?php esc_html_e( 'Scale image box to fit in viewport', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_auto_scale=1 / 0]</code></b></p>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox_cyclic]" value="1" <?php checked( $options['fancybox_cyclic'], 1 ); ?>>&ensp;<?php esc_html_e( 'Enable infinite gallery navigation (User can switch between the last and the first image)', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_cyclic=1 / 0]</code></b></p>

						<h4><?php esc_html_e( 'Graphical Options', 'ngg-geo2-maps' ); ?></h4>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox_show_close_button]" value="1" <?php checked( $options['fancybox_show_close_button'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show "Close" button', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_show_close_button=1 / 0]</code></b></p>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox_show_nav_arrows]" value="1" <?php checked( $options['fancybox_show_nav_arrows'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show navigation arrows', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_show_nav_arrows=1 / 0]</code></b></p>

						<p><input type="checkbox" name="plugin_geo2_maps_options[fancybox_title_show]" value="1" <?php checked( $options['fancybox_title_show'], 1 ); ?>>&ensp;<?php esc_html_e( 'Show image title', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_title_show=1 / 0]</code></b></p>
						<br />

						<?php esc_html_e( 'Title position', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_title_position="over" / "outside" / "inside"]</code></b><br />
						<select class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[fancybox_title_position]">
							<option value="over" <?php echo ( $options['fancybox_title_position'] === 'over' ) ? 'selected' : ''; ?>>Over</option>
							<option value="outside" <?php echo ( $options['fancybox_title_position'] === 'outside' ) ? 'selected' : ''; ?>>Outside</option>
							<option value="inside" <?php echo ( $options['fancybox_title_position'] === 'inside' ) ? 'selected' : ''; ?>>Inside</option>
						</select><br />
						<br />
						<?php esc_html_e( 'Space between Fancybox wrapper and content in px', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_padding=10]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[fancybox_padding]" value="<?php echo floatval( $options['fancybox_padding'] ); ?>" /><br />
						<br />
						<?php esc_html_e( 'Space between viewport and Fancybox wrapper in px', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_margin=20]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[fancybox_margin]" value="<?php echo floatval( $options['fancybox_margin'] ); ?>" /><br />
						<br />
						<?php esc_html_e( 'Opacity of background overlay from 0 (opaque) to 1 (transparent)', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_overlay_opacity=20]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[fancybox_overlay_opacity]" value="<?php echo floatval( $options['fancybox_overlay_opacity'] ); ?>" /><br />
						<br />

						<?php esc_html_e( 'Color of the overlay', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 fancybox_overlay_color="#666"]</code></b><br />
						<input type="text" class="color-picker code" data-default-color="#666" name="plugin_geo2_maps_options[fancybox_overlay_color]" value="<?php echo esc_attr( $options['fancybox_overlay_color'] ); ?>" />
					</div>
				</div>

				<div id="slimbox2" class="box_to_close">

					<h3>
						<p>&ensp;&ensp;<?php esc_html_e( 'Slimbox 2 Options', 'ngg-geo2-maps' ); ?></p>
					</h3>
					<div class="inside">
						<p><b><?php esc_html_e( 'Control Options', 'ngg-geo2-maps' ); ?></b></p>

						<p><input type="checkbox" name="plugin_geo2_maps_options[slimbox2_loop]" value="1" <?php checked( $options['slimbox2_loop'], 1 ); ?>>&ensp;<?php esc_html_e( 'Enable infinite gallery navigation', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 slimbox2_loop=1 / 0]</code></b></p>

						<?php esc_html_e( 'Box scale factor to use when auto-resizing the image to fit in a browser (1 = full browser size, 0 = disable resizing)', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 slimbox2_scaler=0.8]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[slimbox2_scaler]" value="<?php echo floatval( $options['slimbox2_scaler'] ); ?>" /><br /><br />

						<?php esc_html_e( 'Opacity of background overlay from 0 (transparent) to 1 (opaque)', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 slimbox2_overlay_opacity=0.8]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[slimbox2_overlay_opacity]" value="<?php echo floatval( $options['slimbox2_overlay_opacity'] ); ?>" /><br /><br />

						<?php esc_html_e( 'Image counter style', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 slimbox2_counter_text="Image {x} of {y}" / "Photo {x} of {y}" / "{x}/{y}" / false]</code></b><br />
						<select class="code geo2_margin_top geo2_margin_bottom" name="plugin_geo2_maps_options[slimbox2_counter_text]">
							<option value="Image {x} of {y}" <?php echo ( $options['slimbox2_counter_text'] === 'Image {x} of {y}' ) ? 'selected' : ''; ?>>Image 1 of 20</option>
							<option value="Photo {x} of {y}" <?php echo ( $options['slimbox2_counter_text'] === 'Photo {x} of {y}' ) ? 'selected' : ''; ?>>Photo 1 of 20</option>
							<option value="{x}/{y}" <?php echo ( $options['slimbox2_counter_text'] === '{x}/{y}' ) ? 'selected' : ''; ?>>1/20</option>
							<option value="false" <?php echo ( $options['slimbox2_counter_text'] === 'false' ) ? 'selected' : ''; ?>>No counter</option>
						</select><br />
						<span class="description"><?php esc_html_e( 'Text value allows you to customize, translate or disable the counter text which appears in the captions when multiple images are shown. Inside the text, {x} will be replaced by the current image index, and {y} will be replaced by the total number of images. Set it to false (boolean value, without quotes) or "" to disable the counter display.', 'ngg-geo2-maps' ); ?></span><br />

						<h4><?php esc_html_e( 'Size Options', 'ngg-geo2-maps' ); ?></h4>
						<?php esc_html_e( 'The initial width of the box in pixels', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 slimbox2_initial_width=250]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[slimbox2_initial_width]" value="<?php echo (int) $options['slimbox2_initial_width']; ?>" /><br /><br />
						<?php esc_html_e( 'The initial height of the box in pixels', 'ngg-geo2-maps' ); ?><b><code style="margin-left:30px;">Shortcode: [geo2 slimbox2_initial_height=250]</code></b><br />
						<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[slimbox2_initial_height]" value="<?php echo (int) $options['slimbox2_initial_height']; ?>" /><br />
					</div>
				</div>
			</div>


			<div id="routes" class="postbox geo2_tab_content">
				<h3>
					<p>&ensp;&ensp;<?php esc_html_e( 'Routes', 'ngg-geo2-maps' ); ?></p>
				</h3>
				<div class="inside">
					<p><b><input type="checkbox" name="plugin_geo2_maps_options[route]" value="1" <?php checked( $options['route'], 1 ); ?>>&ensp;<?php esc_html_e( 'Activate Route Mode', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 route=1 / 0]</code></b></p><span class="description"><?php esc_html_e( 'Activate to use options below or for Shortcode loading route paths with elements (Shapes) containing balloon description shown using Infoboxes.', 'ngg-geo2-maps' ); ?></span><br /><br />
					<b><?php esc_html_e( 'Polyline Width', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 route_width=5]</code></b><br />
					<input type="text" size='5' class="code geo2_margin_top  geo2_margin_bottom" name="plugin_geo2_maps_options[route_width]" value="<?php echo floatval( $options['route_width'] ); ?>" /><br /><br />
					<b><?php esc_html_e( 'Polyline Color', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 route_color="rgba(0,0,200,0.6)"]</code></b><br />
					<div class="geo2_margin_top">
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(0,0,200,0.6)" name="plugin_geo2_maps_options[route_color]" value="<?php echo esc_attr( $options['route_color'] ); ?>" />
					</div>
					<br />
					<b><?php esc_html_e( 'Polygon Edge Width', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 route_polygon_width=5]</code></b><br />
					<input type="text" size='5' class="code geo2_margin_top" name="plugin_geo2_maps_options[route_polygon_width]" value="<?php echo floatval( $options['route_polygon_width'] ); ?>" /><br /><br />
					<b><?php esc_html_e( 'Polygon Edge Color', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 route_polygon_color="rgba(0,0,200,0.6)"]</code></b><br />
					<div class="geo2_margin_top">
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(0,0,200,0.6)" name="plugin_geo2_maps_options[route_polygon_color]" value="<?php echo esc_attr( $options['route_polygon_color'] ); ?>" />
					</div>
					<br />
					<b><?php esc_html_e( 'Polygon Fill Color', 'ngg-geo2-maps' ); ?><code style="margin-left:30px;">Shortcode: [geo2 route_polygon_fillcolor="rgba(0,0,255,0.6)"]</code></b><br />
					<div class="geo2_margin_top">
						<input type="text" class="color-picker code" data-alpha-enabled="true" data-default-color="rgba(0,0,255,0.6)" name="plugin_geo2_maps_options[route_polygon_fillcolor]" value="<?php echo esc_attr( $options['route_polygon_fillcolor'] ); ?>" />
					</div>
					<h4><?php esc_html_e( 'Path to route file', 'ngg-geo2-maps' ); ?></h4>
					<div class='buttons_wrapper'>
						<input id="upload_path_button" type="button" class="button" value="<?php esc_html_e( 'Upload file' ); ?>" />
						<input type="submit" name="submit_image_selector" value="Save" class="button-primary"><br /><br />
					</div>
					<span class="description"><?php esc_html_e( 'There is a support for pushpins, polylines and polygons in the imported file.', 'ngg-geo2-maps' ); ?></span><br />
					<input type="text" size='
					<?php
					if ( strlen( $options['xmlurl'] ) === 0 ) {
						echo 45;
					} else {
						echo strlen( $options['xmlurl'] ) + 3;
					}
					?>
					' class="code geo2_margin_top" name="plugin_geo2_maps_options[xmlurl]" id="xmlurl" value='<?php echo esc_url( $options['xmlurl'] ); ?>'><br />
					<h4><code>Shortcode: [geo2 xmlurl=http://...] </code></h4>
					<span class="description"><?php esc_html_e( 'Path for each map needs to be specified individually in a shortcode. Shapes from a file in the field above will be shown on all maps.', 'ngg-geo2-maps' ); ?></span><br /><br />
					<span class="description"><?php esc_html_e( 'Use a path starting with: "http://..." or "https://...". Accepted are common geospatial XML file formats such as KML (Keyhole Markup Language), KMZ (compressed KML), GeoRSS, GML (Geography Markup Language, exposed via GeoRSs ), and GPX (GPS Exchange Format).', 'ngg-geo2-maps' ); ?></span>
				</div>
			</div>

			<div id="geo2_plus" class="postbox geo2_tab_content">
				<h2><?php esc_html_e( 'GEO2 MAPS PLUS', 'ngg-geo2-maps' ); ?></h2>
				<a id="geo2_plus_upgrade" class="button-primary" href="http://www.geo2maps.plus" target="_blank">Get Geo2 Maps Plus NOW!</a>
				<h3><?php esc_html_e( 'Upgrade to much more powerful version of the Geo2 Maps with expanded functionality, other beautiful pins and Lightboxes, video capabilities and all of it with plenty of customization options.', 'ngg-geo2-maps' ); ?>
				</h3>
				<div class="inside">
					<table class="geo2_plus_tb">
						<caption><?php esc_html_e( 'Comparison of the basic Geo2 Maps and the expanded Geo2 Maps Plus for NextGEN plugins.', 'ngg-geo2-maps' ); ?></caption>
						<tr class="geo2_tr_head">
							<th class="geo2_plus_tb_col1">
								<h3>Features</h3>
							</th>
							<th>
								<h2>Geo2 Maps</h2>
							</th>
							<th>
								<h2>Geo2 Maps Plus</h2>
							</th>
						</tr>
						<tr style="height: 28px;">
							<td><?php esc_html_e( 'Map Provider', 'ngg-geo2-maps' ); ?></td>
							<td><i><?php esc_html_e( 'Bing Maps', 'ngg-geo2-maps' ); ?></i></td>
							<td><i><?php esc_html_e( 'Bing Maps', 'ngg-geo2-maps' ); ?></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Geocoding', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Load map on demand in Ajax Mode', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Map with selected images', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Map with tagged images', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'EXIF Viewer', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Preview Map', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Pushpins Type', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Canvas image thumbnails', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Pushpins', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Pushpins with thumbnails on hover', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Auto Mode', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Maps with images contained in galleries', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Maps with albums and/or galleries or images', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Top or bottom placement', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Option to disable with other maps', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Option to block Auto Mode', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Worldmap', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Map with albums and/or galleries', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Infobox or Lightbox for galleries or albums', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Exclude specific albums or gallery', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Map Options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Map zoom level and dimensions', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Dashboard, Locate Me button, Scalebar, Copyright, Terms, Logo', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Mini Map', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Navigation Bar mode and orientation, Map Type Selector, Zoom, Traffic buttons', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Map style: Road, Aerial, Bird\'s-eye view', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Map style: Canvas Light, Canvas Dark, Grayscale, Ordnance Survey', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Thumbnails and Pushpins options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Show title and caption', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Show text/Alt Text on pins/thumbnails head', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Thumbnails options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Rectangular', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Round', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Rectangular thumbnails with round corners', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Border width and color', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Albums Thumbnail scale factor', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Image and border shadow', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Pointer', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Pushpins options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Individual color for image, gallery and album pins', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Pins from image or SVG file', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Pins clustering', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Infobox and Lightbox options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Show gallery title and description', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Show EXIF and GPS data', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Show image tags', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Show copyrights or artist name', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Types: Infobox, Fancybox, Slimbox 2 or Fancybox 3', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Infobox with Lightbox', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'NextGEN Gallery Lightbox Override Mode', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'URL link to a specific web page instead of Infobox or Lightbox', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'CSS override field', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Infobox options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Overlap image title and description on top of Infobox thumbnail', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Infobox size override', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Background and text color', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Round corners', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Multiple Infoboxes', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Infobox dragging', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<td><?php esc_html_e( 'Hide image description and expand when clicked', 'ngg-geo2-maps' ); ?></td>
						<td><i class="geo2_td_empty"></i></td>
						<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Fancybox options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Scale image box to fit in viewport', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Infinite gallery navigation', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Show "Close" button, navigation arrows, image title and position', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Background opacity and color', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Transitions type and speed', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Fancybox 3 options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'No caption and bottom caption panel', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Prevent bottom caption to overlap the content', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Sliding or fixed side caption panel', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Side caption panel mini map with images location', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Video support and video options', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Thumbnails preview vertical orientation', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Horizontal thumbnails preview and automatic orientation', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Infinite gallery navigation, autostart in fullscreen or slideshow', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Show specific buttons, navigation arrows, counter', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Show Facebook and Twitter buttons', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Background, caption and buttons opacity and color', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Slideshow speed', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Transitions type and speed', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Translations: German, Polish, Spanish', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Disable Right-click - simple image protection for images', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Slimbox 2 options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Resizing and the initial size', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Enable infinite gallery navigation', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Background opacity, image counter style', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Transitions type and speed', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Route options', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Support for GPX, XML, KMZ, KML and GeoRSS files', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Polyline and polygon edge width and colors override', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr class="geo2_tr_category">
							<th class="geo2_plus_tb_col1">
								<h3><?php esc_html_e( 'Future improvements', 'ngg-geo2-maps' ); ?></h3>
							</th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Shortcode Editor', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Galleries sort order', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_check"></i></td>
							<td><i class="geo2_td_check"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Different pin shapes for different image tags', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Animated pins', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Fully customizable map appearance', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Geo2 Map Widget', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Mini Map left or right location', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Set and save GPS coordinates', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Geo2 (Fancybox 3) colour style presets', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( '"textOffset" option for pin head\'s text', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Infobox pointer and position offset option', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Fancybox 3 bottom caption panel mini map', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Fancybox 3 side caption panel left or right location', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
						<tr class="geo2_tr">
							<td><?php esc_html_e( 'Fancybox 3 commerce options', 'ngg-geo2-maps' ); ?></td>
							<td><i class="geo2_td_empty"></i></td>
							<td><i class="geo2_td_empty"></i></td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</div>

	<?php
}
?>