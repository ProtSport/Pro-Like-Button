<?php

	/*
	=======================================================
		Structured data (schema.org) for like/dislike counts.
		Off by default. See plans/04-rich-snippets.md — Google
		doesn't show a dedicated rich result for "likes", this is
		machine-readable data for whatever else reads it.
	=======================================================
	*/

	// Builds the schema.org fragment for one post, or an empty array when
	// there is nothing worth outputting (feature off, or zero votes yet).
	function plb_build_interaction_stats( $post_id, $schema_output ) {
		global $wpdb;
		$table_name_post = $wpdb->prefix . 'posts';
		$counts = $wpdb->get_row(
			$wpdb->prepare( "SELECT counter_like, counter_dislike FROM $table_name_post WHERE id = %d", $post_id ),
			ARRAY_A
		);
		$likes = isset( $counts['counter_like'] ) ? (int) $counts['counter_like'] : 0;
		$dislikes = isset( $counts['counter_dislike'] ) ? (int) $counts['counter_dislike'] : 0;

		if ( $schema_output === 'aggregate-rating' ) {
			$total = $likes + $dislikes;
			if ( $total === 0 ) {
				return array();
			}
			return array(
				'aggregateRating' => array(
					'@type'       => 'AggregateRating',
					'ratingValue' => round( 1 + 4 * ( $likes / $total ), 1 ),
					'bestRating'  => 5,
					'worstRating' => 1,
					'ratingCount' => $total,
				),
			);
		}

		// Default when enabled: interaction-counter.
		if ( $likes === 0 && $dislikes === 0 ) {
			return array();
		}
		return array(
			'interactionStatistic' => array(
				array(
					'@type'                => 'InteractionCounter',
					'interactionType'      => 'https://schema.org/LikeAction',
					'userInteractionCount' => $likes,
				),
				array(
					'@type'                => 'InteractionCounter',
					'interactionType'      => 'https://schema.org/DislikeAction',
					'userInteractionCount' => $dislikes,
				),
			),
		);
	}

	// Whether the current request is a page this feature applies to: a single
	// post or page that also has "Where to display" enabled for that type —
	// reusing the same bits the like/dislike buttons themselves check.
	function plb_schema_applies_here( $display_bitmask ) {
		if ( ( $display_bitmask & 4 ) && is_single() ) {
			return true;
		}
		if ( ( $display_bitmask & 2 ) && is_page() && ! is_front_page() ) {
			return true;
		}
		return false;
	}

	function plb_get_schema_settings() {
		global $wpdb;
		$table = $wpdb->prefix . 'prolike';
		$myrows = $wpdb->get_results( "SELECT * FROM $table WHERE id = 1" );
		if ( empty( $myrows ) || empty( $myrows[0]->schema_output ) || $myrows[0]->schema_output === 'none' ) {
			return null;
		}
		if ( ! is_singular() || ! plb_schema_applies_here( (int) $myrows[0]->display ) ) {
			return null;
		}
		return $myrows[0];
	}

	// Fallback path: no Rank Math active, print our own JSON-LD block.
	add_action( 'wp_head', 'plb_output_schema_markup' );
	function plb_output_schema_markup() {
		if ( class_exists( 'RankMath' ) ) {
			return; // handled via the rank_math/json_ld filter below instead
		}

		$settings = plb_get_schema_settings();
		if ( empty( $settings ) ) {
			return;
		}

		$post_id = get_the_ID();
		$stats = plb_build_interaction_stats( $post_id, $settings->schema_output );
		if ( empty( $stats ) ) {
			return;
		}

		$schema = array_merge(
			array(
				'@context'         => 'https://schema.org',
				'@type'            => 'BlogPosting',
				'mainEntityOfPage' => get_permalink( $post_id ),
				'headline'         => get_the_title( $post_id ),
			),
			$stats
		);

		echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $schema ) . "</script>\n";
	}

	// Integration path: Rank Math already builds a JSON-LD graph for the page —
	// mix our interaction data into its main content entry instead of printing
	// a second, competing <script> block.
	add_filter( 'rank_math/json_ld', 'plb_add_rank_math_schema', 10, 2 );
	function plb_add_rank_math_schema( $data, $jsonld ) {
		$settings = plb_get_schema_settings();
		if ( empty( $settings ) ) {
			return $data;
		}

		$stats = plb_build_interaction_stats( get_the_ID(), $settings->schema_output );
		if ( empty( $stats ) ) {
			return $data;
		}

		$content_types = array( 'Article', 'BlogPosting', 'NewsArticle', 'WebPage' );
		foreach ( $data as $key => $entry ) {
			if ( isset( $entry['@type'] ) && in_array( $entry['@type'], $content_types, true ) ) {
				$data[ $key ] = array_merge( $entry, $stats );
				break;
			}
		}

		return $data;
	}
