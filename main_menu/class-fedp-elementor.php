<?php

use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Frontend;
use Elementor\Plugin;

if ( ! class_exists( 'FEDP_Elementor' ) ) {
	/**
	 * Class FEDP_Elementor
	 */
	class FEDP_Elementor extends Frontend {
		public $e_post;

		/**
		 * FEDP_Elementor constructor.
		 *
		 * @param  \WP_Post $e_post
		 */
		public function __construct( WP_Post $e_post ) {
			parent::__construct();
			$this->e_post = $e_post;
			if ( is_singular() && Plugin::$instance->db->is_built_with_elementor( $e_post->ID ) ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
				$this->parse_global_css_code();

				if ( $this->e_post->ID && is_singular() ) {
					$css_file = Post_CSS::create( $this->e_post->ID );
					$css_file->enqueue();
				}
			}
		}

		/**
		 * @param  array $classes
		 *
		 * @return array
		 */
		public function body_class( $classes = array() ) {
			$classes = array_merge( $classes, array(
				'elementor-default',
			) );

			if ( is_singular() && Plugin::$instance->db->is_built_with_elementor( $this->e_post->ID ) ) {
				$classes[] = 'elementor-page elementor-page-' . $this->e_post->ID;
			}

			return $classes;
		}


		/**
		 * Get Styled Content as per Elementor
		 *
		 * @param  \WP_Post $post  Post Object
		 *
		 * @return string
		 */
		public function styled_content() {

			$content         = $this->e_post->post_content;
			$builder_content = $this->get_builder_content( $this->e_post->ID );

			if ( ! empty( $builder_content ) ) {
				$content = $builder_content;
				$this->remove_content_filters();
			}

			$this->add_content_filter();

			return $content;
		}

		/**
		 * @return bool
		 */
		public function is_page_elementor() {
			if ( is_singular() && Plugin::$instance->db->is_built_with_elementor( $this->e_post->ID ) ) {
				return true;
			}

			return false;
		}
	}
}