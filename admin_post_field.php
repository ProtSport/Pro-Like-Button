<?php 
add_filter('manage_posts_columns', 'plb_my_columns');
function plb_my_columns($columns) {
    $columns['likes'] = 'Likes';
    $columns['dislikes'] = 'Dislikes';
    return $columns;
}

// we fill the column with data
add_filter('manage_post_posts_custom_column', 'plb_fill_views_column', 5, 2);
function plb_fill_views_column( $colname, $post_id ){
	global $wpdb;
	$current_like = get_the_ID();
	$table_name_post = $wpdb->prefix . 'posts';
    $carently_likes_dislikes = $wpdb->get_row( $wpdb->prepare( "SELECT counter_like, counter_dislike FROM %i WHERE id = %d", $table_name_post, $current_like ), ARRAY_A );
	if( $colname === 'likes' ){
		   print_r($carently_likes_dislikes['counter_like']);
	}
	if( $colname === 'dislikes' ){
		   print_r($carently_likes_dislikes['counter_dislike']);
	}
}

// correct the width of the column through css
add_action('admin_head', 'plb_add_views_column_css');
function plb_add_views_column_css(){
	if( get_current_screen()->base == 'edit')
		echo '<style type="text/css">.column-likes{width:10%;}.column-dislikes{width:10%;}</style>';
}

// add the ability to sort the column
add_filter('manage_edit-post_sortable_columns', 'plb_add_views_sortable_column');
function plb_add_views_sortable_column($sortable_columns){
	$sortable_columns['likes'] = 'likes_likes';
	return $sortable_columns;
}

add_filter( 'posts_clauses', 'plb_add_column_views_request', 10, 2 );
function plb_add_column_views_request( $clauses, $wp_query ){
	global $wpdb;
	if( 'likes_likes' != $wp_query->get('orderby') )
		return $clauses;
	global $wpdb;
	$clauses['orderby']  = " {$wpdb->posts}.counter_like ";
	$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';

	return $clauses;
}