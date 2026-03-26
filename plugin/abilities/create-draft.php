<?php
/**
 * Ability: Create Draft Post
 *
 * Creates a new draft post with the given title and content.
 */

wp_register_ability( 'workshop/create-draft', array(
	'label'       => 'Create Draft Post',
	'description' => 'Creates a new draft post with the given title and content.',
	'category'    => 'site',
	'input_schema' => array(
		'type'                 => 'object',
		'required'             => array( 'title', 'content' ),
		'properties'           => array(
			'title'   => array( 'type' => 'string', 'description' => 'The post title.' ),
			'content' => array( 'type' => 'string', 'description' => 'The post content.' ),
		),
		'additionalProperties' => false,
	),
	'output_schema' => array(
		'type'                 => 'object',
		'properties'           => array(
			'id'        => array( 'type' => 'integer' ),
			'title'     => array( 'type' => 'string' ),
			'status'    => array( 'type' => 'string' ),
			'edit_link' => array( 'type' => 'string' ),
		),
		'additionalProperties' => false,
	),
	'execute_callback' => function ( $input ) {
		$input   = (array) $input;
		$post_id = wp_insert_post( array(
			'post_title'   => sanitize_text_field( $input['title'] ),
			'post_content' => wp_kses_post( $input['content'] ),
			'post_status'  => 'draft',
			'post_author'  => get_current_user_id(),
		), true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		return array(
			'id'        => $post_id,
			'title'     => get_the_title( $post_id ),
			'status'    => 'draft',
			'edit_link' => get_edit_post_link( $post_id, 'raw' ),
		);
	},
	'permission_callback' => function () {
		return current_user_can( 'edit_posts' );
	},
	'meta' => array(
		'show_in_rest' => true,
		'annotations'  => array(
			'readonly'    => false,
			'destructive' => false,
			'idempotent'  => false,
		),
	),
) );
