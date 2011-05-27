<?php
/*
Plugin Name: Disable Comments
Plugin URI: http://rayofsolaris.net/code/disable-comments-for-wordpress
Description: Allows administrators to disable comments on their site, globally or for certain post types.
Version: 0.1
Author: Samir Shah
Author URI: http://rayofsolaris.net/
License: GPL2
*/

if( ! defined( 'ABSPATH' ) )
	exit;

class Disable_Comments {
	const db_version = 1;
	private $disabled_types;
	
	function __construct() {
		if( $this->disabled_types = get_option( 'disable_comments_post_types', array() ) ) {
			add_action( 'wp_loaded', array( $this, 'remove_comment_support' ) );
			add_filter( 'comments_open', array( $this, 'filter_comment_status' ), 20, 2 );
		}
		
		if( is_admin() )
			add_action( 'admin_menu',	array( $this, 'settings_menu' ) 	);	
	}
	
	function remove_comment_support(){
		// prevents display of comment fields from edit/quick edit screens
		foreach( $this->disabled_types as $type ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
	
	function filter_comment_status( $open, $post_id ) {
		$post = get_post( $post_id );
		return in_array( $post->post_type, $this->disabled_types ) ? false : $open;
	}
	
	function settings_menu() {
		add_submenu_page('options-general.php', 'Disable Comments', 'Disable Comments', 'manage_options', 'disable_comments_settings', array( $this, 'settings_page' ) );
	}
	
	function settings_page() {
		$types = get_post_types( array( 'public' => true ), 'objects' );
		
		if ( isset( $_POST['submit'] ) ) {
			$this->disabled_types = empty( $_POST['disabled_types'] ) ? array() : (array) $_POST['disabled_types'];	
			update_option( 'disable_comments_post_types', $this->disabled_types );
		}
			
	?>
	<style>
	.indent {padding-left: 2em}
	</style>
	<div class="wrap">
	<h2>Disable Comments</h2>
	<form action="" method="post" id="disable-comments">
	<p>Globally disable comments on:</p>
	<ul class="indent" id="post-types">
		<?php foreach( $types as $k => $v ) echo "<li><input type='checkbox' name='disabled_types[]' value='$k' ". checked( in_array( $k, $this->disabled_types ), true, false ) ." id='post-type-$k'> <label for='post-type-$k'>{$v->labels->name}</label></li>";?>
	</ul>
	<p>Note:</p>
	<ul class="indent" style="list-style: disc">
	<li>Disabling comments will also disable trackbacks and pingbacks</li>
	<li>Disabling comments will also hide all comment-related fields from the edit/quick-edit screens of the affected posts.</li>
	<li>This plugin does not modify the comment status of individual posts in the database. If you uninstall the plugin, the comment status of affected posts will return to whatever it was before. This means you can use this plugin to temporarily disable comments without permananetly altering individual posts' comment statuses.</li>
	</ul>
	<p class="submit"><input class="button-primary" type="submit" name="submit" value="Update settings" /></p>
	</form>
	</div>
<?php
	}
}

new Disable_Comments();
