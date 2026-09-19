<?php
/**
 * @package ProLike Button
 * @version 1.0.4
 */
/*
Plugin Name: Pro Like Button
Plugin URI: 
Description: Like and dislike each post
Author: Andriy Prots
Version: 1.0.4
Author URI: https://github.com/ProtSport
*/


global $wpdb,$plb_bac_image,$plb_bac_image_dis,$plb_likebtn_styles;

register_activation_hook( __FILE__, 'plb_prolike_install' );
	function plb_prolike_install() {
}

register_uninstall_hook(__FILE__, 'plb_prolike_unistall');
function plb_prolike_unistall() {
    global $wpdb;
	$table = $wpdb->prefix . 'prolike';
    $sql = "DROP TABLE IF EXISTS $table;";
    $wpdb->query($sql);
}

register_deactivation_hook( __FILE__, 'plb_prolike_deactivate' );
function plb_prolike_deactivate(){
	global $wpdb;
	$table = $wpdb->prefix . 'prolike';
	$wpdb->delete( $table, array( 'ID' => 1 ) );
}


	/*
	=======================================================
		Create a settings page for the plugin
	=======================================================
	*/
	function plb_like_add_admin_page(){
		global $wpdb;
	    $table = $wpdb->prefix . 'prolike';
	    $myrows = $wpdb->get_results("SELECT * FROM $table WHERE id = 1");

		add_menu_page(
			'ProLike Button',
			'ProLike Button',
			'manage_options',
			'prolike_first_plugin',
			'plb_plugin_setting_page', 
			plugins_url( '/assets/img/thumbs-up-hand-symbol.png', __FILE__ ),
			90
		);
		add_submenu_page( 'prolike_first_plugin', 'General', 'General', 'manage_options', 'prolike_first_plugin', 'plb_like_genneral_page' );
	}
	add_action('admin_menu','plb_like_add_admin_page');

	// By attached the function to the page ProLikePlugin

	function plb_custom_setting(){
		register_setting( 'plb-image-setting', 'plb_background-image-field-like');
		register_setting( 'plb-image-setting', 'plb_background-image-field-dislike');
		register_setting( 'plb-image-setting', 'plb_your_style_css' , 'plb_sanitize_custom_css' );
		add_settings_section( 'plb-sidebar-options', 'CSS', 'plb_custom_css_section_callback', 'plb_image_css_subpage' );
		add_settings_section( 'plb-sidebar-image', 'Image', 'plb_sidebar_options', 'plb_image_css_subpage' );
		add_settings_field( 'plb_background-image_like', 'Picture of the Like button', 'plb_background_image_like', 'plb_image_css_subpage', 'plb-sidebar-image');
		add_settings_field( 'plb_background-image_dislike', 'Picture of the Dislike button', 'plb_background_image_dislike', 'plb_image_css_subpage', 'plb-sidebar-image');
		add_settings_field( 'plb_your-style', 'Your style', 'plb_your_style', 'plb_image_css_subpage', 'plb-sidebar-options');
	}

	function plb_your_style() {
		$css = get_option( 'plb_your_style_css' );
		$css = ( empty($css) ? '/* ProLike Button Custom CSS */' : $css );
		echo '<div id="customCss">'.$css.'</div><textarea id="plb_your_style_css" name="plb_your_style_css" style="display:none;visibility:hidden;">'.$css.'</textarea>';
	}

	function plb_like_genneral_page(){};
	function plb_sidebar_options(){};
	function plb_custom_css_section_callback(){};

	function plb_background_image_like(){
					$plb_bac_image = esc_attr(get_option('plb_background-image-field-like'));
					if( empty(get_option('plb_background-image-field-like'))){
						echo "<img class='wrapp_image_admin_like' src='".$plb_bac_image."'><br><input type='button' value='Upload Image' id='upload-button_like' class='upload_button'><input type='hidden' id='upload_image_like' value='". $plb_bac_image ."' name='plb_background-image-field-like'>";
					}
					else{
						echo "<img class='wrapp_image_admin_like' src='".$plb_bac_image."'><br><input type='button' value='Change Image' id='upload-button_like' class='upload_button'><input type='hidden' id='upload_image_like' value='". $plb_bac_image ."' name='plb_background-image-field-like'><input type='button' value='Remove' id='remove_button_like'>";	
					}

	}

	function plb_background_image_dislike(){
					$plb_bac_image_dis = esc_attr(get_option('plb_background-image-field-dislike'));
					if( empty(get_option('plb_background-image-field-dislike'))){
						echo "<img class='wrapp_image_admin_dislike' src='".$plb_bac_image_dis."'><br><input type='button' value='Upload Image' id='upload-button_dislike' class='upload_button'><input type='hidden' id='upload_image_dislike' value='". $plb_bac_image_dis ."' name='plb_background-image-field-dislike'>";
					}
					else{
						echo "<img class='wrapp_image_admin_dislike' src='".$plb_bac_image_dis."'><br><input type='button' value='Change Image' id='upload-button_dislike' class='upload_button'><input type='hidden' id='upload_image_dislike' value='". $plb_bac_image_dis ."' name='plb_background-image-field-dislike'><input type='button' value='Remove' id='remove_button_dislike'>";	
					}
	}


	// Created database
	function plb_created_database() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'prolike';
		if($wpdb->get_var('$prolike') != $table_name) {
				$sql = "CREATE TABLE " . $table_name . " (
	             id mediumint(9) NOT NULL AUTO_INCREMENT,
	             display int,
	             layout varchar (50),
	             view varchar (50),
	             position varchar (50),
	             btn_size varchar (50),
	             text_like text NOT NULL COLLATE utf8_general_ci,
	             text_dislike text NOT NULL COLLATE utf8_general_ci,
	             beforeafter varchar (25),
	             output_shortcode varchar (50),
	             imagelike text NOT NULL COLLATE utf8_general_ci,
	             imagedislike text NOT NULL COLLATE utf8_general_ci,
	             user_id int,
                 active int,
                 created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                 last_modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	             UNIQUE KEY id (id)
	          );";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}
	register_activation_hook(__FILE__, 'plb_created_database');


	// Post new row
	function plb_create_row(){
		global $wpdb;
	    $table_name_post = $wpdb->prefix . 'posts';
	  	// $wpdb->query('ALTER TABLE' .$table_name_post. 'ADD counter_like');
	  	$wpdb->query("ALTER TABLE $table_name_post ADD counter_like INT(1) NOT NULL DEFAULT 0");
	  	$wpdb->query("ALTER TABLE $table_name_post ADD counter_dislike INT(1) NOT NULL DEFAULT 0");

	};
	register_activation_hook(__FILE__, 'plb_create_row');


	// Start date, when activated plugin
	function plb_install_data() {
	    global $wpdb;
	    $table_name = $wpdb->prefix . 'prolike';
	    $myrows = $wpdb->get_results("SELECT * FROM $table_name WHERE id = 1");

	    if ($myrows == NULL) {
	        $wpdb->insert($table_name, array(
	        	'id' => 1,
	            'created' => current_time('mysql'),
	            'last_modified' => current_time('mysql'),
	            'display' => 1,
	            'layout' => 'with',
	            'position' => 'left',
	            'btn_size' => 'small',
	            'text_like' => 'Like',
	            'text_dislike' => 'Dislike',
	            'beforeafter' => 'before',
	            'view' => 'white',
	            'output_shortcode' => 'no',
	            'active' => 1,
	            'user_id' => $user_id    
	            )
	        );
	    }
	}
	register_activation_hook(__FILE__, 'plb_install_data');


	// Action save setting button
	if (isset($_POST['update_prolike'])) {
	    global $wpdb, $plb_bac_image,$plb_bac_image_dis;
	    $plb_bac_image = esc_attr(get_option('plb_background-image-field-like'));
	    $plb_bac_image_dis = esc_attr(get_option('plb_background-image-field-dislike'));
	  
	    $display = $_REQUEST['display'];
	    $display_val = 0;
	    foreach ($display as $d) {
	        $display_val += sanitize_text_field($d);
	    }
	    
	   
	   	$layout = sanitize_text_field($_REQUEST['layout']);
	    $position = sanitize_text_field($_REQUEST['position']);
	    $btn_size = sanitize_text_field($_REQUEST['btn_size']);
	    $text_like = sanitize_text_field($_REQUEST['text_like']);
	    $text_dislike = sanitize_text_field($_REQUEST['text_dislike']);
	    $view = sanitize_text_field($_REQUEST['view']);
	    $beforeafter = sanitize_text_field($_REQUEST['beforeafter']);
	    $output_shortcode = sanitize_text_field($_REQUEST['output_shortcode']);
	    $imagelike = sanitize_text_field($_REQUEST['imagelike']);
	    $imagedislike = sanitize_text_field($_REQUEST['imagedislike']);
	    $table = $wpdb->prefix . 'prolike';
	    $data1 = array(
	        'display' => $display_val,
	        'layout' => $layout,
	        'view' => $view,
	        'position' => $position,
	        'btn_size' => $btn_size,
	        'text_like' => $text_like,
	        'text_dislike' => $text_dislike,
	        'text_like' => $text_like,
	        'imagelike' => $plb_bac_image,
	        'imagedislike' => $plb_bac_image_dis,
	        'beforeafter' => $beforeafter,
	        'output_shortcode' => $output_shortcode,
	        'last_modified' => current_time('mysql')
	    );
	   	$wpdb->update($table, $data1, array('id' => 1));
	  
	}


	// function option admin plugin
	function plb_plugin_setting_page(){
		global $wpdb;
	    $table_name = $wpdb->prefix . 'prolike';
	    $myrows = $wpdb->get_results("SELECT * FROM $table_name WHERE id = 1");
	    $data_array = array();
		    if ($myrows[0]->display & 1) {
		        $display[1] = 'checked';
		    };
		    if ($myrows[0]->display & 2) {
		        $display[2] = 'checked';
		    };
		    if ($myrows[0]->display & 4) {
		        $display[4] = 'checked';
		    };
		  
		    if ($myrows[0]->display & 16) {
		        $display[16] = 'checked';
		    };


		    $likebtn_styles = array(
		    'white',
		    'lightgray',
		    'black',
		    'smile',
		    'colorfull',
		    'check',
		    'updown',
		    'smilemodern',
		    'ok',
		    'heart',
			);
	    $display[$myrows[0]->display] = ' checked';
	    $btn_size[$myrows[0]->btn_size] = ' selected="selected"';
		$layout[$myrows[0]->layout] = ' checked';
		$output_shortcode[$myrows[0]->output_shortcode] = ' checked';
		$view[$myrows[0]->view] = ' selected="selected"';
		$beforeafter[$myrows[0]->beforeafter] = ' selected="selected"';
		$position[$myrows[0]->position] = ' selected="selected"';
	 ?>
	 <div class="prolikewrapp">
				
			<?php settings_errors(); 
		
	    	$table = $wpdb->prefix . 'prolike';
			$currently_view = $wpdb->get_row("SELECT view FROM $table");

			foreach ($currently_view as  $value) {
				$name_currently_view = $value;
			}

			$counter_image = 1;

			 ?>
			<div class="tabs_button">
				<div class="tab_button active">
					General setting
				</div>
				<div class="tab_button">
					Custom
				</div>
				
			</div>
			<div class="tabs_wrapper">
				<div class="tab_content active">
					<form method="post" class="wrapp_form_prolike" >
					

						<h3><?php _e( 'Where to display?', 'prolikebutton' ); ?></h3>
						<div class="prolike_display">
							<input type="checkbox" id="prolike_display1" name="display[]" <?php echo @$display['1']; ?> value="1">
							<label for="prolike_display1"><?php _e('Homepage', 'prolike');?></label><br>
							<input type="checkbox" id="prolike_display2" name="display[]" <?php echo @$display['2']; ?> value="2">
							<label for="prolike_display2"><?php _e('All pages except the main page', 'prolike');?></label><br>
							<input type="checkbox" id="prolike_display3" name="display[]" <?php echo @$display['4']; ?> value="4">
							<label for="prolike_display3"><?php _e('Single page', 'prolike');?></label><br>
							<input type="checkbox" id="prolike_display4" name="display[]" <?php echo @$display['16']; ?> value="16">
							<label for="prolike_display4"><?php _e('Archive page', 'prolike');?></label><br>
						</div>
						<div class="prolike_form-group">
							<h3><?php _e('Layout page', 'prolike');?></h3>  
	                        <div class="like_form-control" name="layout" id="layout">
	                        	<input type="radio" name="layout" <?php echo @$layout['with']; ?> value="with"> <?php _e('With', 'prolike');?><br>
	                        	<input type="radio" name="layout" <?php echo @$layout['without']; ?> value="without"> <?php _e('Without', 'prolike');?><br>
								<input type="radio" name="layout" <?php echo @$layout['withoutlike']; ?> value="withoutlike"> <?php _e('Without Like ', 'prolike');?><br>
								<input type="radio" name="layout" <?php echo @$layout['withoutdislike']; ?> value="withoutdislike"> <?php _e('Without Dislike', 'prolike');?><br>
	                        </div>
	                    </div>
	                     <div class="prolike_form-group">
							<h3><?php _e( 'View', 'prolikebutton' ); ?></h3>
	                        <select class="like_form-control like_form_select_image" name="view" id="view">
	                           <?php foreach ($likebtn_styles as $style): ?>
                                    <option <?php echo @$view[$style]; ?> value="<?php echo $style; ?>"><?php echo $style; ?></option>
                                <?php endforeach ?>
	                           
	                        </select>
	                        <div class="dropdown_view">
		                    	<div class="drop_down_image">
									<a class="dropdown-toggle" href="javascript:;" title="Menu">
										<?php 
											$carently_like_text = $wpdb->get_row("SELECT view FROM $table", ARRAY_A);

											if($carently_like_text['view'] === 'white'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image1.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'lightgray'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image2.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'black'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image3.png', __FILE__ );?>" alt="">
											<?php }
											else if($carently_like_text['view'] === 'smile'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image4.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'colorfull'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image5.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'check'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image6.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'updown'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image7.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'smilemodern'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image8.png', __FILE__ );?>" alt="">
											<?php  }
											else if($carently_like_text['view'] === 'ok'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image9.png', __FILE__ );?>" alt="">
											<?php  } 
											else if($carently_like_text['view'] === 'heart'){ ?>
												<img src="<?php echo plugins_url('/assets/img/icon_button/image10.png', __FILE__ );?>" alt="">
											<?php } 
										?>
										
									</a>
									<ul class="dropdown js-dropdown">
										<?php $counter_number = 1;  ?>
									  	<?php foreach ($likebtn_styles as $style): ?>
									  		
									  		 
			                    		<li data-number="<?php echo $counter_number; ?>"><img src="<?php echo plugins_url('/assets/img/icon_button/image'. $counter_image .'.png' , __FILE__ );?>"></li>
			                    		<?php $counter_number++; $counter_image++; ?>
			                    		<?php endforeach ?>
									</ul>
								</div>
	                    	</div>
	                    </div>
	                    <div class="prolike_form-group">
							<h3><?php _e( 'Post like text', 'prolikebutton' ); ?></h3>
	                        <input type="text" <?php echo @$text_like['text_like']; ?> placeholder="Like" value="<?php echo($myrows[0]->text_like);?>" name="text_like">
	                        <input type="text" <?php echo @$text_dislike['text_dislike']; ?> placeholder="Dislike"  value="<?php echo($myrows[0]->text_dislike);?>" name="text_dislike">
	                    </div>
	                   
	                    <div class="prolike_form-group">
							<h3><?php _e( 'Size', 'prolikebutton' ); ?></h3>
	                        <select class="like_form-control" name="btn_size" id="size">
	                            <option <?php echo @$btn_size['small']; ?> value="small"><?php _e( 'Small', 'prolikebutton' ); ?></option>
	                            <option <?php echo @$btn_size['medium']; ?> value="medium"><?php _e( 'Medium', 'prolikebutton' ); ?></option>
	                            <option <?php echo @$btn_size['big']; ?> value="big"><?php _e( 'Big', 'prolikebutton' ); ?></option>
	                        </select>
	                    </div>
	                    
					
	                	 <div class="prolike_form-group">
							<h3><?php _e( 'Position', 'prolikebutton' ); ?></h3>
	                        <select class="like_form-control" name="position" id="position">
	                            <option <?php echo @$position['left']; ?> value="left"><?php _e( 'Left', 'prolikebutton' ); ?></option>
	                            <option <?php echo @$position['right']; ?> value="right"><?php _e( 'Right', 'prolikebutton' ); ?></option>
	                            <option <?php echo @$position['center']; ?> value="center"><?php _e( 'Center', 'prolikebutton' ); ?></option>
	                        </select>
	                    </div>
	                    <div class="prolike_form-group">
							<h3><?php _e( 'Before or after posts buttons', 'prolikebutton' ); ?></h3>
	                        <select class="like_form-control" name="beforeafter" id="beforeafter">
	                            <option <?php echo @$beforeafter['before']; ?> value="before"><?php _e( 'Before', 'prolikebutton' ); ?></option>
	                            <option <?php echo @$beforeafter['after']; ?> value="after"><?php _e( 'After', 'prolikebutton' ); ?></option>
	                        </select>
	                    </div>
	                    <div class="prolike_form-group">
							<h3><?php _e( 'Output Like&Dislike Button shortcode', 'prolikebutton' ); ?></h3>
							 <div class="like_form-control" name="output_shortcode" id="output_shortcode">
	                        	<input type="radio" name="output_shortcode" <?php echo @$output_shortcode['yes']; ?> value="yes"><?php _e( 'Yes', 'prolikebutton' ); ?> <br>
	                        	<input type="radio" name="output_shortcode" <?php echo @$output_shortcode['no']; ?> value="no"><?php _e( 'No', 'prolikebutton' ); ?> <br>
	                        </div>
						</div>

	                    
						

						<button type="submit" name="update_prolike" class="button-general-save button button-primary">Save Settings</button>
					</form>
				</div>
				<div class="tab_content">
					
					<form id="save-custom-css-form" method="post" action="options.php" class="sunset-general-form">
						<?php settings_fields('plb-image-setting'); ?>
						<?php do_settings_sections('plb_image_css_subpage'); ?>
						
						<?php submit_button('Save Changes', 'primary','btnSubmit'); ?>
					</form>

				</div>
			</div>
		</div>
		<?php
	}
	add_action('admin_init','plb_custom_setting');



	// POST, counter-likes, filter the_content
	require_once('front-likes.php');
	require_once('admin_post_field.php');
	require_once('include/enqueue-front.php');
	require_once('include/enqueue-admin.php');