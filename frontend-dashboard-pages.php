<?php
/**
 * Plugin Name: Frontend Dashboard Pages
 * Plugin URI: https://buffercode.com/plugin/frontend-dashboard-pages
 * Description: Frontend Dashboard Pages is a plugin to show pages inside the Frontend Dashboard menu. The assigning page may contain content, images and even shortcodes
 * Version: 3.0.0
 * Author: vinoth06
 * Author URI: http://buffercode.com/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fed_check = get_option( 'fed_plugin_version' );

require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( $fed_check && is_plugin_active( 'frontend-dashboard/frontend-dashboard.php' ) ) {

	/**
	 * Version Number
	 */
	define( 'FED_PAGES_PLUGIN_VERSION', '3.0.0' );

	/**
	 * App Name
	 */
	define( 'FED_PAGES_APP_NAME', 'Frontend Dashboard Pages' );

	/**
	 * Root Path
	 */
	define( 'FED_PAGES_PLUGIN', __FILE__ );
	/**
	 * Plugin Base Name
	 */
	define( 'FED_PAGES_PLUGIN_BASENAME', plugin_basename( FED_PAGES_PLUGIN ) );
	/**
	 * Plugin Name
	 */
	define( 'FED_PAGES_PLUGIN_NAME', trim( dirname( FED_PAGES_PLUGIN_BASENAME ), '/' ) );
	/**
	 * Plugin Directory
	 */
	define( 'FED_PAGES_PLUGIN_DIR', untrailingslashit( dirname( FED_PAGES_PLUGIN ) ) );


	require_once FED_PAGES_PLUGIN_DIR . '/install.php';
	require_once FED_PAGES_PLUGIN_DIR . '/main_menu/FEDP_MainMenu.php';
	require_once FED_PAGES_PLUGIN_DIR . '/functions.php';

	/**
	 * Deprecation Notice: Frontend Dashboard Pages is now built directly into Frontend Dashboard Core (v3.0.0+).
	 */
	function fed_pages_deprecation_admin_notice() {
		$deactivate_url = wp_nonce_url(
			admin_url( 'plugins.php?action=deactivate&plugin=' . urlencode( FED_PAGES_PLUGIN_BASENAME ) ),
			'deactivate-plugin_' . FED_PAGES_PLUGIN_BASENAME
		);
		?>
		<div class="notice notice-info is-dismissible" style="border-left-color: #4f46e5; padding: 12px 16px;">
			<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
				<div>
					<p style="margin: 0 0 4px 0; font-size: 13px; font-weight: 700; color: #1e293b;">
						<span class="dashicons dashicons-info" style="color: #4f46e5; margin-right: 4px; vertical-align: middle;"></span>
						<?php esc_html_e( 'Frontend Dashboard Pages has been integrated into Core!', 'frontend-dashboard-pages' ); ?>
					</p>
					<p style="margin: 0; font-size: 12px; color: #475569;">
						<?php esc_html_e( 'Custom WordPress page mapping and external URLs are now natively supported in Frontend Dashboard (v3.0.0+). You can safely deactivate and remove this add-on without losing any menu configurations.', 'frontend-dashboard-pages' ); ?>
					</p>
				</div>
				<div>
					<a href="<?php echo esc_url( $deactivate_url ); ?>" class="button button-primary" style="background-color: #4f46e5; border-color: #4338ca; text-shadow: none;">
						<?php esc_html_e( 'Deactivate Add-on', 'frontend-dashboard-pages' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'fed_pages_deprecation_admin_notice' );
} else {
	function fed_global_admin_notification_pages() {
		?>
		<div class="notice notice-warning">
			<p>
				<b>
					<?php
						_e( 'Please install <a href="https://buffercode.com/plugin/frontend-dashboard">Frontend Dashboard</a> to use this plugin [Frontend Dashboard Pages]', 'frontend-dashboard-pages' );
					?>
					</b>
				</p>
			</div>
			<?php
	}
	add_action( 'admin_notices', 'fed_global_admin_notification_pages' );
}

