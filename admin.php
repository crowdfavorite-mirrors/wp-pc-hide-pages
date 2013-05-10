<?php
class pc_hide_pages_admin {
	function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	}
	function admin_menu() {
		global $pc_hide_pages;
		$pluginpage = add_options_page( $pc_hide_pages->name . ' Settings', $pc_hide_pages->shortname, 'manage_options', $pc_hide_pages->slug, array( &$this, 'settings_page' ) );
		add_filter( 'plugin_action_links_' . $pc_hide_pages->basename, array( &$this, 'settings_link' ) );
		add_action( "admin_print_scripts-$pluginpage", array( &$this, 'admin_plugin_scripts' ) );
	}
	function settings_link( $links ) {
		global $pc_hide_pages;
		$settings_link = '<a href="options-general.php?page=' . $pc_hide_pages->slug . '">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	function admin_plugin_scripts() {
		global $pc_hide_pages;
		echo '<link rel="stylesheet" href="' . $pc_hide_pages->url . 'css/admin.css" type="text/css" />' . "\n";
	}
	function settings_page() {
		global $pc_hide_pages;
		$options = $pc_hide_pages->get_options();		
		if ( isset( $_POST['save-changes'] ) ) {
			if ( function_exists( 'current_user_can' ) && !current_user_can( 'manage_options' ) )
				die( 'Sorry, not allowed...' );
			check_admin_referer( 'pc_hide_pages_settings' );

			if ( is_array( $_POST['hidden_pages'] ) && count( $_POST['hidden_pages'] ) )
				$options['hidden_pages'] = $_POST['hidden_pages'];
			else
				$options['hidden_pages'] = array();
			
			update_option( 'pc_hide_pages', $options );
			$msg .= '<p><strong>Settings saved.</strong></p>';
			echo '<div id="message" class="updated fade">' . $msg . '</div>';
		}
		echo '<div class="wrap">';
		screen_icon( 'options-general' );
		echo '<h2>' . $pc_hide_pages->name . ' Settings - version ' . $pc_hide_pages->version . '</h2>
		<form method="post">';
		if ( function_exists( 'wp_nonce_field' ) )
			wp_nonce_field( 'pc_hide_pages_settings' );
		echo '<p>This plugin enables you to hide pages on your blog.</p><p>It will prevent them from appearing in any of the standard menus, lists or searches. It will also add code to your page which tells search engines not to index the page or keep a cached copy.</p><p>This plugin is ideal for thank you pages, download pages ..etc.</p>
		<h3>Your Pages</h3>
		<p>Tick the pages you want to hide and click the Save Changes button.</p>
		<table class="form-table">
		<tr>
			<td colspan="2">';
			wp_list_pages( array( 'title_li' => '', 'walker' => new pdc_walker() ) );
			echo '</td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="save-changes" class="button-primary" value="Save Changes" /></p>
		</form>
		</div>';
	}
}
$pc_hide_pages_admin = new pc_hide_pages_admin;

class pdc_walker extends Walker_Page {
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '';
	}
	// start element..
	function start_el( &$output, $page, $depth, $args, $current_page = 0 ) {
		global $pc_hide_pages;
		$options = $pc_hide_pages->get_options();
		if ( $depth )
			$indent = str_repeat( ' &mdash; ', $depth );
		else
			$indent = '';

		$output .= '<div>';
		$output .= '<span style="color:#ccc;">' . $indent . '</span>';
		$output .= '<input type="checkbox" name="hidden_pages[]" value="' . $page->ID . '"';
		if ( !empty( $options['hidden_pages'] ) && in_array( $page->ID, $options['hidden_pages'] ) )
			$output .= ' checked="checked"';
		$output .= ' /> &nbsp;  <span style="font-weight:bold;">';
		$output .= apply_filters( 'the_title', $page->post_title, $page->ID ) . '</span> &middot <a href="' . get_permalink( $page->ID ) . '" target="_blank">View</a> | <a href="' . get_edit_post_link( $page->ID ) . '" target="_blank">Edit</a>';

	}
	// end element..
	function end_el( &$output, $page, $depth = 0, $args = array() ) {
		$output .= '</div>';
	}
	// if element was a child, end level..
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '';
	}
}
