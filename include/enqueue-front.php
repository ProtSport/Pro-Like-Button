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

		// The nonce only proves the request came from a page we rendered (CSRF protection).
		// It is printed on every front-end page, so it does NOT authenticate the caller —
		// every value below must still be validated before it reaches a query.
		if ( ! check_ajax_referer( 'plb-nonce', 'nonce', false ) ) {
			wp_die();
		}

		global $wpdb;

		$prolike_table = $wpdb->prefix . 'prolike';
		$settings = $wpdb->get_row( "SELECT who_can_like FROM $prolike_table WHERE id = 1", ARRAY_A );
		if ( ! empty( $settings ) && $settings['who_can_like'] === 'members' && ! is_user_logged_in() ) {
			wp_die();
		}

		$type            = ( isset( $_POST['type'] ) && $_POST['type'] === 'comment' ) ? 'comment' : 'post';
		$table_name_post = ( $type === 'comment' ) ? $wpdb->prefix . 'comments' : $wpdb->prefix . 'posts';
		$id_column       = ( $type === 'comment' ) ? 'comment_ID' : 'id';
		$cookie_key      = ( $type === 'comment' ) ? 'cookie_ip_comment' : 'cookie_ip';

		// postid must be a positive integer. It is never concatenated into SQL directly —
		// every query below binds it through $wpdb->prepare() with a %d placeholder.
		$postid = isset( $_POST['postid'] ) ? absint( $_POST['postid'] ) : 0;
		if ( $postid <= 0 ) {
			wp_die();
		}

		if ( isset( $_POST['liked'] ) ) {
			// save cookies
			setcookie( $cookie_key . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// counter like post
			$carently_likes_likes = $wpdb->get_row(
				$wpdb->prepare( "SELECT counter_like, counter_dislike FROM $table_name_post WHERE $id_column = %d", $postid ),
				ARRAY_A
			);
			$var_like = isset( $carently_likes_likes['counter_like'] ) ? (int) $carently_likes_likes['counter_like'] : 0;
			// check is there or not cookies

			if ( isset( $_COOKIE[ $cookie_key . $postid ] ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $table_name_post SET counter_like = %d WHERE $id_column = %d", $var_like, $postid ) );
				echo $var_like;
				wp_die();
			}
			setcookie( $cookie_key . '_like' . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// add like database and frontend
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name_post SET counter_like = %d WHERE $id_column = %d", $var_like + 1, $postid ) );
			echo $var_like + 1;

			wp_die();
		}

		if ( isset( $_POST['unliked'] ) ) {
			// save cookies
			setcookie( $cookie_key . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// counter like post
			$carently_likes_dislikes = $wpdb->get_row(
				$wpdb->prepare( "SELECT counter_like, counter_dislike FROM $table_name_post WHERE $id_column = %d", $postid ),
				ARRAY_A
			);
			$var_dislike = isset( $carently_likes_dislikes['counter_dislike'] ) ? (int) $carently_likes_dislikes['counter_dislike'] : 0;

			// check is there or not cookies
			if ( isset( $_COOKIE[ $cookie_key . $postid ] ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $table_name_post SET counter_dislike = %d WHERE $id_column = %d", $var_dislike, $postid ) );
				echo $var_dislike;
				wp_die();
			}
			setcookie( $cookie_key . '_dislike' . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// add like database and frontend
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name_post SET counter_dislike = %d WHERE $id_column = %d", $var_dislike - 1, $postid ) );
			echo $var_dislike - 1;

			wp_die();
		}

		wp_die();
	}
