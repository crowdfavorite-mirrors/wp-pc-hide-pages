<?php
/*
Plugin Name: PC Hide Pages
Plugin URI: http://petercoughlin.com/
Description: Allows you to hide pages from WordPress menus, blog searches and search engines.
Version: 1.4
Author: Peter Coughlin
Author URI: http://petercoughlin.com/
License: GPLv2 or later
*/

/*
Copyright 2011 Peter Coughlin http://petercoughlin.com
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class pc_hide_pages {
	var $name = 'PC Hide Pages';
	var $shortname = 'Hide Pages';
	var $version = '1.4';
	var $slug;
	var $url;
	var $dir;
	var $basename;
	function __construct() {
		$this->url = plugin_dir_url( __FILE__ );
		$this->dir = str_replace( '\\', '/', plugin_dir_path( __FILE__ ) );
		$this->basename = plugin_basename( __FILE__ );
		$this->slug = str_replace( array( basename( __FILE__ ), '/' ), '', plugin_basename( __FILE__ ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		add_filter( 'get_pages', array( &$this, 'get_pages' ) );
		add_filter( 'pre_get_posts', array( &$this, 'pre_get_posts' ) );
		add_filter( 'wp_nav_menu_objects', array( &$this, 'wp_nav_menu_objects' ) );
		add_action( 'wp_head', array( &$this, 'wp_head' ) );
	}
	function deactivate() {
		delete_option( 'pc_hide_pages' );
	}
	function get_pages( $pages ) {
		if ( is_admin() )
			return $pages;
		$options = $this->get_options();
		$hidden_pages = $options['hidden_pages'];
		if (  count( $hidden_pages ) && $total = count( $pages ) ) {
			for ( $i = 0; $i < $total; $i++ ) {
				if ( in_array( $pages[$i]->ID, $hidden_pages ) )
					unset( $pages[$i] );
			}
		}
		return $pages;
	}
	function wp_nav_menu_objects( $sorted_menu_items ) {
		$options = $this->get_options();
		$hidden_pages = $options['hidden_pages'];
		if (  count( $hidden_pages ) && $total = count( $sorted_menu_items ) ) {
			for ( $i = 1; $i <= $total; $i++ ) {
				if ( ( 'page' == $sorted_menu_items[$i]->object ) && in_array( $sorted_menu_items[$i]->object_id, $hidden_pages ) ) {
					unset( $sorted_menu_items[$i] );
				}
			}
		}
		return $sorted_menu_items;
	}
	function pre_get_posts( $query ) {
		// exclude from search results..
		if ( is_search() ) {
			$options = $this->get_options();
			$hidden_pages = $options['hidden_pages'];
			if ( count( $hidden_pages ) )
				$query->set( 'post__not_in', $hidden_pages );
		}
		return $query;
	}
	function wp_head() {
		if ( is_page() ) {
			global $post;
			$options = $this->get_options();
			$hidden_pages = $options['hidden_pages'];
			if ( count( $hidden_pages ) && in_array( $post->ID, $hidden_pages ) )
				echo '<meta name="robots" content="noindex,noarchive,nosnippet,noodp,noydir" />'."\n";
		}
	}
	function get_options() {
		$options = get_option( 'pc_hide_pages' );
		if ( !is_array( $options ) )
			$options = $this->set_defaults();
		return $options;
	}
	function set_defaults() {
		$options = array( 'hidden_pages' => array() );
		update_option( 'pc_hide_pages', $options );
		return $options;
	}
}
$pc_hide_pages = new pc_hide_pages;

if ( is_admin() )
	include_once dirname( __FILE__ ) . '/admin.php';
