<?php
/**
 * Concurso post type and taxonomies.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Concurso custom post type.
 */
function eyes_register_concursos_cpt() {
	$labels = array(
		'name'                  => _x( 'Concursos', 'Post Type General Name', 'eyes-child' ),
		'singular_name'         => _x( 'Concurso', 'Post Type Singular Name', 'eyes-child' ),
		'menu_name'             => __( 'Concursos', 'eyes-child' ),
		'name_admin_bar'        => __( 'Concurso', 'eyes-child' ),
		'archives'              => __( 'Concurso Archives', 'eyes-child' ),
		'attributes'            => __( 'Concurso Attributes', 'eyes-child' ),
		'parent_item_colon'     => __( 'Parent Concurso:', 'eyes-child' ),
		'all_items'             => __( 'All Concursos', 'eyes-child' ),
		'add_new_item'          => __( 'Add New Concurso', 'eyes-child' ),
		'add_new'               => __( 'Add New', 'eyes-child' ),
		'new_item'              => __( 'New Concurso', 'eyes-child' ),
		'edit_item'             => __( 'Edit Concurso', 'eyes-child' ),
		'update_item'           => __( 'Update Concurso', 'eyes-child' ),
		'view_item'             => __( 'View Concurso', 'eyes-child' ),
		'view_items'            => __( 'View Concursos', 'eyes-child' ),
		'search_items'          => __( 'Search Concurso', 'eyes-child' ),
		'not_found'             => __( 'Not found', 'eyes-child' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'eyes-child' ),
		'featured_image'        => __( 'Featured Image', 'eyes-child' ),
		'set_featured_image'    => __( 'Set featured image', 'eyes-child' ),
		'remove_featured_image' => __( 'Remove featured image', 'eyes-child' ),
		'use_featured_image'    => __( 'Use as featured image', 'eyes-child' ),
		'insert_into_item'      => __( 'Insert into concurso', 'eyes-child' ),
		'uploaded_to_this_item' => __( 'Uploaded to this concurso', 'eyes-child' ),
		'items_list'            => __( 'Concursos list', 'eyes-child' ),
		'items_list_navigation' => __( 'Concursos list navigation', 'eyes-child' ),
		'filter_items_list'     => __( 'Filter concursos list', 'eyes-child' ),
	);

	register_post_type(
		'concurso',
		array(
			'label'               => __( 'Concurso', 'eyes-child' ),
			'description'         => __( 'Post Type for Concursos', 'eyes-child' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'taxonomies'          => array(
				'regiao',
				'enquadramento_pme',
				'cae_activity',
				'financing_type',
				'sector_activity',
				'concurso_audience',
				'concursos_publicos',
				'beneficios_fiscais',
			),
			'rewrite'             => array(
				'slug'       => 'concurso',
				'with_front' => false,
			),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-megaphone',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		)
	);
}
add_action( 'init', 'eyes_register_concursos_cpt', 0 );

/**
 * Register taxonomies for the Concurso post type.
 *
 * All taxonomies use hierarchical (checkbox) UI so editors pick from fixed terms
 * instead of creating free-form tags. Only administrators can add/edit terms.
 */
function eyes_register_concursos_taxonomies() {
	$taxonomy_defaults = array(
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'capabilities'      => array(
			'manage_terms' => 'manage_options',
			'edit_terms'   => 'manage_options',
			'delete_terms' => 'manage_options',
			'assign_terms' => 'edit_posts',
		),
	);

	$taxonomies = array(
		'regiao'            => array(
			'name'          => _x( 'Regiões', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Região', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Regiões', 'eyes-child' ),
		),
		'enquadramento_pme' => array(
			'name'          => _x( 'Enquadramento PME', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Enquadramento PME', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Enquadramento PME', 'eyes-child' ),
		),
		'cae_activity'      => array(
			'name'          => _x( 'Atividades CAE', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Atividade CAE', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Atividades CAE', 'eyes-child' ),
		),
		'financing_type'    => array(
			'name'          => _x( 'Tipos de Financiamento', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Tipo de Financiamento', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Tipos de Financiamento', 'eyes-child' ),
		),
		'sector_activity'   => array(
			'name'          => _x( 'Setores de Atividade', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Setor de Atividade', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Setores de Atividade', 'eyes-child' ),
		),
		'concurso_audience' => array(
			'name'          => _x( 'Público-Alvo', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Público-Alvo', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Público-Alvo', 'eyes-child' ),
		),
		'concursos_publicos' => array(
			'name'          => _x( 'Concursos Públicos', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Concurso Público', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Concursos Públicos', 'eyes-child' ),
		),
		'beneficios_fiscais' => array(
			'name'          => _x( 'Benefícios Fiscais', 'Taxonomy General Name', 'eyes-child' ),
			'singular_name' => _x( 'Benefício Fiscal', 'Taxonomy Singular Name', 'eyes-child' ),
			'menu_name'     => __( 'Benefícios Fiscais', 'eyes-child' ),
		),
	);

	foreach ( $taxonomies as $taxonomy => $labels ) {
		register_taxonomy(
			$taxonomy,
			array( 'concurso' ),
			array_merge(
				$taxonomy_defaults,
				array(
					'labels' => $labels,
				)
			)
		);
	}
}
add_action( 'init', 'eyes_register_concursos_taxonomies', 0 );
