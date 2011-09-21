<?php
/*
Plugin Name: Disable Comments
Plugin URI: http://rayofsolaris.net/code/disable-comments-for-wordpress
Description: Allows administrators to globally disable comments on their site. Comments can be disabled according to post type.
Version: 0.2.1
Author: Samir Shah
Author URI: http://rayofsolaris.net/
License: GPL2
*/

if( !defined( 'ABSPATH' ) )
	exit;

class Disable_Comments {
	const db_version = 1;
	private $disabled_types;
	
	function __construct() {
		$this->disabled_types = get_option( 'disable_comments_post_types', array() );
		if( empty( $this->disabled_types ) ) {
			if( is_admin() )
				add_action( 'admin_notices', array( $this, 'setup_notice' ) );
		}
		else {
			add_action( 'wp_loaded', array( $this, 'remove_comment_support' ) );
			add_filter( 'comments_open', array( $this, 'filter_comment_status' ), 20, 2 );
			add_filter( 'pings_open', array( $this, 'filter_comment_status' ), 20, 2 );
		}
		
		if( is_admin() )
			add_action( 'admin_menu',	array( $this, 'settings_menu' ) 	);	
	}
	
	function setup_notice(){
		if( !strpos($_SERVER['REQUEST_URI'], 'options-general.php?page=disable_comments_settings') )
			echo '<div class="updated fade"><p>The <em>Disable Comments</em> plugin is active, but isn\'t configured to do anything yet. Visit the <a href="options-general.php?page=disable_comments_settings">configuration page</a> to choose which post types to disable comments on.</p></div>';
	}
	
	function remove_comment_support(){
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
			echo '<div id="message" class="updated fade"><p>Options updated.</p></div>';
		}
			
	?>
	<style>
	.indent {padding-left: 2em}
	</style>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Disable Comments</h2>
	<form action="" method="post" id="disable-comments">
	<p>Globally disable comments on:</p>
	<ul class="indent" id="post-types">
		<?php foreach( $types as $k => $v ) echo "<li><input type='checkbox' name='disabled_types[]' value='$k' ". checked( in_array( $k, $this->disabled_types ), true, false ) ." id='post-type-$k'> <label for='post-type-$k'>{$v->labels->name}</label></li>";?>
	</ul>
	<p><strong>Note:</strong> disabling comments will also disable trackbacks and pingbacks. All comment-related fields will also be hidden from the edit/quick-edit screens of the affected posts.</p>
	<p class="submit"><input class="button-primary" type="submit" name="submit" value="Update settings" /></p>
	</form>
	</div>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$("#disable-comments input").change(function(){
			$("#message").slideUp('slow');
		});
	});
	</script>
<?php
	}
}

new Disable_Comments();
