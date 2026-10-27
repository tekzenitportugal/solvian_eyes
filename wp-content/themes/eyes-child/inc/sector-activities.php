<?php
/**
 * Sector activity taxonomy terms (Setor de atividade on concurso detail).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sector activity labels keyed by slug.
 *
 * @return array<string, string>
 */
function eyes_get_sector_activities() {
	return array(
		'tecnologia'  => __( 'Tecnologia', 'eyes-child' ),
		'industria'   => __( 'Indústria', 'eyes-child' ),
		'turismo'     => __( 'Turismo', 'eyes-child' ),
		'agricola'    => __( 'Agrícola', 'eyes-child' ),
		'energia'     => __( 'Energia', 'eyes-child' ),
		'comercio'    => __( 'Comércio', 'eyes-child' ),
		'servicos'    => __( 'Serviços', 'eyes-child' ),
		'outros'      => __( 'Outros', 'eyes-child' ),
	);
}

/**
 * Seed sector_activity terms and remove invalid entries.
 */
function eyes_seed_sector_activity_terms() {
	if ( ! taxonomy_exists( 'sector_activity' ) ) {
		return;
	}

	$seed_version = '2026-06-07';
	if ( get_option( 'eyes_sector_activity_terms_version' ) === $seed_version ) {
		return;
	}

	$sectors     = eyes_get_sector_activities();
	$valid_slugs = array_keys( $sectors );

	foreach ( $sectors as $slug => $label ) {
		$existing = term_exists( $slug, 'sector_activity' );

		if ( $existing ) {
			wp_update_term(
				(int) $existing['term_id'],
				'sector_activity',
				array(
					'name' => $label,
					'slug' => $slug,
				)
			);
			continue;
		}

		wp_insert_term(
			$label,
			'sector_activity',
			array(
				'slug' => $slug,
			)
		);
	}

	$existing_terms = get_terms(
		array(
			'taxonomy'   => 'sector_activity',
			'hide_empty' => false,
			'fields'     => 'all',
		)
	);

	if ( ! is_wp_error( $existing_terms ) ) {
		foreach ( $existing_terms as $term ) {
			if ( in_array( $term->slug, $valid_slugs, true ) ) {
				continue;
			}

			wp_delete_term( $term->term_id, 'sector_activity' );
		}
	}

	update_option( 'eyes_sector_activity_terms_version', $seed_version, false );
}
add_action( 'init', 'eyes_seed_sector_activity_terms', 20 );
