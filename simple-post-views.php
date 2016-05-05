<?php
/**
  * Plugin Name: Simple Post Views
  * Plugin URI: https://github.com/Vyygir/simple-post-views
  * Version: 0.2
  * Author: Matt Royce
  * Author URI: http://www.mattroyce.org/
  * Description: A simple way to add views to individual posts, with support for custom post types
***/
	class Simple_Post_Views {
		private static $post_types = array();

		public function __construct() {
			add_action('pre_get_posts', array('Simple_Post_Views', 'add_post_view'));
		}

		public static function init() {
			self::$post_types = get_post_types(array(
				'public' => true,
				'_builtin' => false
			));

			add_filter('manage_posts_columns', array('Simple_Post_Views', 'add_posts_columns_head'));

			self::$post_types[] = 'post';
			self::$post_types[] = 'page';

			if (!empty(self::$post_types)) {
				foreach (self::$post_types as $post_type) {
					add_filter('manage_' . $post_type . '_posts_columns', array('Simple_Post_Views', 'add_posts_columns_head'));
					add_filter('manage_' . $post_type . '_posts_custom_column', array('Simple_Post_Views', 'add_posts_columns_content'), 10, 2);
				}
			}
		}

		public static function add_post_view($query) {
			if (!is_admin() && $query->is_singular && is_int($query->queried_object_id)) {
				$id = $query->queried_object_id;
				update_post_meta($id, 'spv_views', (self::get_post_views($id) + 1));
			}
		}

		public static function get_post_views($id) {
			if ($id && is_int ($id)) {
				$post = get_post($id);

				if (in_array($post->post_type, self::$post_types)) {
					return ($v = get_post_meta($id, 'spv_views', true)) ? $v : '0';
				}
			}

			return false;
		}

		public static function add_posts_columns_head($defaults) {
			global $current_screen;

			if (in_array($current_screen->post_type, self::$post_types)) {
				$defaults['views'] = 'Views';
			}

			return $defaults;
		}

		public static function add_posts_columns_content($name, $id) {
			global $current_screen;

			if ($name == 'views' && in_array($current_screen->post_type, self::$post_types)) {
				$views = ($v = get_post_meta($id, 'spv_views', true)) ? $v : '0';
				echo $views;
			}
		}
	}

	$spv = new Simple_Post_Views();
	add_action('admin_init', array('Simple_Post_Views', 'init'));