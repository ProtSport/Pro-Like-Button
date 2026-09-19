<?php 

	function plb_enqueue_scripts() {
		wp_enqueue_script( 'pro_like_post_script', plugins_url( '../assets/js/like_click.js', __FILE__ ), array('jquery') );
	}
	add_action( 'wp_enqueue_scripts', 'plb_enqueue_scripts' );

	function plb_load_plugin_css() {
		wp_enqueue_style( 'style_button', plugins_url( '../assets/css/button_style.css', __FILE__ ));
		wp_enqueue_style( 'style_all', plugins_url( '../assets/css/style.css', __FILE__ ));	    
	}
	add_action( 'wp_enqueue_scripts', 'plb_load_plugin_css' );


	add_action('wp_ajax_id', 'plb_action_callback');
	add_action('wp_ajax_nopriv_id', 'plb_action_callback');

	add_action( 'wp_enqueue_scripts', 'plb_myajax_data', 99 );
	function plb_myajax_data(){
			wp_localize_script( 'pro_like_post_script', 'myajax', 
				array(
					'url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('plb-nonce')
				)
			);  
	}


	$cookie_ip = $_SERVER['REMOTE_ADDR'];
	$cookie_ip_like = $_SERVER['REMOTE_ADDR'];
	$cookie_ip_dislike = $_SERVER['REMOTE_ADDR'];

	function plb_action_callback(){
		
		if ( empty( $_POST['nonce'] ) ) {
			wp_die();
		}

		if(check_ajax_referer('plb-nonce','nonce', false)){
			global $wpdb;
			$table_name_post = $wpdb->prefix . 'posts';
			$carently_likes_dislikes = $wpdb->get_row("SELECT counter_like, counter_dislike FROM $table_name_post WHERE id = $current_like", ARRAY_A);

			if (isset($_POST['liked'])) {
				$postid = sanitize_text_field($_POST['postid']);
							// save cookies
				setcookie('cookie_ip'.$postid, sanitize_text_field($_POST['liked']), time()+62208000, '/', $_SERVER['HTTP_HOST']);
				
				// counter like post
				$carently_likes_likes = $wpdb->get_row("SELECT counter_like, counter_dislike FROM $table_name_post WHERE id = $postid", ARRAY_A);
				$var_like = $carently_likes_likes['counter_like'];
				// check is there or not cookies
				
				if(isset($_COOKIE["cookie_ip".$postid])){
					$wpdb->query("UPDATE $table_name_post SET counter_like=$var_like WHERE id = $postid");
					echo  $var_like;
					wp_die();
				}
				setcookie('cookie_ip_like'.$postid, sanitize_text_field($_POST['liked']), time()+62208000, '/', $_SERVER['HTTP_HOST']);

				// add like database and frontend
				$wpdb->query("UPDATE $table_name_post SET counter_like=$var_like+1 WHERE id = $postid");
				echo  $var_like + 1;
				
				wp_die();
			}

			if (isset($_POST['unliked'])) {
				$postid = sanitize_text_field($_POST['postid']);

				// save cookies
				setcookie('cookie_ip'.$postid, $_POST['unliked'], time()+62208000, '/', $_SERVER['HTTP_HOST']);
				
				// counter like post
				$carently_likes_dislikes = $wpdb->get_row("SELECT counter_like, counter_dislike FROM $table_name_post WHERE id = $postid", ARRAY_A);
				$var_dislike = $carently_likes_dislikes['counter_dislike'];

				// check is there or not cookies
				if(isset($_COOKIE["cookie_ip".$postid])){
					$wpdb->query("UPDATE $table_name_post SET counter_dislike=$var_dislike WHERE id = $postid");
					echo  $var_dislike;
					wp_die();
				}
				setcookie('cookie_ip_dislike'.$postid, $_POST['unliked'], time()+62208000, '/', $_SERVER['HTTP_HOST']);

				// add like database and frontend
				$wpdb->query("UPDATE $table_name_post SET counter_dislike=$var_dislike-1 WHERE id = $postid");
				echo  $var_dislike-1;

				wp_die();
			}
		}
		else{
			wp_die();
		}
	}




	
	

?>