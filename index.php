<?php
/**
 * @package ProLike Button
 * @version 2.0
 */
/*
Plugin Name: Pro Like Button
Plugin URI:
Description: Like and dislike each post
Author: Andriy Prots
Version: 2.0
Author URI: https://github.com/ProtSport
Text Domain: prolike-button
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb,$plb_bac_image,$plb_bac_image_dis,$plb_likebtn_styles;

register_activation_hook( __FILE__, 'plb_prolike_install' );
	function plb_prolike_install() {
}

register_uninstall_hook(__FILE__, 'plb_prolike_unistall');
function plb_prolike_unistall() {
    global $wpdb;
	$table = $wpdb->prefix . 'prolike';
    $wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %i", $table ) );
    $votes_table = $wpdb->prefix . 'prolike_votes';
    $wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %i", $votes_table ) );
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
	    $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE id = 1", $table ) );

		// WordPress derives each submenu's hook suffix from the parent's menu
		// TITLE (not its slug), so a substring check against the slug misses
		// pages like Statistics. Collect the real hook suffixes here instead.
		$hooks = array();
		$hooks[] = add_menu_page(
			'ProLike Button',
			'ProLike Button',
			'manage_options',
			'prolike_first_plugin',
			'plb_plugin_setting_page',
			plugins_url( '/assets/img/thumbs-up-hand-symbol.png', __FILE__ ),
			90
		);
		$hooks[] = add_submenu_page( 'prolike_first_plugin', 'General', 'General', 'manage_options', 'prolike_first_plugin', 'plb_like_genneral_page' );
		$hooks[] = add_submenu_page( 'prolike_first_plugin', 'Statistics', 'Statistics', 'manage_options', 'prolike_stats', 'plb_stats_page' );

		$GLOBALS['plb_admin_page_hooks'] = array_filter( $hooks );
	}
	add_action('admin_menu','plb_like_add_admin_page');

	// By attached the function to the page ProLikePlugin

	function plb_custom_setting(){
		register_setting( 'plb-image-setting', 'plb_background-image-field-like', array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( 'plb-image-setting', 'plb_background-image-field-dislike', array( 'sanitize_callback' => 'esc_url_raw' ) );
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
		echo '<div id="customCss">' . esc_html( $css ) . '</div><textarea id="plb_your_style_css" name="plb_your_style_css" style="display:none;visibility:hidden;">' . esc_textarea( $css ) . '</textarea>';
	}

	function plb_like_genneral_page(){};
	function plb_sidebar_options(){};
	function plb_custom_css_section_callback(){};

	function plb_background_image_like(){
					$plb_bac_image = esc_attr(get_option('plb_background-image-field-like'));
					if( empty(get_option('plb_background-image-field-like'))){
						echo "<img class='wrapp_image_admin_like' src='" . esc_attr( $plb_bac_image ) . "'><br><input type='button' value='Upload Image' id='upload-button_like' class='upload_button'><input type='hidden' id='upload_image_like' value='" . esc_attr( $plb_bac_image ) . "' name='plb_background-image-field-like'>";
					}
					else{
						echo "<img class='wrapp_image_admin_like' src='" . esc_attr( $plb_bac_image ) . "'><br><input type='button' value='Change Image' id='upload-button_like' class='upload_button'><input type='hidden' id='upload_image_like' value='" . esc_attr( $plb_bac_image ) . "' name='plb_background-image-field-like'><input type='button' value='Remove' id='remove_button_like'>";
					}

	}

	function plb_background_image_dislike(){
					$plb_bac_image_dis = esc_attr(get_option('plb_background-image-field-dislike'));
					if( empty(get_option('plb_background-image-field-dislike'))){
						echo "<img class='wrapp_image_admin_dislike' src='" . esc_attr( $plb_bac_image_dis ) . "'><br><input type='button' value='Upload Image' id='upload-button_dislike' class='upload_button'><input type='hidden' id='upload_image_dislike' value='" . esc_attr( $plb_bac_image_dis ) . "' name='plb_background-image-field-dislike'>";
					}
					else{
						echo "<img class='wrapp_image_admin_dislike' src='" . esc_attr( $plb_bac_image_dis ) . "'><br><input type='button' value='Change Image' id='upload-button_dislike' class='upload_button'><input type='hidden' id='upload_image_dislike' value='" . esc_attr( $plb_bac_image_dis ) . "' name='plb_background-image-field-dislike'><input type='button' value='Remove' id='remove_button_dislike'>";
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
	             who_can_like varchar (20) DEFAULT 'anyone',
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


	// Upgrade existing installs: add columns/tables introduced after the initial release
	define('PLB_DB_VERSION', '1.4');

	function plb_column_exists($table, $column){
		global $wpdb;
		$result = $wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM %i LIKE %s", $table, $column));
		return !empty($result);
	}

	function plb_maybe_upgrade_db(){
		if (version_compare(get_option('plb_db_version', '1.0'), PLB_DB_VERSION, '>=')) {
			return;
		}

		global $wpdb;
		$prolike_table = $wpdb->prefix . 'prolike';
		$comments_table = $wpdb->prefix . 'comments';
		$votes_table = $wpdb->prefix . 'prolike_votes';

		if (!plb_column_exists($prolike_table, 'who_can_like')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD who_can_like VARCHAR(20) NOT NULL DEFAULT 'anyone'", $prolike_table ) );
		}
		if (!plb_column_exists($comments_table, 'counter_like')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD counter_like INT(1) NOT NULL DEFAULT 0", $comments_table ) );
		}
		if (!plb_column_exists($comments_table, 'counter_dislike')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD counter_dislike INT(1) NOT NULL DEFAULT 0", $comments_table ) );
		}
		if (!plb_column_exists($prolike_table, 'rate_limit_enabled')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD rate_limit_enabled VARCHAR(3) NOT NULL DEFAULT 'no'", $prolike_table ) );
		}
		if (!plb_column_exists($prolike_table, 'recaptcha_version')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD recaptcha_version VARCHAR(10) NOT NULL DEFAULT 'none'", $prolike_table ) );
		}
		if (!plb_column_exists($prolike_table, 'recaptcha_site_key')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD recaptcha_site_key VARCHAR(255) NOT NULL DEFAULT ''", $prolike_table ) );
		}
		if (!plb_column_exists($prolike_table, 'recaptcha_secret_key')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD recaptcha_secret_key VARCHAR(255) NOT NULL DEFAULT ''", $prolike_table ) );
		}
		if (!plb_column_exists($prolike_table, 'schema_output')) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD schema_output VARCHAR(20) NOT NULL DEFAULT 'none'", $prolike_table ) );
		}

		// Per-vote log, used by the Statistics page (today / this-week counts).
		// The aggregate counter columns above stay as the fast read path for
		// display; this table only powers the stats dashboard.
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $votes_table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			object_type VARCHAR(10) NOT NULL,
			object_id BIGINT UNSIGNED NOT NULL,
			vote_type VARCHAR(10) NOT NULL,
			user_id BIGINT UNSIGNED NULL,
			ip_hash VARCHAR(64) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY object_lookup (object_type, object_id),
			KEY created_at (created_at)
		) $charset_collate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		update_option('plb_db_version', PLB_DB_VERSION);
	}
	add_action('plugins_loaded', 'plb_maybe_upgrade_db');


	// Post new row
	function plb_create_row(){
		global $wpdb;
	    $table_name_post = $wpdb->prefix . 'posts';
	  	// $wpdb->query('ALTER TABLE' .$table_name_post. 'ADD counter_like');
	  	$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD counter_like INT(1) NOT NULL DEFAULT 0", $table_name_post ) );
	  	$wpdb->query( $wpdb->prepare( "ALTER TABLE %i ADD counter_dislike INT(1) NOT NULL DEFAULT 0", $table_name_post ) );

	};
	register_activation_hook(__FILE__, 'plb_create_row');


	// Start date, when activated plugin
	function plb_install_data() {
	    global $wpdb;
	    $table_name = $wpdb->prefix . 'prolike';
	    $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE id = 1", $table_name ) );

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
	            'who_can_like' => 'anyone',
	            'rate_limit_enabled' => 'no',
	            'recaptcha_version' => 'none',
	            'recaptcha_site_key' => '',
	            'recaptcha_secret_key' => '',
	            'schema_output' => 'none',
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

	    $display = isset($_REQUEST['display']) ? (array) $_REQUEST['display'] : array();
	    $display_val = 0;
	    foreach ($display as $d) {
	        $display_val += sanitize_text_field($d);
	    }


	   	$layout = sanitize_text_field($_REQUEST['layout'] ?? '');
	    $position = sanitize_text_field($_REQUEST['position'] ?? '');
	    $btn_size = sanitize_text_field($_REQUEST['btn_size'] ?? '');
	    $text_like = sanitize_text_field($_REQUEST['text_like'] ?? '');
	    $text_dislike = sanitize_text_field($_REQUEST['text_dislike'] ?? '');
	    $view = sanitize_text_field($_REQUEST['view'] ?? '');
	    $beforeafter = sanitize_text_field($_REQUEST['beforeafter'] ?? '');
	    $output_shortcode = sanitize_text_field($_REQUEST['output_shortcode'] ?? '');
	    $who_can_like = sanitize_text_field($_REQUEST['who_can_like'] ?? '');
	    // Rate-limiting is a single on/off checkbox — an unchecked box is simply
	    // absent from $_REQUEST, so its presence alone means "enabled".
	    $rate_limit_enabled = isset($_REQUEST['rate_limit_enabled']) ? 'yes' : 'no';
	    // reCAPTCHA is entirely optional: leaving it "none" changes nothing.
	    $recaptcha_version = sanitize_text_field($_REQUEST['recaptcha_version'] ?? 'none');
	    if (!in_array($recaptcha_version, array('none', 'v2', 'v3'), true)) {
	        $recaptcha_version = 'none';
	    }
	    $recaptcha_site_key = sanitize_text_field($_REQUEST['recaptcha_site_key'] ?? '');
	    $recaptcha_secret_key = sanitize_text_field($_REQUEST['recaptcha_secret_key'] ?? '');
	    // Structured data is opt-in: "none" (default) outputs nothing at all.
	    $schema_output = sanitize_text_field($_REQUEST['schema_output'] ?? 'none');
	    if (!in_array($schema_output, array('none', 'interaction-counter', 'aggregate-rating'), true)) {
	        $schema_output = 'none';
	    }
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
	        'who_can_like' => $who_can_like,
	        'rate_limit_enabled' => $rate_limit_enabled,
	        'recaptcha_version' => $recaptcha_version,
	        'recaptcha_site_key' => $recaptcha_site_key,
	        'recaptcha_secret_key' => $recaptcha_secret_key,
	        'schema_output' => $schema_output,
	        'last_modified' => current_time('mysql')
	    );
	   	$wpdb->update($table, $data1, array('id' => 1));

	}


	// function option admin plugin
	function plb_plugin_setting_page(){
		global $wpdb;
	    $table_name = $wpdb->prefix . 'prolike';
	    $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE id = 1", $table_name ) );
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

		    if ($myrows[0]->display & 8) {
		        $display[8] = 'checked';
		    };

		    if ($myrows[0]->display & 16) {
		        $display[16] = 'checked';
		    };

		    if ($myrows[0]->display & 32) {
		        $display[32] = 'checked';
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
		$current_layout = $myrows[0]->layout;
		$output_shortcode[$myrows[0]->output_shortcode] = ' checked';
		$view[$myrows[0]->view] = ' selected="selected"';
		$beforeafter[$myrows[0]->beforeafter] = ' selected="selected"';
		$position[$myrows[0]->position] = ' selected="selected"';
		$who_can_like[$myrows[0]->who_can_like] = ' checked';
		$rate_limit_checked = ($myrows[0]->rate_limit_enabled === 'yes') ? ' checked' : '';
		$recaptcha_version_current = $myrows[0]->recaptcha_version;
		$schema_output_current = $myrows[0]->schema_output;
	 ?>
	 <div class="plb-wrap">
	 <div class="plb-card">

		<header class="plb-head">
			<h1 class="plb-title"><?php esc_html_e( 'ProLike Button', 'prolike-button' ); ?></h1>
			<nav class="plb-tabs">
				<button type="button" class="plb-tab is-active" data-tab="general"><?php esc_html_e( 'General setting', 'prolike-button' ); ?></button>
				<button type="button" class="plb-tab" data-tab="shortcode"><?php esc_html_e( 'Shortcode', 'prolike-button' ); ?></button>
				<button type="button" class="plb-tab" data-tab="antispam"><?php esc_html_e( 'Anti-spam', 'prolike-button' ); ?></button>
				<button type="button" class="plb-tab" data-tab="custom"><?php esc_html_e( 'Custom', 'prolike-button' ); ?></button>
			</nav>
		</header>

		<?php settings_errors();

	    	$table = $wpdb->prefix . 'prolike';
			$currently_view = $wpdb->get_row( $wpdb->prepare( "SELECT view FROM %i", $table ) );

			foreach ($currently_view as  $value) {
				$name_currently_view = $value;
			}

			$counter_image = 1;

			 ?>

		<form method="post" class="wrapp_form_prolike">

			<div class="plb-panel" data-panel="general">
			<div class="plb-body-rows">

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Where to display?', 'prolike-button' ); ?></div>
					<div class="plb-field plb-inline">
						<label class="plb-check"><input type="checkbox" name="display[]" <?php echo isset( $display['4'] ) ? esc_attr( $display['4'] ) : ''; ?> value="4"><?php esc_html_e('Posts', 'prolike-button');?></label>
						<label class="plb-check"><input type="checkbox" name="display[]" <?php echo isset( $display['8'] ) ? esc_attr( $display['8'] ) : ''; ?> value="8"><?php esc_html_e('Comments', 'prolike-button');?></label>
						<label class="plb-check"><input type="checkbox" name="display[]" <?php echo isset( $display['1'] ) ? esc_attr( $display['1'] ) : ''; ?> value="1"><?php esc_html_e('Homepage', 'prolike-button');?></label>
						<label class="plb-check"><input type="checkbox" name="display[]" <?php echo isset( $display['2'] ) ? esc_attr( $display['2'] ) : ''; ?> value="2"><?php esc_html_e('Pages', 'prolike-button');?></label>
						<label class="plb-check"><input type="checkbox" name="display[]" <?php echo isset( $display['16'] ) ? esc_attr( $display['16'] ) : ''; ?> value="16"><?php esc_html_e('Archive page', 'prolike-button');?></label>
						<label class="plb-check"><input type="checkbox" name="display[]" <?php echo isset( $display['32'] ) ? esc_attr( $display['32'] ) : ''; ?> value="32"><?php esc_html_e('Products', 'prolike-button');?><?php echo class_exists('WooCommerce') ? '' : ' (' . esc_html__('requires WooCommerce', 'prolike-button') . ')'; ?></label>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Who can like?', 'prolike-button' ); ?></div>
					<div class="plb-field plb-stack">
						<label class="plb-check"><input type="radio" name="who_can_like" <?php echo isset( $who_can_like['anyone'] ) ? esc_attr( $who_can_like['anyone'] ) : ''; ?> value="anyone"><?php esc_html_e('Anyone visiting', 'prolike-button');?></label>
						<label class="plb-check"><input type="radio" name="who_can_like" <?php echo isset( $who_can_like['members'] ) ? esc_attr( $who_can_like['members'] ) : ''; ?> value="members"><?php esc_html_e('Members only (logged in users)', 'prolike-button');?></label>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e('Layout page', 'prolike-button');?></div>
					<div class="plb-field">
						<div class="plb-segmented plb-layout-segmented" role="radiogroup" aria-label="<?php esc_attr_e('Layout page', 'prolike-button'); ?>">
							<button type="button" class="plb-seg<?php echo ($current_layout === 'with') ? ' is-active' : ''; ?>" data-value="with"><?php esc_html_e('With', 'prolike-button');?></button>
							<button type="button" class="plb-seg<?php echo ($current_layout === 'without') ? ' is-active' : ''; ?>" data-value="without"><?php esc_html_e('Without', 'prolike-button');?></button>
							<button type="button" class="plb-seg<?php echo ($current_layout === 'withoutlike') ? ' is-active' : ''; ?>" data-value="withoutlike"><?php esc_html_e('Without Like', 'prolike-button');?></button>
							<button type="button" class="plb-seg<?php echo ($current_layout === 'withoutdislike') ? ' is-active' : ''; ?>" data-value="withoutdislike"><?php esc_html_e('Without Dislike', 'prolike-button');?></button>
						</div>
						<input type="hidden" name="layout" class="plb-layout-input" value="<?php echo esc_attr($current_layout); ?>">
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'View', 'prolike-button' ); ?></div>
					<div class="plb-field">
						<select class="plb-select like_form_select_image" name="view" id="view">
						   <?php foreach ($likebtn_styles as $style): ?>
                                <option <?php echo isset( $view[$style] ) ? esc_attr( $view[$style] ) : ''; ?> value="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $style ); ?></option>
                            <?php endforeach ?>

		                        </select>
		                        <div class="dropdown_view">
			                    	<div class="drop_down_image">
										<a class="dropdown-toggle plb-btn-ghost" href="javascript:;" title="Menu">
											<?php
												$carently_like_text = $wpdb->get_row( $wpdb->prepare( "SELECT view FROM %i", $table ), ARRAY_A );

												if($carently_like_text['view'] === 'white'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image1.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'lightgray'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image2.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'black'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image3.png', __FILE__ ) );?>" alt="">
												<?php }
												else if($carently_like_text['view'] === 'smile'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image4.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'colorfull'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image5.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'check'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image6.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'updown'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image7.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'smilemodern'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image8.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'ok'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image9.png', __FILE__ ) );?>" alt="">
												<?php  }
												else if($carently_like_text['view'] === 'heart'){ ?>
													<img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image10.png', __FILE__ ) );?>" alt="">
												<?php }
											?>

										</a>
										<ul class="dropdown js-dropdown plb-style-dropdown">
											<?php $counter_number = 1;  ?>
										  	<?php foreach ($likebtn_styles as $style): ?>


				                    		<li data-number="<?php echo esc_attr( $counter_number ); ?>"><img src="<?php echo esc_url( plugins_url('/assets/img/icon_button/image'. $counter_image .'.png' , __FILE__ ) );?>"></li>
				                    		<?php $counter_number++; $counter_image++; ?>
				                    		<?php endforeach ?>
										</ul>
									</div>
		                    	</div>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Post like text', 'prolike-button' ); ?></div>
					<div class="plb-field plb-two">
						<input type="text" class="plb-input" placeholder="Like" value="<?php echo esc_attr($myrows[0]->text_like);?>" name="text_like">
						<input type="text" class="plb-input" placeholder="Dislike" value="<?php echo esc_attr($myrows[0]->text_dislike);?>" name="text_dislike">
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Size', 'prolike-button' ); ?></div>
					<div class="plb-field">
						<select class="plb-select" name="btn_size" id="size">
							<option <?php echo isset( $btn_size['small'] ) ? esc_attr( $btn_size['small'] ) : ''; ?> value="small"><?php esc_html_e( 'Small', 'prolike-button' ); ?></option>
							<option <?php echo isset( $btn_size['medium'] ) ? esc_attr( $btn_size['medium'] ) : ''; ?> value="medium"><?php esc_html_e( 'Medium', 'prolike-button' ); ?></option>
							<option <?php echo isset( $btn_size['big'] ) ? esc_attr( $btn_size['big'] ) : ''; ?> value="big"><?php esc_html_e( 'Big', 'prolike-button' ); ?></option>
						</select>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Position', 'prolike-button' ); ?></div>
					<div class="plb-field">
						<select class="plb-select" name="position" id="position">
							<option <?php echo isset( $position['left'] ) ? esc_attr( $position['left'] ) : ''; ?> value="left"><?php esc_html_e( 'Left', 'prolike-button' ); ?></option>
							<option <?php echo isset( $position['right'] ) ? esc_attr( $position['right'] ) : ''; ?> value="right"><?php esc_html_e( 'Right', 'prolike-button' ); ?></option>
							<option <?php echo isset( $position['center'] ) ? esc_attr( $position['center'] ) : ''; ?> value="center"><?php esc_html_e( 'Center', 'prolike-button' ); ?></option>
						</select>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Before or after posts buttons', 'prolike-button' ); ?></div>
					<div class="plb-field">
						<select class="plb-select" name="beforeafter" id="beforeafter">
							<option <?php echo isset( $beforeafter['before'] ) ? esc_attr( $beforeafter['before'] ) : ''; ?> value="before"><?php esc_html_e( 'Before', 'prolike-button' ); ?></option>
							<option <?php echo isset( $beforeafter['after'] ) ? esc_attr( $beforeafter['after'] ) : ''; ?> value="after"><?php esc_html_e( 'After', 'prolike-button' ); ?></option>
						</select>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Structured data (SEO)', 'prolike-button' ); ?></div>
					<div class="plb-field plb-stack">
						<select class="plb-select" name="schema_output">
							<option value="none" <?php selected($schema_output_current, 'none'); ?>><?php esc_html_e('Off (default)', 'prolike-button'); ?></option>
							<option value="interaction-counter" <?php selected($schema_output_current, 'interaction-counter'); ?>><?php esc_html_e('Like/Dislike counts (InteractionCounter)', 'prolike-button'); ?></option>
							<option value="aggregate-rating" <?php selected($schema_output_current, 'aggregate-rating'); ?>><?php esc_html_e('Rating out of 5, from like/dislike ratio (AggregateRating)', 'prolike-button'); ?></option>
						</select>
						<p class="plb-help"><?php esc_html_e('Adds machine-readable vote data to single posts/pages for search engines and crawlers. Google does not show a dedicated rich result for likes specifically, so treat this as SEO hygiene, not a guaranteed search-result bonus.', 'prolike-button'); ?></p>
					</div>
				</div>

			</div>
			<footer class="plb-foot">
				<span class="plb-status" role="status"></span>
				<button type="submit" name="update_prolike" class="plb-save"><?php esc_html_e( 'Save Settings', 'prolike-button' ); ?></button>
			</footer>
			</div>

			<div class="plb-panel" data-panel="shortcode" hidden>
			<div class="plb-body-rows">

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Output Like&Dislike Button shortcode', 'prolike-button' ); ?></div>
					<div class="plb-field plb-stack">
						<div class="plb-inline">
							<label class="plb-check"><input type="radio" name="output_shortcode" <?php echo isset( $output_shortcode['yes'] ) ? esc_attr( $output_shortcode['yes'] ) : ''; ?> value="yes"><?php esc_html_e( 'Yes', 'prolike-button' ); ?></label>
							<label class="plb-check"><input type="radio" name="output_shortcode" <?php echo isset( $output_shortcode['no'] ) ? esc_attr( $output_shortcode['no'] ) : ''; ?> value="no"><?php esc_html_e( 'No', 'prolike-button' ); ?></label>
						</div>
						<p class="plb-help"><?php esc_html_e( 'When "Yes" is selected, paste this shortcode into any post or page:', 'prolike-button' ); ?></p>
						<div class="plb-code-row">
							<code class="plb-code" id="plb-shortcode">[prolikebutton_shortcode]</code>
							<button type="button" class="plb-btn-ghost" id="plb-copy"><?php esc_html_e( 'Copy', 'prolike-button' ); ?></button>
						</div>
					</div>
				</div>

			</div>
			<footer class="plb-foot">
				<span class="plb-status" role="status"></span>
				<button type="submit" name="update_prolike" class="plb-save"><?php esc_html_e( 'Save Settings', 'prolike-button' ); ?></button>
			</footer>
			</div>

			<div class="plb-panel" data-panel="antispam" hidden>
			<div class="plb-body-rows">

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'Rate-limiting', 'prolike-button' ); ?></div>
					<div class="plb-field">
						<label class="plb-check"><input type="checkbox" name="rate_limit_enabled" value="yes"<?php echo esc_attr( $rate_limit_checked ); ?>><?php esc_html_e('Block excessive voting from the same IP (max 10 votes/min, and max 1 vote per post/comment). Off by default — shared office/public IPs may otherwise share a single vote.', 'prolike-button');?></label>
					</div>
				</div>

				<div class="plb-row">
					<div class="plb-label"><?php esc_html_e( 'reCAPTCHA', 'prolike-button' ); ?></div>
					<div class="plb-field plb-stack">
						<select class="plb-select" name="recaptcha_version">
							<option value="none" <?php selected($recaptcha_version_current, 'none'); ?>><?php esc_html_e('Off (default)', 'prolike-button'); ?></option>
							<option value="v2" <?php selected($recaptcha_version_current, 'v2'); ?>><?php esc_html_e('v2 — Checkbox', 'prolike-button'); ?></option>
							<option value="v3" <?php selected($recaptcha_version_current, 'v3'); ?>><?php esc_html_e('v3 — Invisible', 'prolike-button'); ?></option>
						</select>
						<input type="text" class="plb-input" name="recaptcha_site_key" placeholder="<?php esc_attr_e('Site Key', 'prolike-button'); ?>" value="<?php echo esc_attr($myrows[0]->recaptcha_site_key); ?>">
						<input type="text" class="plb-input" name="recaptcha_secret_key" placeholder="<?php esc_attr_e('Secret Key', 'prolike-button'); ?>" value="<?php echo esc_attr($myrows[0]->recaptcha_secret_key); ?>">
						<p class="plb-help"><?php esc_html_e('Optional. Leave "Off" if you don\'t need it — voting works fine without reCAPTCHA. Get keys at google.com/recaptcha/admin.', 'prolike-button'); ?></p>
					</div>
				</div>

			</div>
			<footer class="plb-foot">
				<span class="plb-status" role="status"></span>
				<button type="submit" name="update_prolike" class="plb-save"><?php esc_html_e( 'Save Settings', 'prolike-button' ); ?></button>
			</footer>
			</div>
		</form>

		<form id="save-custom-css-form" method="post" action="options.php" class="plb-panel" data-panel="custom" hidden>
			<div class="plb-body-rows plb-settings-table">
				<?php settings_fields('plb-image-setting'); ?>
				<?php do_settings_sections('plb_image_css_subpage'); ?>
			</div>
			<footer class="plb-foot">
				<span class="plb-status" role="status"></span>
				<button type="submit" name="btnSubmit" class="plb-save"><?php esc_html_e( 'Save Changes', 'prolike-button' ); ?></button>
			</footer>
		</form>

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
	require_once('include/stats-page.php');
	require_once('include/schema.php');
