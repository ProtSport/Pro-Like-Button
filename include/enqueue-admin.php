<?php 

	/*
	=======================================================
		ADMIN ENQUEUE FUNCTION
	=======================================================
	*/


	function plb_load_admin_scripts($hook){
		// Only load on this plugin's own admin pages, not on every wp-admin screen.
		// $plb_admin_page_hooks is populated in plb_like_add_admin_page() with the
		// exact hook suffixes add_menu_page()/add_submenu_page() returned.
		global $plb_admin_page_hooks;
		if ( empty( $plb_admin_page_hooks ) || ! in_array( $hook, $plb_admin_page_hooks, true ) ) {
			return;
		}

		wp_enqueue_style('plb_admin_css', plugins_url( '../assets/css/admin.css', __FILE__ ));
		wp_enqueue_media();
		wp_enqueue_script( 'ace',  plugins_url( '../assets/js/ace.js', __FILE__ ), array(), false, true );
		wp_enqueue_script( 'admin_media', plugins_url( '../assets/js/admin_media.js', __FILE__ ), array('jquery', 'ace'), false, true );

	}
	add_action( 'admin_enqueue_scripts', 'plb_load_admin_scripts' );
 ?>
