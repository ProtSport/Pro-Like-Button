<?php
if ( ! defined( 'ABSPATH' ) ) exit;

	function plb_enqueue_scripts() {
		wp_enqueue_script( 'pro_like_post_script', plugins_url( '../assets/js/like_click.js', __FILE__ ), array('jquery') );

		// reCAPTCHA is optional — only pull in Google's script when a version
		// and site key are actually configured.
		global $wpdb;
		$prolike_table = $wpdb->prefix . 'prolike';
		$settings = $wpdb->get_row( $wpdb->prepare( "SELECT recaptcha_version, recaptcha_site_key FROM %i WHERE id = 1", $prolike_table ), ARRAY_A );
		if ( ! empty( $settings ) && in_array( $settings['recaptcha_version'], array( 'v2', 'v3' ), true ) && ! empty( $settings['recaptcha_site_key'] ) ) {
			if ( $settings['recaptcha_version'] === 'v3' ) {
				wp_enqueue_script( 'plb_recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $settings['recaptcha_site_key'] ), array(), null, true );
			} else {
				wp_enqueue_script( 'plb_recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
			}
		}
	}
	add_action( 'wp_enqueue_scripts', 'plb_enqueue_scripts' );

	// reCAPTCHA v2 needs a visible checkbox widget somewhere on the page —
	// one shared widget is enough, the like/dislike click handlers just read
	// its response before sending a vote (see assets/js/like_click.js).
	function plb_output_recaptcha_v2_widget() {
		global $wpdb;
		$prolike_table = $wpdb->prefix . 'prolike';
		$settings = $wpdb->get_row( $wpdb->prepare( "SELECT recaptcha_version, recaptcha_site_key FROM %i WHERE id = 1", $prolike_table ), ARRAY_A );
		if ( empty( $settings ) || $settings['recaptcha_version'] !== 'v2' || empty( $settings['recaptcha_site_key'] ) ) {
			return;
		}
		echo '<div id="plb-recaptcha-v2" class="g-recaptcha" data-sitekey="' . esc_attr( $settings['recaptcha_site_key'] ) . '"></div>';
	}
	add_action( 'wp_footer', 'plb_output_recaptcha_v2_widget' );

	function plb_load_plugin_css() {
		wp_enqueue_style( 'style_button', plugins_url( '../assets/css/button_style.css', __FILE__ ));
		wp_enqueue_style( 'style_all', plugins_url( '../assets/css/style.css', __FILE__ ));
	}
	add_action( 'wp_enqueue_scripts', 'plb_load_plugin_css' );


	add_action('wp_ajax_id', 'plb_action_callback');
	add_action('wp_ajax_nopriv_id', 'plb_action_callback');

	add_action( 'wp_enqueue_scripts', 'plb_myajax_data', 99 );
	function plb_myajax_data(){
			global $wpdb;
			$prolike_table = $wpdb->prefix . 'prolike';
			$settings = $wpdb->get_row( $wpdb->prepare( "SELECT recaptcha_version, recaptcha_site_key FROM %i WHERE id = 1", $prolike_table ), ARRAY_A );

			wp_localize_script( 'pro_like_post_script', 'myajax',
				array(
					'url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('plb-nonce'),
					'recaptchaVersion' => ! empty( $settings['recaptcha_version'] ) ? $settings['recaptcha_version'] : 'none',
					'recaptchaSiteKey' => ! empty( $settings['recaptcha_site_key'] ) ? $settings['recaptcha_site_key'] : '',
				)
			);
	}


	$cookie_ip = $_SERVER['REMOTE_ADDR'];
	$cookie_ip_like = $_SERVER['REMOTE_ADDR'];
	$cookie_ip_dislike = $_SERVER['REMOTE_ADDR'];

	// Records one vote event for the Statistics page. Only called for genuinely
	// new votes (not the "already voted, just return the count" path), so the
	// per-day/per-week counts on the stats page match real voter actions.
	function plb_log_vote( $object_type, $object_id, $vote_type ) {
		global $wpdb;
		$votes_table = $wpdb->prefix . 'prolike_votes';
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

		$wpdb->insert(
			$votes_table,
			array(
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'vote_type'   => $vote_type,
				'user_id'     => is_user_logged_in() ? get_current_user_id() : null,
				'ip_hash'     => ( $ip !== '' ) ? hash( 'sha256', $ip . wp_salt() ) : null,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%d', '%s', '%s' )
		);
	}

	// Hard cap: at most one vote (like OR dislike) per IP per post/comment.
	// Unlike the cookie check below, this can't be bypassed by clearing
	// cookies or opening a private window — only by changing IP. Reuses the
	// vote log written by plb_log_vote(), so no extra table/column needed.
	// Note: visitors sharing one public IP (e.g. an office behind NAT) share
	// this limit too — that trade-off is intentional here.
	function plb_ip_already_voted( $object_type, $object_id ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		if ( $ip === '' ) {
			return false;
		}
		global $wpdb;
		$votes_table = $wpdb->prefix . 'prolike_votes';
		$ip_hash = hash( 'sha256', $ip . wp_salt() );
		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM %i WHERE object_type = %s AND object_id = %d AND ip_hash = %s",
			$votes_table, $object_type, $object_id, $ip_hash
		) );
		return ( (int) $existing ) > 0;
	}

	// Simple on/off rate-limit: at most 10 votes per minute from the same IP.
	// The limit itself isn't user-configurable (see plans/03) — only whether
	// it's on. Fails open if we can't identify the visitor's IP at all.
	function plb_check_rate_limit(){
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		if ( $ip === '' ) {
			return true;
		}
		$key = 'plb_rl_' . hash( 'sha256', $ip . wp_salt() );
		$count = (int) get_transient( $key );
		if ( $count >= 10 ) {
			return false;
		}
		set_transient( $key, $count + 1, 60 );
		return true;
	}

	// reCAPTCHA is fully optional: returns true (allowed) whenever it isn't
	// configured, so leaving it "Off" never blocks voting.
	function plb_verify_recaptcha( $version, $secret_key ){
		if ( empty( $version ) || $version === 'none' || empty( $secret_key ) ) {
			return true;
		}

		$token = isset( $_POST['recaptcha_token'] ) ? sanitize_text_field( $_POST['recaptcha_token'] ) : '';
		if ( $token === '' ) {
			return false;
		}

		$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
			'body'    => array(
				'secret'   => $secret_key,
				'response' => $token,
				'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			),
			'timeout' => 5,
		) );

		if ( is_wp_error( $response ) ) {
			// Google unreachable — don't let a third-party outage block all voting.
			return true;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['success'] ) ) {
			return false;
		}
		if ( $version === 'v3' ) {
			$score = isset( $body['score'] ) ? (float) $body['score'] : 0.0;
			return $score >= 0.5;
		}
		return true;
	}

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
		$settings = $wpdb->get_row( $wpdb->prepare( "SELECT who_can_like, rate_limit_enabled, recaptcha_version, recaptcha_secret_key FROM %i WHERE id = 1", $prolike_table ), ARRAY_A );
		if ( ! empty( $settings ) && $settings['who_can_like'] === 'members' && ! is_user_logged_in() ) {
			wp_die();
		}

		if ( ! empty( $settings ) && $settings['rate_limit_enabled'] === 'yes' && ! plb_check_rate_limit() ) {
			wp_die();
		}

		if ( ! empty( $settings ) && ! plb_verify_recaptcha( $settings['recaptcha_version'], $settings['recaptcha_secret_key'] ) ) {
			wp_die();
		}

		// Off by default. The "max one vote per IP" hard cap rides on this same
		// toggle — it's the part of rate-limiting that can affect a shared
		// office/NAT IP, so it shouldn't turn on silently for sites that never
		// opted into anti-spam at all.
		$rate_limiting_on = ! empty( $settings ) && $settings['rate_limit_enabled'] === 'yes';

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
				$wpdb->prepare( "SELECT counter_like, counter_dislike FROM %i WHERE %i = %d", $table_name_post, $id_column, $postid ),
				ARRAY_A
			);
			$var_like = isset( $carently_likes_likes['counter_like'] ) ? (int) $carently_likes_likes['counter_like'] : 0;
			// check is there or not cookies, or this IP already voted on this item

			if ( isset( $_COOKIE[ $cookie_key . $postid ] ) || ( $rate_limiting_on && plb_ip_already_voted( $type, $postid ) ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE %i SET counter_like = %d WHERE %i = %d", $table_name_post, $var_like, $id_column, $postid ) );
				echo $var_like;
				wp_die();
			}
			setcookie( $cookie_key . '_like' . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// add like database and frontend
			$wpdb->query( $wpdb->prepare( "UPDATE %i SET counter_like = %d WHERE %i = %d", $table_name_post, $var_like + 1, $id_column, $postid ) );
			plb_log_vote( $type, $postid, 'like' );
			echo $var_like + 1;

			wp_die();
		}

		if ( isset( $_POST['unliked'] ) ) {
			// save cookies
			setcookie( $cookie_key . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// counter like post
			$carently_likes_dislikes = $wpdb->get_row(
				$wpdb->prepare( "SELECT counter_like, counter_dislike FROM %i WHERE %i = %d", $table_name_post, $id_column, $postid ),
				ARRAY_A
			);
			$var_dislike = isset( $carently_likes_dislikes['counter_dislike'] ) ? (int) $carently_likes_dislikes['counter_dislike'] : 0;

			// check is there or not cookies, or this IP already voted on this item
			if ( isset( $_COOKIE[ $cookie_key . $postid ] ) || ( $rate_limiting_on && plb_ip_already_voted( $type, $postid ) ) ) {
				$wpdb->query( $wpdb->prepare( "UPDATE %i SET counter_dislike = %d WHERE %i = %d", $table_name_post, $var_dislike, $id_column, $postid ) );
				echo $var_dislike;
				wp_die();
			}
			setcookie( $cookie_key . '_dislike' . $postid, '1', time() + 62208000, '/', $_SERVER['HTTP_HOST'] );

			// add like database and frontend
			$wpdb->query( $wpdb->prepare( "UPDATE %i SET counter_dislike = %d WHERE %i = %d", $table_name_post, $var_dislike - 1, $id_column, $postid ) );
			plb_log_vote( $type, $postid, 'dislike' );
			echo $var_dislike - 1;

			wp_die();
		}

		wp_die();
	}
