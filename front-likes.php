<?php 	function plb_pro_like_button($content = NULL) {
		
	   
	    global $wpdb, $plb_bac_image,$plb_bac_image_dis;
	    $table = $wpdb->prefix . 'prolike';
	    $table_name_post = $wpdb->prefix . 'posts';
	    $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE id = 1", $table ) );
	    if (empty($myrows)) {
	    	return $content;
	    }
	    $current_like_ID = get_the_ID();
	    $current_user = wp_get_current_user();
		$usr_id=$current_user->ID;

	    $myrows_id = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i", $table_name_post ) );
		$carently_likes_dislikes = $wpdb->get_row( $wpdb->prepare( "SELECT counter_like, counter_dislike FROM %i WHERE id = %d", $table_name_post, $current_like_ID ), ARRAY_A );
		$carently_like_text = $wpdb->get_row( $wpdb->prepare( "SELECT layout,position,text_like,text_dislike,btn_size,imagelike,imagedislike,output_shortcode FROM %i", $table ), ARRAY_A );


		$select_view = $wpdb->get_row( $wpdb->prepare( "SELECT view FROM %i", $table ), ARRAY_A );

		$cookie_ip_like = $_SERVER['REMOTE_ADDR'];
		$cookie_ip_dislike = $_SERVER['REMOTE_ADDR'];

		$user_alredy_like = '';
		$user_alredy_dislike = '';
		
		if(isset($_COOKIE["cookie_ip_like".$current_like_ID])){
			$user_alredy_like = 'active_like';
		}
		if(isset($_COOKIE["cookie_ip_dislike".$current_like_ID])){
			$user_alredy_dislike = 'active_dislike';
		}

	    $plb_bac_image = esc_attr(get_option('background-image-field-like'));
	    $plb_bac_image_dis = esc_attr(get_option('background-image-field-dislike'));
		
	    $who_can_like = $myrows[0]->who_can_like;
	    $can_user_like = ($who_can_like !== 'members') || is_user_logged_in();

	    if (!$can_user_like) {
	    	$login_url = esc_url( wp_login_url( get_permalink() ) );
	    	$like_dis_button = "<div class='wrapp_like_buttons prolike-locked ". esc_html($carently_like_text['position']) ."'>
	    		<a href='".$login_url."' class='prolike-login-prompt'>".esc_html__('Log in to like', 'prolike-button')."</a>
	    	</div>";
	    }
	    else if($plb_bac_image != '' || $plb_bac_image_dis != ''){



			$like_dis_button = "<div class='wrapp_like_buttons ". esc_html($carently_like_text['position']) ."'>
				<div class='wrapp_like custom_like user-". esc_html($user_alredy_like)." prolike-".esc_html($carently_like_text['btn_size'])."' data-id='".get_the_ID()."'>
					<img src=". esc_html($plb_bac_image) ." alt='like_button'>
					".esc_html($carently_like_text['text_like'])."
					<span class='likes_count_like ". esc_html($carently_like_text['position']) ."'>". esc_html($carently_likes_dislikes['counter_like']) ."</span>
				</div>
				<div class='wrapp_like custom_like user-". esc_html($user_alredy_dislike)." prolike-".esc_html($carently_like_text['btn_size'])."' data-id='".get_the_ID()."'>
					<img src=". esc_html($plb_bac_image_dis) ." alt='like_button'>
					".esc_html($carently_like_text['text_dislike'])."
					<span class='likes_count_dislike ". esc_html($carently_like_text['position']) ."'>". esc_html($carently_likes_dislikes['counter_dislike']) ."</span>
				</div>
			</div>";
		    }
		    else{
				$like_dis_button = "<div class='wrapp_like_buttons ". esc_html($carently_like_text['position']) ."'>
					<div class='wrapp_like user-". esc_html($user_alredy_like)." like prolike-view-".esc_html($select_view['view'])." prolike-".esc_html($carently_like_text['btn_size'])."' data-id='".get_the_ID()."'>".esc_html($carently_like_text['text_like'])."
								<span class='likes_count_like ". esc_html($carently_like_text['position']) ."'>". esc_html($carently_likes_dislikes['counter_like']) ."</span>
					</div>
					<div class='wrapp_like user-". esc_html($user_alredy_dislike)." unlike prolike-view-".esc_html($select_view['view'])." prolike-".esc_html($carently_like_text['btn_size'])."' data-id='".get_the_ID()."'>".esc_html($carently_like_text['text_dislike'])."
						<span class='likes_count_dislike ". esc_html($carently_like_text['position']) ."'>". esc_html($carently_likes_dislikes['counter_dislike']) ."</span>
					</div>
				</div>";
	    	}


	    $beforeafter = $myrows[0]->beforeafter;
	    $display = $myrows[0]->display;

	    $should_display = false;
	    if (($display & 1) && is_front_page()) {
	        $should_display = true;
	    }
	    if (($display & 2) && is_page() && !is_front_page()) {
	        $should_display = true;
	    }
	    if (($display & 4) && is_single()) {
	        $should_display = true;
	    }
	    if (($display & 16) && is_archive()) {
	        $should_display = true;
	    }
	    // function_exists() guard: is_product() only exists when WooCommerce is
	    // active, so sites without it never touch a missing-function fatal.
	    if (($display & 32) && function_exists('is_product') && is_product()) {
	        $should_display = true;
	    }

	    if ($should_display) {
	        if ($beforeafter == 'before') {
	            $content = $like_dis_button . $content;
	        } else {
	            $content = $content . $like_dis_button;
	        }
	    }

	    return $content;
	}


	// Append like/dislike buttons under each comment when the "Comments" location is enabled
	add_filter('comment_text', 'plb_pro_like_comment_button', 10, 2);
	function plb_pro_like_comment_button($comment_text, $comment = null) {
		global $wpdb;

		if (empty($comment)) {
			return $comment_text;
		}

		$table = $wpdb->prefix . 'prolike';
		$myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE id = 1", $table ) );

		if (empty($myrows) || !($myrows[0]->display & 8)) {
			return $comment_text;
		}

		$comment_id = (int) $comment->comment_ID;
		$who_can_like = $myrows[0]->who_can_like;
		$can_user_like = ($who_can_like !== 'members') || is_user_logged_in();

		if (!$can_user_like) {
			$login_url = esc_url( wp_login_url( get_permalink() ) );
			$buttons = "<div class='wrapp_like_buttons prolike-locked prolike-comment ". esc_html($myrows[0]->position) ."'>
				<a href='".$login_url."' class='prolike-login-prompt'>".esc_html__('Log in to like', 'prolike-button')."</a>
			</div>";
			return $comment_text . $buttons;
		}

		$comments_table = $wpdb->prefix . 'comments';
		$counts = $wpdb->get_row( $wpdb->prepare( "SELECT counter_like, counter_dislike FROM %i WHERE comment_ID = %d", $comments_table, $comment_id ), ARRAY_A );

		$user_alredy_like = isset($_COOKIE['cookie_ip_comment_like'.$comment_id]) ? 'active_like' : '';
		$user_alredy_dislike = isset($_COOKIE['cookie_ip_comment_dislike'.$comment_id]) ? 'active_dislike' : '';

		$buttons = "<div class='wrapp_like_buttons prolike-comment ". esc_html($myrows[0]->position) ."'>
			<div class='wrapp_like user-". esc_html($user_alredy_like)." like prolike-view-".esc_html($myrows[0]->view)." prolike-".esc_html($myrows[0]->btn_size)."' data-id='".$comment_id."' data-type='comment'>".esc_html($myrows[0]->text_like)."
				<span class='likes_count_like'>". esc_html($counts['counter_like']) ."</span>
			</div>
			<div class='wrapp_like user-". esc_html($user_alredy_dislike)." unlike prolike-view-".esc_html($myrows[0]->view)." prolike-".esc_html($myrows[0]->btn_size)."' data-id='".$comment_id."' data-type='comment'>".esc_html($myrows[0]->text_dislike)."
				<span class='likes_count_dislike'>". esc_html($counts['counter_dislike']) ."</span>
			</div>
		</div>";

		return $comment_text . $buttons;
	}

		global $wpdb;
	  	$table = $wpdb->prefix . 'prolike';
		$output_shortcode_ifno = $wpdb->get_row( $wpdb->prepare( "SELECT output_shortcode FROM %i", $table ), ARRAY_A );

		// The settings row may not exist yet (e.g. right after activation, before the
		// activation hooks that create it have run) — fall back to the default instead
		// of dereferencing a null result.
		$output_shortcode_setting = ! empty( $output_shortcode_ifno['output_shortcode'] ) ? $output_shortcode_ifno['output_shortcode'] : 'no';

		if ($output_shortcode_setting === 'no'){
			add_filter('the_content', 'plb_pro_like_button');
		}
		else{
			add_shortcode('prolikebutton_shortcode', 'plb_pro_like_button_shortcode');
		}

	// Shortcode callbacks receive an attributes array as the first argument, unlike the
	// the_content filter above (which receives the post content string). Using the same
	// function for both made $content an array when called via the shortcode, producing
	// a PHP "Array to string conversion" warning and the literal text "Array" in the output.
	function plb_pro_like_button_shortcode($atts = array(), $content = '', $tag = '') {
		return plb_pro_like_button('');
	}