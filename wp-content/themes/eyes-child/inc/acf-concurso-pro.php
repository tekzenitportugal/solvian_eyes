<?php
/**
 * ACF fields for Pro-only concurso detail content.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register local ACF field group for concurso Pro content.
 */
function eyes_acf_register_concurso_pro_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_eyes_concurso_pro',
			'title'                 => __( 'Conteúdo Pro (detalhe do concurso)', 'eyes-child' ),
			'fields'                => array(
				array(
					'key'               => 'field_eyes_concurso_legal_intro',
					'label'             => __( 'Base legal — introdução', 'eyes-child' ),
					'name'              => 'concurso_legal_intro',
					'type'              => 'textarea',
					'rows'              => 4,
					'new_lines'         => 'br',
				),
				array(
					'key'               => 'field_eyes_concurso_legal_points',
					'label'             => __( 'Base legal — pontos', 'eyes-child' ),
					'name'              => 'concurso_legal_points',
					'type'              => 'repeater',
					'layout'            => 'table',
					'button_label'      => __( 'Adicionar ponto', 'eyes-child' ),
					'sub_fields'        => array(
						array(
							'key'   => 'field_eyes_concurso_legal_point',
							'label' => __( 'Ponto', 'eyes-child' ),
							'name'  => 'point',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'               => 'field_eyes_concurso_documents',
					'label'             => __( 'Documentos', 'eyes-child' ),
					'name'              => 'concurso_documents',
					'type'              => 'repeater',
					'layout'            => 'table',
					'button_label'      => __( 'Adicionar documento', 'eyes-child' ),
					'sub_fields'        => array(
						array(
							'key'   => 'field_eyes_concurso_document_label',
							'label' => __( 'Nome', 'eyes-child' ),
							'name'  => 'label',
							'type'  => 'text',
						),
						array(
							'key'           => 'field_eyes_concurso_document_file',
							'label'         => __( 'Ficheiro', 'eyes-child' ),
							'name'          => 'file',
							'type'          => 'file',
							'return_format' => 'array',
							'library'       => 'all',
						),
						array(
							'key'           => 'field_eyes_concurso_document_complement',
							'label'         => __( 'Complemento', 'eyes-child' ),
							'name'          => 'is_complement',
							'type'          => 'true_false',
							'ui'            => 1,
							'default_value' => 0,
						),
					),
				),
				array(
					'key'               => 'field_eyes_concurso_application_forms',
					'label'             => __( 'Formulários de candidatura', 'eyes-child' ),
					'name'              => 'concurso_application_forms',
					'type'              => 'repeater',
					'layout'            => 'table',
					'button_label'      => __( 'Adicionar formulário', 'eyes-child' ),
					'sub_fields'        => array(
						array(
							'key'   => 'field_eyes_concurso_form_label',
							'label' => __( 'Nome', 'eyes-child' ),
							'name'  => 'label',
							'type'  => 'text',
						),
						array(
							'key'           => 'field_eyes_concurso_form_file',
							'label'         => __( 'Ficheiro', 'eyes-child' ),
							'name'          => 'file',
							'type'          => 'file',
							'return_format' => 'array',
							'library'       => 'all',
						),
					),
				),
				array(
					'key'               => 'field_eyes_concurso_legislation',
					'label'             => __( 'Legislação associada', 'eyes-child' ),
					'name'              => 'concurso_legislation',
					'type'              => 'repeater',
					'layout'            => 'block',
					'button_label'      => __( 'Adicionar legislação', 'eyes-child' ),
					'sub_fields'        => array(
						array(
							'key'   => 'field_eyes_concurso_legislation_title',
							'label' => __( 'Título', 'eyes-child' ),
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_eyes_concurso_legislation_summary',
							'label' => __( 'Resumo', 'eyes-child' ),
							'name'  => 'summary',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'concurso',
					),
				),
			),
			'menu_order'            => 20,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'eyes_acf_register_concurso_pro_fields' );

/**
 * Hide the code-registered Pro ACF box on Free installs (custom meta box replaces it).
 *
 * @param array<string, mixed> $field_group Field group settings.
 */
function eyes_hide_acf_pro_group_on_free( $field_group ) {
	if ( ! is_admin() || ! function_exists( 'acf_is_pro' ) || acf_is_pro() ) {
		return $field_group;
	}

	if ( empty( $field_group['key'] ) || 'group_eyes_concurso_pro' !== $field_group['key'] ) {
		return $field_group;
	}

	$field_group['active'] = false;

	return $field_group;
}
add_filter( 'acf/load_field_group', 'eyes_hide_acf_pro_group_on_free' );

/**
 * Seed version — bump only to re-run empty-field seeding (never overwrites existing values).
 */
function eyes_concurso_pro_seed_version() {
	return '2026-06-12-empty-only-v1';
}

/**
 * Whether a Pro ACF field already has stored content.
 *
 * @param string $field_name Field name.
 * @param int    $post_id    Post ID.
 */
function eyes_concurso_pro_field_has_value( $field_name, $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return false;
	}

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, $post_id );
	} else {
		$value = get_post_meta( $post_id, $field_name, true );
	}

	if ( is_string( $value ) ) {
		return '' !== trim( $value );
	}

	if ( is_array( $value ) ) {
		return ! empty( $value );
	}

	if ( is_numeric( $value ) ) {
		return (int) $value > 0;
	}

	return ! empty( $value );
}

/**
 * Create or reuse a demo PDF attachment for Pro document rows.
 *
 * @param string $filename File name.
 * @param string $title    Attachment title.
 */
function eyes_get_or_create_demo_pdf_attachment( $filename, $title = '' ) {
	static $cache = array();

	$filename = sanitize_file_name( $filename );
	if ( '' === $filename ) {
		return 0;
	}

	if ( isset( $cache[ $filename ] ) ) {
		return $cache[ $filename ];
	}

	$option_key = 'eyes_demo_pdf_' . md5( $filename );
	$existing   = (int) get_option( $option_key, 0 );

	if ( $existing > 0 && 'attachment' === get_post_type( $existing ) ) {
		$cache[ $filename ] = $existing;
		return $existing;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();
	$dir        = trailingslashit( $upload_dir['basedir'] ) . 'eyes-demo-docs';
	wp_mkdir_p( $dir );
	$filepath = $dir . '/' . $filename;

	if ( ! file_exists( $filepath ) ) {
		$pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n%%EOF";
		file_put_contents( $filepath, $pdf );
	}

	$filetype   = wp_check_filetype( $filename, null );
	$attachment = array(
		'post_mime_type' => $filetype['type'] ?: 'application/pdf',
		'post_title'     => $title ? $title : pathinfo( $filename, PATHINFO_FILENAME ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attachment_id = wp_insert_attachment( $attachment, $filepath );

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $filepath ) );
	update_option( $option_key, (int) $attachment_id, false );

	$cache[ $filename ] = (int) $attachment_id;

	return (int) $attachment_id;
}

/**
 * Ensure audience taxonomy terms exist.
 *
 * @return array<string, int> slug => term_id
 */
function eyes_ensure_concurso_audience_terms() {
	$definitions = array(
		'pme'                    => __( 'PME', 'eyes-child' ),
		'empresas-industriais'   => __( 'Empresas industriais', 'eyes-child' ),
		'empresas-servicos'      => __( 'Empresas de serviços', 'eyes-child' ),
		'startups'               => __( 'Startups', 'eyes-child' ),
		'edificios-empresariais' => __( 'Edifícios empresariais', 'eyes-child' ),
	);

	$map = array();

	foreach ( $definitions as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'concurso_audience' );

		if ( ! $term ) {
			$result = wp_insert_term( $name, 'concurso_audience', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $result ) ) {
				$map[ $slug ] = (int) $result['term_id'];
			}
			continue;
		}

		$map[ $slug ] = (int) $term->term_id;
	}

	return $map;
}

/**
 * Build default Pro field values with downloadable demo PDFs.
 *
 * @param int $post_id Post ID.
 * @return array<string, mixed>
 */
function eyes_get_concurso_pro_field_values( $post_id ) {
	$notice = (string) get_post_meta( $post_id, 'concurso_notice_number', true );
	$entity = (string) get_post_meta( $post_id, 'concurso_entity', true );

	$avisio_id   = eyes_get_or_create_demo_pdf_attachment( 'aviso-de-abertura.pdf', __( 'Aviso de abertura', 'eyes-child' ) );
	$anexo_id    = eyes_get_or_create_demo_pdf_attachment( 'anexo-i.pdf', __( 'Anexo I', 'eyes-child' ) );
	$guia_id     = eyes_get_or_create_demo_pdf_attachment( 'guia-tecnico.pdf', __( 'Guia técnico', 'eyes-child' ) );
	$boas_id     = eyes_get_or_create_demo_pdf_attachment( 'boas-praticas.pdf', __( 'Boas práticas', 'eyes-child' ) );
	$form_a_id   = eyes_get_or_create_demo_pdf_attachment( 'formulario-candidatura-a.pdf', __( 'Formulário de candidatura A', 'eyes-child' ) );
	$form_b_id   = eyes_get_or_create_demo_pdf_attachment( 'formulario-candidatura-b.pdf', __( 'Formulário de candidatura B', 'eyes-child' ) );

	$legal_intro = sprintf(
		/* translators: %s: notice number */
		__( 'A candidatura a este concurso (%s) está sujeita às seguintes condições legais e regulamentares:', 'eyes-child' ),
		$notice ? $notice : get_the_title( $post_id )
	);

	$legislation_summary = __(
		'Estabelece o regime de apoio, elegibilidade das entidades beneficiárias, tipologias de investimento elegíveis e critérios de seleção aplicáveis a este aviso.',
		'eyes-child'
	);

	return array(
		'concurso_legal_intro'   => $legal_intro,
		'concurso_legal_points'  => array(
			array( 'point' => __( 'Elegibilidade das empresas e enquadramento como PME', 'eyes-child' ) ),
			array( 'point' => __( 'Tipos de despesas permitidas e elegíveis', 'eyes-child' ) ),
			array( 'point' => __( 'Importância legal, enquadramento e cumprimento normativo', 'eyes-child' ) ),
			array( 'point' => __( 'Prazo de manutenção dos investimentos e obrigações de reporte', 'eyes-child' ) ),
			array( 'point' => __( 'Critérios de seleção e ponderação aplicáveis', 'eyes-child' ) ),
		),
		'concurso_documents'     => array(
			array(
				'label'         => 'Aviso de abertura.pdf',
				'file'          => $avisio_id,
				'is_complement' => 0,
			),
			array(
				'label'         => 'Anexo I — Despesas elegíveis.pdf',
				'file'          => $anexo_id,
				'is_complement' => 0,
			),
			array(
				'label'         => 'Guia técnico.pdf',
				'file'          => $guia_id,
				'is_complement' => 1,
			),
			array(
				'label'         => 'Boas práticas.pdf',
				'file'          => $boas_id,
				'is_complement' => 1,
			),
		),
		'concurso_application_forms' => array(
			array(
				'label' => __( 'Formulário de candidatura — Modelo A', 'eyes-child' ),
				'file'  => $form_a_id,
			),
			array(
				'label' => __( 'Formulário de candidatura — Modelo B', 'eyes-child' ),
				'file'  => $form_b_id,
			),
		),
		'concurso_legislation'   => array(
			array(
				'title'   => 'Portaria nº 99/2021',
				'summary' => $legislation_summary,
			),
			array(
				'title'   => 'Decreto-Lei nº 70/2021',
				'summary' => __( 'Define o regime jurídico dos apoios à recuperação e resiliência, incluindo regras de elegibilidade e execução financeira.', 'eyes-child' ),
			),
			array(
				'title'   => $entity ? sprintf( 'Regulamento %s', $entity ) : __( 'Regulamento específico do aviso', 'eyes-child' ),
				'summary' => __( 'Documento regulamentar específico deste aviso, com requisitos técnicos, prazos de candidatura e documentação obrigatória.', 'eyes-child' ),
			),
		),
	);
}

/**
 * Assign detail-page taxonomies when missing.
 *
 * @param int $post_id Post ID.
 */
function eyes_assign_concurso_detail_taxonomies( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return;
	}

	$audience_terms = eyes_ensure_concurso_audience_terms();

	$assign_if_empty = function ( $taxonomy, $slugs ) use ( $post_id ) {
		$existing = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $existing ) && ! empty( $existing ) ) {
			return;
		}

		$term_ids = array();
		foreach ( (array) $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				$term_ids[] = (int) $term->term_id;
			}
		}

		if ( $term_ids ) {
			wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
		}
	};

	$legacy_region = (string) get_post_meta( $post_id, 'concurso_region', true );
	$region_map    = array(
		'norte'  => 'continental-norte',
		'centro' => 'continental-centro',
		'sul'    => 'continental-sul',
		'acores' => 'insular-acores',
		'madeira' => 'insular-madeira',
	);

	if ( $legacy_region && isset( $region_map[ $legacy_region ] ) ) {
		$assign_if_empty( 'regiao', array( $region_map[ $legacy_region ] ) );
	}

	$title_lower = strtolower( (string) get_the_title( $post_id ) );

	$activity = (string) get_post_meta( $post_id, 'concurso_activity', true );
	$financing_map = array(
		'incentivos'    => 'subsidio',
		'financiamento' => 'emprestimo',
		'capital_risco' => 'outros',
		'outros'        => 'outros',
	);

	if ( $activity && isset( $financing_map[ $activity ] ) ) {
		$assign_if_empty( 'financing_type', array( $financing_map[ $activity ] ) );
	}

	$sector_slug = '';

	if ( str_contains( $title_lower, 'energ' ) || str_contains( $title_lower, 'turismo' ) ) {
		$sector_slug = str_contains( $title_lower, 'turismo' ) ? 'turismo' : 'energia';
	} elseif ( str_contains( $title_lower, 'digital' ) || str_contains( $title_lower, 'inova' ) || str_contains( $title_lower, 'internacional' ) ) {
		$sector_slug = 'tecnologia';
	} elseif ( str_contains( $title_lower, 'qualifica' ) ) {
		$sector_slug = 'servicos';
	}

	if ( $sector_slug ) {
		$assign_if_empty( 'sector_activity', array( $sector_slug ) );
	}

	$pme_type = (string) get_post_meta( $post_id, 'concurso_type', true );
	$pme_map  = array(
		'micro'   => array( 'micro-empresa' ),
		'pequena' => array( 'pequena-empresa' ),
		'media'   => array( 'media-empresa' ),
	);

	if ( $pme_type && isset( $pme_map[ $pme_type ] ) ) {
		$assign_if_empty( 'enquadramento_pme', $pme_map[ $pme_type ] );
	}

	$audience_slugs = array( 'pme' );
	if ( 'energia' === $sector_slug || str_contains( $title_lower, 'edif' ) ) {
		$audience_slugs[] = 'edificios-empresariais';
	}
	if ( 'tecnologia' === $sector_slug ) {
		$audience_slugs[] = 'startups';
	}
	if ( 'servicos' === $sector_slug ) {
		$audience_slugs[] = 'empresas-servicos';
	}
	if ( 'turismo' === $sector_slug ) {
		$audience_slugs[] = 'empresas-servicos';
	}
	if ( 'industria' === $sector_slug || str_contains( $title_lower, 'indust' ) ) {
		$audience_slugs[] = 'empresas-industriais';
	}

	$audience_ids = array();
	foreach ( array_unique( $audience_slugs ) as $slug ) {
		if ( isset( $audience_terms[ $slug ] ) ) {
			$audience_ids[] = $audience_terms[ $slug ];
		}
	}

	if ( $audience_ids ) {
		$existing_audience = wp_get_object_terms( $post_id, 'concurso_audience', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $existing_audience ) || empty( $existing_audience ) ) {
			wp_set_object_terms( $post_id, $audience_ids, 'concurso_audience', false );
		}
	}
}

/**
 * Fill shared concurso detail fields when empty.
 *
 * @param int $post_id Post ID.
 */
function eyes_fill_concurso_shared_detail_fields( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return;
	}

	$details = get_post_meta( $post_id, 'concurso_details_content', true );
	if ( ! is_string( $details ) || strlen( trim( $details ) ) < 120 ) {
		$title  = get_the_title( $post_id );
		$entity = (string) get_post_meta( $post_id, 'concurso_entity', true );
		$value  = (string) get_post_meta( $post_id, 'concurso_value', true );

		$paragraph = sprintf(
			/* translators: 1: title, 2: entity, 3: value */
			__( '%1$s, promovido por %2$s, disponibiliza um envelope financeiro de %3$s. Este aviso destina-se a empresas que pretendam investir em projetos elegíveis, cumprindo os requisitos de elegibilidade, enquadramento PME e localização geográfica definidos no regulamento.', 'eyes-child' ),
			$title,
			$entity ? $entity : __( 'entidade promotora', 'eyes-child' ),
			$value ? $value : __( 'montante a definir', 'eyes-child' )
		);

		update_post_meta( $post_id, 'concurso_details_content', $paragraph );
	}

	$apply_link = get_post_meta( $post_id, 'concurso_apply_button_link', true );
	if ( ! is_string( $apply_link ) || '' === trim( $apply_link ) ) {
		update_post_meta( $post_id, 'concurso_apply_button_link', 'https://portugal2030.pt/' );
	}
}

/**
 * Seed Pro content for a single concurso.
 *
 * @param int $post_id Post ID.
 */
function eyes_seed_concurso_pro_content_for_post( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || ! function_exists( 'update_field' ) ) {
		return;
	}

	eyes_assign_concurso_detail_taxonomies( $post_id );
	eyes_fill_concurso_shared_detail_fields( $post_id );

	$values = eyes_get_concurso_pro_field_values( $post_id );

	foreach ( $values as $field_name => $field_value ) {
		if ( eyes_concurso_pro_field_has_value( $field_name, $post_id ) ) {
			continue;
		}
		update_field( $field_name, $field_value, $post_id );
	}
}

/**
 * Populate empty Pro fields for every published concurso (does not overwrite edits).
 */
function eyes_seed_all_concurso_pro_content() {
	if ( ! eyes_concurso_pro_auto_seed_enabled() ) {
		return;
	}

	$version = eyes_concurso_pro_seed_version();
	if ( get_option( 'eyes_concurso_pro_content_version' ) === $version ) {
		return;
	}

	if ( ! function_exists( 'update_field' ) ) {
		return;
	}

	$post_ids = get_posts(
		array(
			'post_type'              => 'concurso',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => true,
		)
	);

	foreach ( $post_ids as $post_id ) {
		eyes_seed_concurso_pro_content_for_post( (int) $post_id );
	}

	update_option( 'eyes_concurso_pro_content_version', $version, false );
	delete_option( 'eyes_concurso_pro_demo_seeded' );
}
add_action( 'init', 'eyes_seed_all_concurso_pro_content', 25 );

/**
 * Replace taxonomy slugs on a concurso.
 *
 * @param int           $post_id  Post ID.
 * @param string        $taxonomy Taxonomy slug.
 * @param array<string> $slugs    Term slugs.
 */
function eyes_set_concurso_terms_by_slug( $post_id, $taxonomy, $slugs ) {
	$post_id  = (int) $post_id;
	$term_ids = array();

	foreach ( (array) $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );

		if ( $term && ! is_wp_error( $term ) ) {
			$term_ids[] = (int) $term->term_id;
		}
	}

	wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
}

/**
 * Remove over-assigned regiao / PME terms introduced by demo seeding.
 */
function eyes_fix_concurso_overassigned_taxonomies() {
	$version = '2026-06-12-v1';

	if ( get_option( 'eyes_concurso_taxonomy_fix_version' ) === $version ) {
		return;
	}

	$notice_pme_map = array(
		'SAMPLE-2026-001'             => array( 'micro-empresa', 'pequena-empresa' ),
		'SAMPLE-2026-002'             => array( 'micro-empresa', 'pequena-empresa' ),
		'SAMPLE-2026-003'             => array( 'micro-empresa' ),
		'SAMPLE-2026-004'             => array( 'micro-empresa', 'pequena-empresa' ),
		'SAMPLE-2026-005'             => array( 'micro-empresa' ),
		'Aviso N.º 03/C16-i03/2025' => array( 'micro-empresa' ),
	);

	$post_ids = get_posts(
		array(
			'post_type'              => 'concurso',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $post_ids as $post_id ) {
		$post_id = (int) $post_id;
		$public  = wp_get_object_terms( $post_id, 'concursos_publicos', array( 'fields' => 'slugs' ) );
		$title   = strtolower( (string) get_the_title( $post_id ) );

		if ( ( ! is_wp_error( $public ) && in_array( 'nacional', $public, true ) ) || str_contains( $title, 'nacional' ) ) {
			wp_set_object_terms( $post_id, array(), 'regiao', false );
		} else {
			$regions = wp_get_object_terms( $post_id, 'regiao', array( 'fields' => 'slugs' ) );

			if ( ! is_wp_error( $regions ) && count( $regions ) >= 4 ) {
				wp_set_object_terms( $post_id, array(), 'regiao', false );
			}
		}

		$pme_slugs = wp_get_object_terms( $post_id, 'enquadramento_pme', array( 'fields' => 'slugs' ) );

		if ( ! is_wp_error( $pme_slugs ) && count( $pme_slugs ) >= 3 ) {
			$notice = (string) get_post_meta( $post_id, 'concurso_notice_number', true );
			$type   = (string) get_post_meta( $post_id, 'concurso_type', true );
			$target = $notice_pme_map[ $notice ] ?? array( 'micro-empresa', 'pequena-empresa' );

			if ( 'micro' === $type ) {
				$target = array( 'micro-empresa' );
			}

			eyes_set_concurso_terms_by_slug( $post_id, 'enquadramento_pme', $target );
		}
	}

	update_option( 'eyes_concurso_taxonomy_fix_version', $version, false );
}
add_action( 'init', 'eyes_fix_concurso_overassigned_taxonomies', 24 );

/**
 * Keep one PME term per concurso (profile uses a single PME size).
 */
function eyes_normalize_concurso_pme_terms() {
	$version = '2026-06-12-v3';

	if ( get_option( 'eyes_concurso_pme_normalize_version' ) === $version ) {
		return;
	}

	$single_by_notice = array(
		'SAMPLE-2026-001'             => 'pequena-empresa',
		'SAMPLE-2026-002'             => 'micro-empresa',
		'SAMPLE-2026-003'             => 'micro-empresa',
		'SAMPLE-2026-005'             => 'micro-empresa',
		'Aviso N.º 03/C16-i03/2025' => 'micro-empresa',
	);

	$pme_priority = array( 'micro-empresa', 'pequena-empresa', 'media-empresa' );

	$post_ids = get_posts(
		array(
			'post_type'              => 'concurso',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $post_ids as $post_id ) {
		$post_id = (int) $post_id;
		$notice  = (string) get_post_meta( $post_id, 'concurso_notice_number', true );

		if ( function_exists( 'eyes_concurso_is_open_to_all_pme' ) && eyes_concurso_is_open_to_all_pme( $post_id ) ) {
			wp_set_object_terms( $post_id, array(), 'enquadramento_pme', false );
			continue;
		}

		if ( isset( $single_by_notice[ $notice ] ) ) {
			eyes_set_concurso_terms_by_slug( $post_id, 'enquadramento_pme', array( $single_by_notice[ $notice ] ) );
			continue;
		}

		$pme_slugs = wp_get_object_terms( $post_id, 'enquadramento_pme', array( 'fields' => 'slugs' ) );

		if ( is_wp_error( $pme_slugs ) || count( $pme_slugs ) <= 1 ) {
			continue;
		}

		$chosen = '';

		foreach ( $pme_priority as $slug ) {
			if ( in_array( $slug, $pme_slugs, true ) ) {
				$chosen = $slug;
				break;
			}
		}

		if ( $chosen ) {
			eyes_set_concurso_terms_by_slug( $post_id, 'enquadramento_pme', array( $chosen ) );
		}
	}

	update_option( 'eyes_concurso_pme_normalize_version', $version, false );
}
add_action( 'init', 'eyes_normalize_concurso_pme_terms', 24 );
