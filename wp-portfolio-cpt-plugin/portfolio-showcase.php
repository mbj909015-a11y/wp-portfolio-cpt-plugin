<?php
/**
 * Plugin Name: Portfolio Showcase
 * Description: Registers a "Project" custom post type with a taxonomy, extra meta fields, REST API support, and a [projects] shortcode.
 * Version:     1.0.0
 * Author:      Muhammad Bin Jamil
 * License:     GPL-2.0-or-later
 * Text Domain: portfolio-showcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Block direct access.
}

/**
 * Register the Project post type and Technology taxonomy.
 */
function psc_register_content_types() {
	register_post_type( 'psc_project', array(
		'labels'       => array(
			'name'          => __( 'Projects', 'portfolio-showcase' ),
			'singular_name' => __( 'Project', 'portfolio-showcase' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-portfolio',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'projects' ),
	) );

	register_taxonomy( 'psc_technology', 'psc_project', array(
		'labels'       => array(
			'name'          => __( 'Technologies', 'portfolio-showcase' ),
			'singular_name' => __( 'Technology', 'portfolio-showcase' ),
		),
		'hierarchical' => false,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'technology' ),
	) );

	register_post_meta( 'psc_project', 'psc_live_url', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'esc_url_raw',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
}
add_action( 'init', 'psc_register_content_types' );

/**
 * Meta box for the live project URL.
 */
function psc_add_meta_box() {
	add_meta_box( 'psc_details', __( 'Project Details', 'portfolio-showcase' ), 'psc_render_meta_box', 'psc_project', 'side' );
}
add_action( 'add_meta_boxes', 'psc_add_meta_box' );

function psc_render_meta_box( $post ) {
	wp_nonce_field( 'psc_save_meta', 'psc_meta_nonce' );
	$url = get_post_meta( $post->ID, 'psc_live_url', true );
	echo '<label for="psc_live_url">' . esc_html__( 'Live URL', 'portfolio-showcase' ) . '</label>';
	echo '<input type="url" id="psc_live_url" name="psc_live_url" class="widefat" value="' . esc_attr( $url ) . '" />';
}

function psc_save_meta( $post_id ) {
	if ( ! isset( $_POST['psc_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['psc_meta_nonce'] ) ), 'psc_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['psc_live_url'] ) ) {
		update_post_meta( $post_id, 'psc_live_url', esc_url_raw( wp_unslash( $_POST['psc_live_url'] ) ) );
	}
}
add_action( 'save_post_psc_project', 'psc_save_meta' );

/**
 * Shortcode: [projects limit="6" technology="wordpress"]
 */
function psc_projects_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'limit'      => 6,
		'technology' => '',
	), $atts, 'projects' );

	$args = array(
		'post_type'      => 'psc_project',
		'posts_per_page' => absint( $atts['limit'] ),
	);

	if ( ! empty( $atts['technology'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'psc_technology',
				'field'    => 'slug',
				'terms'    => sanitize_title( $atts['technology'] ),
			),
		);
	}

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	echo '<div class="psc-projects">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$url = get_post_meta( get_the_ID(), 'psc_live_url', true );
		echo '<article class="psc-project">';
		echo '<h3>' . esc_html( get_the_title() ) . '</h3>';
		echo '<p>' . esc_html( get_the_excerpt() ) . '</p>';
		if ( $url ) {
			echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'View site', 'portfolio-showcase' ) . '</a>';
		}
		echo '</article>';
	}
	echo '</div>';
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'projects', 'psc_projects_shortcode' );

/**
 * Flush rewrite rules on activation/deactivation.
 */
function psc_activate() {
	psc_register_content_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'psc_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
