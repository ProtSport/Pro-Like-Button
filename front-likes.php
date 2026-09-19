<?php 	function plb_pro_like_button($content = NULL) {
		
	   
	    global $wpdb, $plb_bac_image,$plb_bac_image_dis;
	    $table = $wpdb->prefix . 'prolike';
	    $table_name_post = $wpdb->prefix . 'posts';
	    $myrows = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");
	    $current_like_ID = get_the_ID();
	    $current_user = wp_get_current_user();
		$usr_id=$current_user->ID;

	    $myrows_id = $wpdb->get_results("SELECT * FROM $table_name_post" );
		$carently_likes_dislikes = $wpdb->get_row("SELECT counter_like, counter_dislike FROM $table_name_post WHERE id = $current_like_ID", ARRAY_A);
		$carently_like_text = $wpdb->get_row("SELECT layout,position,text_like,text_dislike,btn_size,imagelike,imagedislike,output_shortcode FROM $table", ARRAY_A);
		

		$select_view = $wpdb->get_row("SELECT view FROM $table", ARRAY_A);

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
		
	    if($plb_bac_image != '' || $plb_bac_image_dis != ''){
		
	
		  	
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

	   
	    	if ($display == 1) {
	            if (is_front_page()) {
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	                    
	                } else {
	                    $content = $content . $like_dis_button;
	                   
	                }
	            }
	            else if(is_page() || is_single() || is_archive()){
	            	return $content;
	            }
	        }
	        if ($display == 2) {
	            if (is_page() && !is_front_page()) {
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	             
	                } else {
	                    $content = $content . $like_dis_button;
	                 
	                }
	            }
	        }
	        if ($display == 3) {
	           
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	             
	                } else {
	                    $content = $content . $like_dis_button;
	                 
	                }
	            
	        }
	        if ($display == 4) {
	            if (is_single()) {
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	                 
	                } else {
	                    $content = $content . $like_dis_button;
	              
	                }
	            }
	        }
	        if ($display == 5) {
	         	if (is_single() || is_front_page()) {
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	                    
	                } else {
	                    $content = $content . $like_dis_button;
	                   
	                }
	            }
	            
	            else if(is_page()  || is_archive()){
	            	return $content;
	            	}
	        	
	        }
	        if ($display == 7) {
	         	if (is_single() || is_front_page() || is_page()) {
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	                    
	                } else {
	                    $content = $content . $like_dis_button;              
	                }
	            }
	            else if(is_archive()){
	            	return $content;
	            }
	        }
	        if ($display == 16 ) {
	            if (is_archive()) {
	                if ($beforeafter == 'before') {
	                    $content = $like_dis_button . $content;
	                
	                } else {
	                    $content = $content . $like_dis_button;
	               
	                }
	            }
	        }
	        return $content;
	    }  
	     
		global $wpdb;
	  	$table = $wpdb->prefix . 'prolike';
		$output_shortcode_ifno = $wpdb->get_row("SELECT output_shortcode FROM $table", ARRAY_A);
	
		if($output_shortcode_ifno['output_shortcode'] == 'no'){
			add_filter('the_content', 'plb_pro_like_button');
		} 
		else{
			add_shortcode('prolikebutton_shortcode', 'plb_pro_like_button');
		}