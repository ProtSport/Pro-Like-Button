<?php 

	/*
	=======================================================
		ADMIN ENQUEUE FUNCTION
	=======================================================
	*/


	function plb_load_admin_scripts($hook){
		// Only load on this plugin's own settings page, not on every wp-admin screen.
		if ( strpos( $hook, 'prolike_first_plugin' ) === false ) {
			return;
		}

		wp_enqueue_style('plb_admin_css', plugins_url( '../assets/css/admin.css', __FILE__ ));
		wp_enqueue_media();
		wp_enqueue_script( 'ace',  plugins_url( '../assets/js/ace.js', __FILE__ ), array(), false, true );
		wp_enqueue_script( 'admin_media', plugins_url( '../assets/js/admin_media.js', __FILE__ ), array('jquery', 'ace'), false, true );

	}
	add_action( 'admin_enqueue_scripts', 'plb_load_admin_scripts' );
 ?>
