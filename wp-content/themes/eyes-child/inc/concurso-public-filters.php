<?php
/**
 * Extra archive filter taxonomies for logged-in users.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Concursos públicos filter terms.
 *
 * @return array<string, string>
 */
function eyes_get_concursos_publicos_terms() {
	return array(
		'nacional'  => __( 'Nacional', 'eyes-child' ),
		'europeu'   => __( 'Europeu', 'eyes-child' ),
		'regional'  => __( 'Regional', 'eyes-child' ),
		'local'     => __( 'Local', 'eyes-child' ),
	);
}

/**
 * Benefícios fiscais filter terms.
 *
 * Types of corporate tax incentives commonly linked to public funding in Portugal.
 *
 * @return array<string, string>
 */
function eyes_get_beneficios_fiscais_terms() {
	return array(
		'nenhum'                   => __( 'Nenhum', 'eyes-child' ),
		'credito-fiscal'           => __( 'Crédito fiscal', 'eyes-child' ),
		'deducao-colecta'          => __( 'Dedução à colecta', 'eyes-child' ),
		'isencao-reducao-irc'      => __( 'Isenção ou redução de IRC', 'eyes-child' ),
		'depreciacoes-aceleradas'  => __( 'Depreciações aceleradas', 'eyes-child' ),
		'reinvestimento-lucro'     => __( 'Reinvestimento do lucro', 'eyes-child' ),
		'regime-fiscal-incentivo'  => __( 'Regime fiscal de incentivo', 'eyes-child' ),
		'outros'                   => __( 'Outros', 'eyes-child' ),
	);
}

/**
 * Seed logged-in-only filter taxonomies.
 *
 * @param string               $taxonomy Taxonomy slug.
 * @param array<string,string> $terms    Slug => label map.
 * @param string               $option   Version option name.
 * @param string               $version  Seed version string.
 */
function eyes_seed_concurso_filter_taxonomy( $taxonomy, $terms, $option, $version ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return;
	}

	if ( get_option( $option ) === $version ) {
		return;
	}

	$valid_slugs = array_keys( $terms );

	foreach ( $terms as $slug => $label ) {
		$existing = term_exists( $slug, $taxonomy );

		if ( $existing ) {
			wp_update_term(
				(int) $existing['term_id'],
				$taxonomy,
				array(
					'name' => $label,
					'slug' => $slug,
				)
			);
			continue;
		}

		wp_insert_term(
			$label,
			$taxonomy,
			array(
				'slug' => $slug,
			)
		);
	}

	$existing_terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'all',
		)
	);

	if ( ! is_wp_error( $existing_terms ) ) {
		foreach ( $existing_terms as $term ) {
			if ( in_array( $term->slug, $valid_slugs, true ) ) {
				continue;
			}

			wp_delete_term( $term->term_id, $taxonomy );
		}
	}

	update_option( $option, $version, false );
}

/**
 * Seed concursos_publicos and beneficios_fiscais terms.
 */
function eyes_seed_concurso_public_filter_terms() {
	$legacy_public_map = array(
		'compete-2030' => 'nacional',
		'norte-2030'   => 'regional',
	);

	foreach ( $legacy_public_map as $from_slug => $to_slug ) {
		if ( function_exists( 'eyes_migrate_concurso_term_assignments' ) ) {
			eyes_migrate_concurso_term_assignments( 'concursos_publicos', $from_slug, $to_slug );
		}
	}

	eyes_seed_concurso_filter_taxonomy(
		'concursos_publicos',
		eyes_get_concursos_publicos_terms(),
		'eyes_concursos_publicos_terms_version',
		'2026-06-10'
	);

	eyes_seed_concurso_filter_taxonomy(
		'beneficios_fiscais',
		eyes_get_beneficios_fiscais_terms(),
		'eyes_beneficios_fiscais_terms_version',
		'2026-06-09'
	);
}
add_action( 'init', 'eyes_seed_concurso_public_filter_terms', 20 );
