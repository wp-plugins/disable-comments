<?php
/*
Plugin Name: Disable Comments
Plugin URI: http://rayofsolaris.net/code/disable-comments-for-wordpress
Description: Allows administrators to globally disable comments on their site. Comments can be disabled according to post type.
Version: 0.3.2
Author: Samir Shah
Author URI: http://rayofsolaris.net/
License: GPL2
*/

if( !defined( 'ABSPATH' ) )
	exit;

class Disable_Comments {
	const db_version = 2;
	private $options;
	
	function __construct() {
		// load options
		$this->options = get_option( 'disable_comments_options', array() );
		
		if( !isset( $this->options['db_version'] ) || $this->options['db_version'] < self::db_version ) {
			// upgrade options from version 0.2.1 or earlier to 0.3
			$this->options['disabled_post_types'] = get_option( 'disable_comments_post_types', array() );
			delete_option( 'disable_comments_post_types' );
			foreach( array( 'remove_admin_menu_comments', 'remove_admin_bar_comments', 'remove_recent_comments', 'remove_discussion' ) as $v )
				$this->options[$v] = false;
			$this->options['db_version'] = self::db_version;
			update_option( 'disable_comments_options', $this->options );
		}
		
		add_action( 'wp_loaded', array( $this, 'setup_filters' ) );
	}
	
	function setup_filters(){
		if( !empty( $this->options['disabled_post_types'] ) ) {
			foreach( $this->options['disabled_post_types'] as $type ) {
				remove_post_type_support( $type, 'comments' );
				remove_post_type_support( $type, 'trackbacks' );
			}
			add_filter( 'comments_open', array( $this, 'filter_comment_status' ), 20, 2 );
			add_filter( 'pings_open', array( $this, 'filter_comment_status' ), 20, 2 );
		}
		elseif( is_admin() ) {
			add_action( 'admin_notices', array( $this, 'setup_notice' ) );
		}
		
		if( $this->options['remove_admin_bar_comments'] && is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 50 );	// WP<3.3
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );	// WP 3.3
		}
		
		if( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'settings_menu' ) );
			
			if( $this->options['remove_admin_menu_comments'] )
				add_action('admin_menu', array( $this, 'filter_admin_menu' ) );
				
			if( $this->options['remove_discussion'] )
				add_action( 'admin_head', array( $this, 'hide_discussion_rightnow' ) );
				
			if( $this->options['remove_recent_comments'] )
				add_action( 'wp_dashboard_setup', array( $this, 'filter_dashboard' ) );
		}
	}
	
	function setup_notice(){
		if( !strpos($_SERVER['REQUEST_URI'], 'options-general.php?page=disable_comments_settings') )
			echo '<div class="updated fade"><p>The <em>Disable Comments</em> plugin is active, but isn\'t configured to do anything yet. Visit the <a href="options-general.php?page=disable_comments_settings">configuration page</a> to choose which post types to disable comments on.</p></div>';
	}
	
	function filter_admin_menu(){
		global $menu;
		if( isset( $menu[25] ) && $menu[25][2] == 'edit-comments.php' )
			unset( $menu[25] );
	}
	
	function filter_dashboard(){
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
	
	function hide_discussion_rightnow(){
		if( 'dashboard' == get_current_screen()->id )
			add_action( 'admin_print_footer_scripts', array( $this, 'discussion_js' ) );
	}
	
	function discussion_js(){
		// getting hold of the discussion box is tricky. The table_discussion class is used for other things in multisite
		echo '<script> jQuery(document).ready(function($){ $("#dashboard_right_now .table_discussion").has(\'a[href="edit-comments.php"]\').first().hide(); }); </script>';
	}
	
	function filter_comment_status( $open, $post_id ) {
		$post = get_post( $post_id );
		return in_array( $post->post_type, $this->options['disabled_post_types'] ) ? false : $open;
	}
	
	function settings_menu() {
		add_submenu_page('options-general.php', 'Disable Comments', 'Disable Comments', 'manage_options', 'disable_comments_settings', array( $this, 'settings_page' ) );
	}
	
	function settings_page() {
		$types = get_post_types( array( 'public' => true ), 'objects' );
		
		if ( isset( $_POST['submit'] ) ) {
			$this->options['disabled_post_types'] = empty( $_POST['disabled_types'] ) ? array() : (array) $_POST['disabled_types'];	
			foreach( array( 'remove_admin_menu_comments', 'remove_admin_bar_comments', 'remove_recent_comments', 'remove_discussion' ) as $v )
				$this->options[$v] = !empty( $_POST[$v] );	
			update_option( 'disable_comments_options', $this->options );
			echo '<div id="message" class="updated fade"><p>Options updated. Changes to the Admin Menu and Admin Bar will not appear until you leave or reload this page.</p></div>';
		}	
	?>
	<style> .indent {padding-left: 2em} </style>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Disable Comments</h2>
	<form action="" method="post" id="disable-comments">
	<p>Globally disable comments on:</p>
	<ul class="indent">
		<?php foreach( $types as $k => $v ) echo "<li><input type='checkbox' name='disabled_types[]' value='$k' ". checked( in_array( $k, $this->options['disabled_post_types'] ), true, false ) ." id='post-type-$k'> <label for='post-type-$k'>{$v->labels->name}</label></li>";?>
	</ul>
	<p><strong>Note:</strong> disabling comments will also disable trackbacks and pingbacks. All comment-related fields will also be hidden from the edit/quick-edit screens of the affected posts.</p>
	<h3>Other options</h3>
	<ul class="indent">
		<li><input type="checkbox" name="remove_admin_menu_comments" id="remove_admin_menu_comments" <?php checked( $this->options['remove_admin_menu_comments'] );?>> <label for="remove_admin_menu_comments">Remove the "Comments" link from the Admin Menu</label></li>
		<li><input type="checkbox" name="remove_admin_bar_comments" id="remove_admin_bar_comments" <?php checked( $this->options['remove_admin_bar_comments'] );?>> <label for="remove_admin_bar_comments">Remove the "Comments" link from the Admin Bar</label></li>
		<li><input type="checkbox" name="remove_recent_comments" id="remove_recent_comments" <?php checked( $this->options['remove_recent_comments'] );?>> <label for="remove_recent_comments">Remove the "Recent Comments" widget from the Dashboard</label></li>
		<li><input type="checkbox" name="remove_discussion" id="remove_discussion" <?php checked( $this->options['remove_discussion'] );?>> <label for="remove_discussion">Remove the "Discussion" section from the Right Now widget on the Dashboard <span class="hide-if-js"><strong>(Note: this option will only work if you have Javascript enabled in your browser)</strong><span></label></li>
	</ul>
	<p><strong>Note:</strong> these options are global. They will affect all users, everywhere, regardless of whether comments are enabled on portions of your site. Use them only if you want to remove all references to comments <em>everywhere</em>.
	<p class="submit"><input class="button-primary" type="submit" name="submit" value="Update settings" /></p>
	</form>
	</div>
	<script>
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
