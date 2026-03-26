<?php
/**
 * Ability: Search Posts
 *
 * Searches published posts by keyword.
 */

wp_register_ability( 'workshop/search-posts', array(
	'label'       => 'Search Posts',
	'description' => 'Searches published posts by keyword.',
	'category'    => 'site',
	'input_schema' => array(
		'type'                 => 'object',
		'required'             => array( 'query' ),
		'properties'           => array(
			'query' => array(
				'type'        => 'string',
				'description' => 'Search keyword or phrase.',
			),
			'count' => array(
				'type'        => 'integer',
				'description' => 'Max results (1-10).',
				'default'     => 5,
			),
		),
		'additionalProperties' => false,
	),
	'output_schema' => array(
		'type'  => 'array',
		'items' => array(
			'type'       => 'object',
			'properties' => array(
				'id'      => array( 'type' => 'integer' ),
				'title'   => array( 'type' => 'string' ),
				'excerpt' => array( 'type' => 'string' ),
				'link'    => array( 'type' => 'string' ),
			),
		),
	),
	'execute_callback' => function ( $input ) {
		$input = (array) $input;
		$posts = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			's'              => sanitize_text_field( $input['query'] ),
			'posts_per_page' => min( absint( $input['count'] ?? 5 ), 10 ),
		) );
		return array_map( function ( $post ) {
			return array(
				'id'      => $post->ID,
				'title'   => get_the_title( $post ),
				'excerpt' => wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ),
				'link'    => get_permalink( $post ),
			);
		}, $posts );
	},
	'permission_callback' => function () {
		return current_user_can( 'read' );
	},
	'meta' => array(
		'show_in_rest' => true,
		'annotations'  => array(
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		),
	),
) );
