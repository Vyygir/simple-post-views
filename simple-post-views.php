<?php
/**
 * Plugin Name: Simple Post Views
 * Plugin URI: https://github.com/Vyygir/simple-post-views
 * Version: 0.2
 * Author: Vyygir
 * Author URI: https://github.com/Vyygir
 * Description: A simple way to add views to individual posts (currently only works with the default post type)
 */
class Simple_Post_Views {
	public static function init() {
		add_action( 'pre_get_posts', array( 'Simple_Post_Views', 'addPostView' ) );
		add_filter( 'manage_posts_columns', array( 'Simple_Post_Views', 'addPostsColumnsHead' ) );
		add_filter( 'manage_posts_custom_column', array( 'Simple_Post_Views', 'addPostsColumnsContent' ), 10, 2 );
	}

	public static function addPostView( $query ) {
		if ( $query->is_single && !is_admin() ) {
			$post = isset( $query->query['name'] ) ? get_page_by_path( $query->query['name'], OBJECT, 'post' ) : get_post( $query->query['p'] );

			if ( $post ) {
				$id = $post->ID;
				update_post_meta( $id, 'spv_views', ( self::getPostViews( $id ) + 1 ) );
			}
		}
	}

	public static function getPostViews( $id ) {
		if ( $id && is_int ( $id ) ) {
			$post = get_post( $id );

			if ( $post->post_type == 'post' ) {
				return ( $v = get_post_meta( $id, 'spv_views', true ) ) ? $v : '0';
			}
		}

		return false;
	}

	public static function addPostsColumnsHead( $defaults ) {
		global $current_screen;

		if ( $current_screen->post_type == 'post' ) {
			$defaults['views'] = 'Views';
		}

		return $defaults;
	}

	public static function addPostsColumnsContent( $name, $id ) {
		global $current_screen;

		if ( $name == 'views' && $current_screen->post_type == 'post' ) {
			$views = ( $v = get_post_meta( $id, 'spv_views', true ) ) ? $v : '0';
			echo $views;
		}
	}
}

Simple_Post_Views::init();