<?php
/**
 * Frontend Dashboard Main Menu
 *
 * @package frontend-dashboard-pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FEDP_MainMenu' ) ) {
	/**
	 * Class FEDP_MainMenu
	 */
	class FEDP_MainMenu {

		public function __construct() {
			add_action( 'fed_add_main_menu_item_bottom', array( $this, 'fed_pages_add_main_menu_item_bottom' ) );
			add_action( 'fed_edit_main_menu_item_bottom', array( $this, 'fed_pages_edit_main_menu_item_bottom' ) );
			add_action( 'fed_enqueue_script_style_admin', array( $this, 'fed_pages_enqueue_script_style_admin' ) );
			add_action( 'fed_override_default_page', array( $this, 'fed_pages_override_default_page' ), 10, 2 );
			add_filter( 'fed_customize_admin_user_profile_layout_options',
				array( $this, 'fedp_customize_admin_user_profile_layout_options' ), 10, 2 );
			add_filter( 'fed_process_menu', array( $this, 'fed_pages_process_menu' ), 10, 2 );
			add_filter(
				'fed_convert_dashboard_menu_url', array( $this, 'fed_pages_convert_dashboard_menu_url' ), 10,
				2
			);

			add_filter( 'fed_menu_title', array( $this, 'fed_pages_menu_title' ), 10, 3 );
			add_filter( 'fed_menu_default_page', array( $this, 'fed_pages_menu_default_page' ), 10, 3 );

			add_action( 'wp_ajax_fedp_save_elementor_settings', array( $this, 'save_elementor_settings' ) );
			add_action( 'wp_ajax_nopriv_fedp_save_elementor_settings', 'fed_block_the_action' );

		}

		public function save_elementor_settings() {
			$post_payload = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

			fed_verify_nonce();

			$elementor = 'Disable';
			if ( isset( $post_payload['enable_elementor'] ) && 'Enable' === $post_payload['enable_elementor'] ) {
				$elementor = 'Enable';
			}

			update_option( 'fedp_elementor_settings',
				array(
					'settings' => array(
						'enable_elementor' => $elementor,
					),
				)
			);

			wp_send_json_success( array( 'message' => 'Elementor Settings Successfully Saved' ) );
		}

		/**
		 * Custom Admin Dashboard.
		 *
		 * @param  array $options  Options.
		 * @param  array $fed_admin_options  Admin Options.
		 *
		 * @return mixed
		 */
		public function fedp_customize_admin_user_profile_layout_options( $options, $fed_admin_options ) {
			$new_options['fedp_elementor_settings'] = array(
				'icon'      => 'fa fa-cogs',
				'name'      => __( 'Elementor', 'frontend-dashboard' ),
				'callable'  => array(
					'object' => $this,
					'method' => 'elementor_settings',
				),
				'arguments' => $fed_admin_options,
			);

			return $options + $new_options;
		}

		public function elementor_settings() {
			$options = get_option( 'fedp_elementor_settings' );

			$array = array(
				'form'  => array(
					'method' => '',
					'class'  => 'fed_admin_menu fed_ajax',
					'attr'   => '',
					'action' => array(
						'url'    => '',
						'action' => 'fedp_save_elementor_settings',
					),
					'nonce'  => array(
						'action' => '',
						'name'   => '',
					),
					'loader' => '',
				),
				'input' => array(
					'Enable Elementor' => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Enable Elementor', 'frontend-dashboard' ),
						'input'        => fed_form_checkbox( array(
							'input_meta'    => 'enable_elementor',
							'default_value' => 'Enable',
							'label'         => '',
							'user_value'    => fed_get_data( 'settings.enable_elementor', $options ),
						) ),
						'help_message' => fed_show_help_message( array( 'content' => 'Enable the Elementor WordPress plugin Styles and Scripts in Frontend Dashboard' ) ),
					),
				),
			);

			fed_common_simple_layout( $array );
		}

		/**
		 * @param $menu_url
		 * @param $menu
		 *
		 * @return array|string
		 */
		public function fed_pages_convert_dashboard_menu_url( $menu_url, $menu ) {
			if ( isset( $menu['menu_key'] ) && $menu['menu_key'] === 'url' ) {
				$menu_value = unserialize( $menu['menu_value'] );
				$url        = isset( $menu_value['url'] ) ? esc_url( $menu_value['url'] ) : null;
				$target     = isset( $menu_value['target'] ) ? $menu_value['target'] : '';
				if ( $url !== null ) {
					return array(
						'url'    => $url,
						'target' => $target,
					);
				}

				return '#';
			}

			return $menu_url;
		}

		/**
		 * @param $menus
		 * @param $index
		 */
		public function fed_pages_override_default_page( $menus, $index ) {
			if ( 'yes' === $menus[ $index ]['menu_key'] ) {
				$post   = get_post( $menus[ $index ]['menu_value'] );
				$output = '';
				/**
				 * WPBakery Page Custom Page CSS
				 */
				$wpb = get_post_meta( $post->ID, '_wpb_shortcodes_custom_css', true );
				if ( $wpb ) {
					$shortcodes_custom_css = strip_tags( $wpb );
					$output                = '<style type="text/css" data-type="vc_shortcodes-custom-css">';
					$output                .= $shortcodes_custom_css;
					$output                .= '</style>';
				}

				if ( $post instanceof WP_Post ) {
					$elementor_settings = get_option( 'fedp_elementor_settings' );
					if ( defined( 'ELEMENTOR_VERSION' ) && isset( $elementor_settings['settings']['enable_elementor'] ) && 'Enable' === $elementor_settings['settings']['enable_elementor'] ) {
						require_once FED_PAGES_PLUGIN_DIR . '/main_menu/class-fedp-elementor.php';
						$elementor = new FEDP_Elementor( $post );
						if ( $elementor->is_page_elementor() ) {
							//phpcs:ignore
							echo $elementor->styled_content();
						} else {
							//phpcs:ignore
							echo apply_filters( 'the_content', $post->post_content );
						}
					} else {
						//phpcs:ignore
						echo apply_filters( 'the_content', $post->post_content );
					}

					//phpcs:ignore
					echo $output;
				}
			}
		}

		/**
		 * @param $status
		 * @param $menus
		 * @param $index
		 *
		 * @return bool
		 */
		public function fed_pages_menu_default_page( $status, $menus, $index ) {
			if ( isset( $menus[ $index ]['menu_key'] ) && $menus[ $index ]['menu_key'] === 'yes' ) {
				return false;
			}

			return $status;
		}

		/**
		 * @param $menu_title
		 * @param $menus
		 * @param $index
		 *
		 * @return string
		 */
		public function fed_pages_menu_title( $menu_title, $menus, $index ) {
			if ( isset( $menus[ $index ]['menu_key'] ) && $menus[ $index ]['menu_key'] === 'yes' ) {
				$post = get_post( $menus[ $index ]['menu_value'] );
				if ( $post instanceof WP_Post ) {
					$menu_value   = $post->post_title;
					$menu_content = $post->post_content;

					return $menu_value;
				}
			}

			return $menu_title;
		}

		public function fed_pages_add_main_menu_item_bottom() {
			?>
			<div class="row padd_top_20">
				<div class="flex_between fed_pages_menu_item_container">
					<div class="col-md-5 fed_page_menu_checkbox">
						<?php
						echo fed_get_input_details(
							array(
								'input_meta'    => 'fed_menu_key',
								'input_type'    => 'checkbox',
								'default_value' => 'yes',
								'user_value'    => '',
								'class_name'    => 'fed_show_pages_checkbox',
								'label'         => 'Show pages for this menu item?',
							)
						)
						?>
					</div>
					<div class="col-md-7 fed_page_menu_item hide">
						<?php
						wp_dropdown_pages(
							array(
								'name'  => 'fed_menu_value',
								'class' => 'form-control',
							)
						);
						?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3">
					<div class="flex-center">[OR]</div>
				</div>
			</div>

			<div class="row padd_top_20 fed_pages_menu_item_container">
				<div class="col-md-4">
					<?php
					echo fed_get_input_details(
						array(
							'input_meta'    => 'fed_menu_key',
							'class_name'    => 'fed_external_url_key',
							'input_type'    => 'checkbox',
							'default_value' => 'url',
							'label'         => 'Convert this menu into External URL ?',
						)
					)
					?>
				</div>
				<div class="col-md-8 fed_page_menu_item hide">
					<div class="row">
						<div class="col-md-4">
							<?php
							echo fed_get_input_details(
								array(
									'input_meta'  => 'fed_menu_value_url[target]',
									'class_name'  => 'fed_external_url_key',
									'input_type'  => 'select',
									'input_value' => array(
										''       => 'Open Link in?',
										'_self'  => 'Same Window',
										'_blank' => 'New Window',
									),
								)
							)
							?>
						</div>
						<div class="col-md-8">
							<?php
							echo fed_get_input_details(
								array(
									'input_meta'  => 'fed_menu_value_url[url]',
									'class_name'  => 'form-control external_url',
									'input_type'  => 'single_line',
									'placeholder' => 'Please enter the external URL',
								)
							)
							?>
						</div>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * @param $menu
		 */
		public function fed_pages_edit_main_menu_item_bottom( $menu ) {
			$hide     = isset( $menu['menu_key'] ) && $menu['menu_key'] === 'yes' ? '' : 'hide';
			$url_hide = isset( $menu['menu_key'] ) && $menu['menu_key'] === 'url' ? '' : 'hide';

			$link = isset( $menu['menu_value'], $menu['menu_key'] ) && $menu['menu_key'] === 'url' ? unserialize( $menu['menu_value'] ) : '';

			if ( $menu['extra'] !== 'no' ) {
				?>
				<div class="row padd_top_20">
					<div class="flex_between fed_pages_menu_item_container">
						<div class="col-md-5 fed_page_menu_checkbox">
							<?php
							echo fed_get_input_details(
								array(
									'input_meta'    => 'fed_menu_key',
									'input_type'    => 'checkbox',
									'default_value' => 'yes',
									'user_value'    => isset( $menu['menu_key'] ) && $menu['menu_key'] === 'yes' ? $menu['menu_key'] : '',
									'class_name'    => 'fed_show_pages_checkbox',
									'label'         => 'Show pages for this menu item?',

								)
							)
							?>
						</div>
						<div class="col-md-7 fed_page_menu_item <?php echo $hide; ?>">
							<?php
							wp_dropdown_pages(
								array(
									'name'     => 'fed_menu_value',
									'class'    => 'form-control',
									'selected' => isset( $menu['menu_value'] ) ? $menu['menu_value'] : '',
								)
							)
							?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<div class="flex-center">[OR]</div>
					</div>
				</div>
				<div class="row padd_top_20 fed_pages_menu_item_container">
					<div class="col-md-4">
						<?php
						echo fed_get_input_details(
							array(
								'input_meta'    => 'fed_menu_key',
								'class_name'    => 'fed_external_url_key',
								'input_type'    => 'checkbox',
								'user_value'    => isset( $menu['menu_key'] ) && $menu['menu_key'] === 'url' ? $menu['menu_key'] : '',
								'default_value' => 'url',
								'label'         => 'Convert this menu into External URL ?',
							)
						)
						?>
					</div>

					<div class="col-md-8 fed_page_menu_item <?php echo $url_hide; ?>">
						<div class="row">
							<div class="col-md-4">
								<?php

								echo fed_get_input_details(
									array(
										'input_meta'  => 'fed_menu_value_url[target]',
										'class_name'  => 'fed_external_url_key',
										'input_type'  => 'select',
										'user_value'  => isset( $link['target'] ) ? $link['target'] : '',
										'input_value' => array(
											''       => 'Open Link in?',
											'_self'  => 'Same Window',
											'_blank' => 'New Window',
										),
									)
								)
								?>
							</div>
							<div class="col-md-8">
								<?php
								echo fed_get_input_details(
									array(
										'input_meta'  => 'fed_menu_value_url[url]',
										'class_name'  => 'form-control external_url',
										'input_type'  => 'single_line',
										'user_value'  => isset( $link['url'] ) ? $link['url'] : '',
										'placeholder' => 'Please enter the external URL',
									)
								)
								?>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * @param $default_value
		 * @param $row
		 *
		 * @return array
		 */
		public function fed_pages_process_menu( $default_value, $row ) {
			$new_value               = array(
				'menu_key' => isset( $row['fed_menu_key'] ) ? esc_attr( $row['fed_menu_key'] ) : null,
			);
			$new_value['menu_value'] = '';
			if ( isset( $row['fed_menu_key'] ) && $row['fed_menu_key'] === 'yes' ) {
				$new_value['menu_value'] = $row['fed_menu_value'];
			}
			if ( isset( $row['fed_menu_key'] ) && $row['fed_menu_key'] === 'url' ) {
				$new_value['menu_value'] = serialize( $row['fed_menu_value_url'] );
			}

			return array_merge( $default_value, $new_value );
		}

		public function fed_pages_enqueue_script_style_admin() {
			wp_enqueue_script(
				'fed_pages_script', plugins_url( '/assets/fed_script_pages.js', FED_PAGES_PLUGIN ),
				array()
			);
		}
	}

	new FEDP_MainMenu();
}
