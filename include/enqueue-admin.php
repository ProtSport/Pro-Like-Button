<?php 

	/*
	=======================================================
		ADMIN ENQUEUE FUNCTION
	=======================================================
	*/


	function plb_load_admin_scripts($hook){
		global $admin_page_hooks;
	
		wp_enqueue_style('plb_admin_css', plugins_url( '../assets/css/admin.css', __FILE__ ));
		wp_enqueue_media();
		wp_enqueue_script( 'ace',  plugins_url( '../assets/js/ace.js', __FILE__ ));
		wp_enqueue_script( 'admin_media', plugins_url( '../assets/js/admin_media.js', __FILE__ ));
	 
	}
	add_action( 'admin_head', 'plb_load_admin_scripts' );
 ?>
