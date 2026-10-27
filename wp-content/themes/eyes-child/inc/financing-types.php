<?php
/**
 * Financing type taxonomy terms (Tipo de financiamento on concurso detail).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Financing instrument labels keyed by slug.
 *
 * Matches the detail page "Tipo de financiamento" taxonomy — separate from the
 * archive Financiamento filter stored in ACF concurso_activity.
 *
 * @return array<string, string>
 */
function eyes_get_financing_types() {
	return array(
		'subsidio'   => __( 'Subsídio', 'eyes-child' ),
		'emprestimo' => __( 'Empréstimo', 'eyes-child' ),
		'garantia'   => __( 'Garantia', 'eyes-child' ),
		'misto'      => __( 'Misto', 'eyes-child' ),
		'outros'     => __( 'Outros', 'eyes-child' ),
	);
}

/**
 * Seed financing_type terms and remove invalid entries.
 */
function eyes_seed_financing_type_terms() {
	if ( ! taxonomy_exists( 'financing_type' ) ) {
		return;
	}

	$seed_version = '2026-06-07';
	if ( get_option( 'eyes_financing_terms_version' ) === $seed_version ) {
		return;
	}

	$types       = eyes_get_financing_types();
	$valid_slugs = array_keys( $types );

	foreach ( $types as $slug => $label ) {
		$existing = term_exists( $slug, 'financing_type' );

		if ( $existing ) {
			wp_update_term(
				(int) $existing['term_id'],
				'financing_type',
				array(
					'name' => $label,
					'slug' => $slug,
				)
			);
			continue;
		}

		wp_insert_term(
			$label,
			'financing_type',
			array(
				'slug' => $slug,
			)
		);
	}

	$existing_terms = get_terms(
		array(
			'taxonomy'   => 'financing_type',
			'hide_empty' => false,
			'fields'     => 'all',
		)
	);

	if ( ! is_wp_error( $existing_terms ) ) {
		foreach ( $existing_terms as $term ) {
			if ( in_array( $term->slug, $valid_slugs, true ) ) {
				continue;
			}

			wp_delete_term( $term->term_id, 'financing_type' );
		}
	}

	update_option( 'eyes_financing_terms_version', $seed_version, false );
}
add_action( 'init', 'eyes_seed_financing_type_terms', 20 );
