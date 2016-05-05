<?php
/**
 * Plugin Name: Simple Post Views
 * Plugin URI: https://github.com/Vyygir/simple-post-views
 * Version: 0.2.2
 * Author: Matt Royce
 * Author URI: http://www.mattroyce.org/
 * Description: A simple way to add views to individual posts
**/
class Simple_Post_Views {
	private static $post_types = array();

	public function __construct() {
		add_action('pre_get_posts', array('Simple_Post_Views', 'add_post_view'));
	}

	public static function build_post_types() {
		self::$post_types = get_post_types(array(
			'public' => true,
			'_builtin' => false
		));
	}

	public static function init() {
		add_filter('manage_posts_columns', array('Simple_Post_Views', 'add_posts_columns_head'));

		self::$post_types['post'] = 'post';
		self::$post_types['page'] = 'page';

		if (!empty(self::$post_types)) {
			foreach (self::$post_types as $post_type) {
				add_filter('manage_' . $post_type . '_posts_columns', array('Simple_Post_Views', 'add_posts_columns_head'));
				add_filter('manage_' . $post_type . '_posts_custom_column', array('Simple_Post_Views', 'add_posts_columns_content'), 10, 2);
			}
		}
	}

	public static function add_post_view($query) {
		if (!is_admin()) {
			self::$post_types['post'] = 'post';
			self::$post_types['page'] = 'page';

			if ($query->is_singular && (is_int($query->queried_object_id) || isset($query->query['name']) || isset($query->query_vars['page_id']))) {
				if (is_int($query->queried_object_id)) {
					$id = $query->queried_object_id;
				} elseif (isset($query->query_vars['page_id']) && $query->query_vars['page_id']) {
					$id = $query->query_vars['page_id'];
				} else {
					$_post = get_page_by_path($query->query['name'], OBJECT, self::$post_types);
					$id = $_post->ID;
				}

				update_post_meta($id, 'spv_views', (self::get_post_views($id) + 1));
			} elseif ($query->is_posts_page) {
				$id = get_option('page_for_posts');
				update_post_meta($id, 'spv_views', (self::get_post_views($id) + 1));
			}
		}
	}

	public static function get_post_views($id) {
		$post = get_post($id);

		if (in_array($post->post_type, self::$post_types)) {
			return ($v = get_post_meta($id, 'spv_views', true)) ? $v : '0';
		}
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
add_action('wp_loaded', array('Simple_Post_Views', 'build_post_types'));
add_action('admin_init', array('Simple_Post_Views', 'init'));