<?php
if ( ! defined( 'ABSPATH' ) ) exit;

	/*
	=======================================================
		Statistics page: totals, today/this-week activity,
		and the top liked posts. See plans/02-stats-dashboard.md
	=======================================================
	*/

	function plb_stats_page(){
		global $wpdb;
		$posts_table = $wpdb->prefix . 'posts';
		$votes_table = $wpdb->prefix . 'prolike_votes';

		$totals = $wpdb->get_row( $wpdb->prepare( "SELECT SUM(counter_like) AS total_like, SUM(counter_dislike) AS total_dislike FROM %i", $posts_table ), ARRAY_A );
		$total_like = isset( $totals['total_like'] ) ? (int) $totals['total_like'] : 0;
		$total_dislike = isset( $totals['total_dislike'] ) ? (int) $totals['total_dislike'] : 0;

		$today = current_time( 'Y-m-d' );
		$votes_today = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE DATE(created_at) = %s", $votes_table, $today ) );

		$week_start = gmdate( 'Y-m-d', strtotime( 'monday this week', current_time( 'timestamp' ) ) );
		$prev_week_start = gmdate( 'Y-m-d', strtotime( '-7 days', strtotime( $week_start ) ) );

		$votes_this_week = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE created_at >= %s", $votes_table, $week_start . ' 00:00:00' ) );
		$votes_prev_week = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE created_at >= %s AND created_at < %s", $votes_table, $prev_week_start . ' 00:00:00', $week_start . ' 00:00:00' ) );

		$week_change = null;
		if ( $votes_prev_week > 0 ) {
			$week_change = round( ( ( $votes_this_week - $votes_prev_week ) / $votes_prev_week ) * 100 );
		}

		$top_posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, counter_like, counter_dislike FROM %i WHERE counter_like > 0 OR counter_dislike > 0 ORDER BY counter_like DESC LIMIT 10", $posts_table ) );
		?>
		<div class="plb-wrap">
		<div class="plb-card">

			<header class="plb-head plb-head-simple">
				<h1 class="plb-title"><?php esc_html_e( 'ProLike Button — Statistics', 'prolike-button' ); ?></h1>
			</header>

			<div class="plb-body-rows">

				<div class="plb-stats-cards">
					<div class="plb-stat-card">
						<div class="plb-stat-value"><?php echo esc_html( $total_like ); ?></div>
						<div class="plb-stat-label"><?php esc_html_e( 'Total likes', 'prolike-button' ); ?></div>
					</div>
					<div class="plb-stat-card">
						<div class="plb-stat-value"><?php echo esc_html( $total_dislike ); ?></div>
						<div class="plb-stat-label"><?php esc_html_e( 'Total dislikes', 'prolike-button' ); ?></div>
					</div>
					<div class="plb-stat-card">
						<div class="plb-stat-value"><?php echo esc_html( $votes_today ); ?></div>
						<div class="plb-stat-label"><?php esc_html_e( 'Votes today', 'prolike-button' ); ?></div>
					</div>
					<div class="plb-stat-card">
						<div class="plb-stat-value">
							<?php echo esc_html( $votes_this_week ); ?>
							<?php if ( $week_change !== null ) : ?>
								<span class="plb-stat-delta <?php echo ( $week_change >= 0 ) ? 'is-up' : 'is-down'; ?>">
									<?php echo esc_html( ( $week_change >= 0 ? '+' : '' ) . $week_change ) . '%'; ?>
								</span>
							<?php endif; ?>
						</div>
						<div class="plb-stat-label"><?php esc_html_e( 'Votes this week (vs last week)', 'prolike-button' ); ?></div>
					</div>
				</div>

				<h2 class="plb-stats-subtitle"><?php esc_html_e( 'Top liked posts', 'prolike-button' ); ?></h2>
				<?php if ( empty( $top_posts ) ) : ?>
					<p class="plb-help"><?php esc_html_e( 'No votes yet.', 'prolike-button' ); ?></p>
				<?php else : ?>
					<table class="plb-stats-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Post', 'prolike-button' ); ?></th>
								<th><?php esc_html_e( 'Likes', 'prolike-button' ); ?></th>
								<th><?php esc_html_e( 'Dislikes', 'prolike-button' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_posts as $post ) : ?>
								<tr>
									<td><a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( $post->post_title !== '' ? $post->post_title : '(no title)' ); ?></a></td>
									<td><?php echo esc_html( $post->counter_like ); ?></td>
									<td><?php echo esc_html( $post->counter_dislike ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

			</div>

		</div>
		</div>
		<?php
	}
