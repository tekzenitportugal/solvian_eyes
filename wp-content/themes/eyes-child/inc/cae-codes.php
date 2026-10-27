<?php
/**
 * Portuguese CAE (Classificação Portuguesa das Atividades Económicas) codes.
 *
 * Used by the concurso archive filter and registration form.
 * Expand this list or replace with a full REV3 import as needed.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * CAE codes keyed by 4-digit code => activity label.
 *
 * @return array<string, string>
 */
function eyes_get_cae_codes() {
	static $codes = null;

	if ( null !== $codes ) {
		return $codes;
	}

	$codes = array(
		'1111' => 'Cerealicultura (excepto arroz)',
		'1112' => 'Cultura de leguminosas secas e sementes oleaginosas',
		'1120' => 'Cultura de arroz',
		'1130' => 'Culturas de produtos hortícolas e melões, raízes e tubérculos',
		'1131' => 'Cultura de produtos hortícolas e melões',
		'1132' => 'Cultura de raízes e tubérculos',
		'1140' => 'Cultura de cana-de-açúcar',
		'1150' => 'Cultura de tabaco',
		'1160' => 'Cultura de plantas têxteis',
		'1191' => 'Cultura de flores e de plantas ornamentais',
		'1192' => 'Outras culturas agrícolas permanentes, n.e.',
		'1210' => 'Viticultura',
		'1220' => 'Cultura de frutos tropicais e subtropicais',
		'1230' => 'Cultura de citrinos',
		'1240' => 'Cultura de pomóideas e frutos de caroço',
		'1251' => 'Cultura de frutos de casca rija',
		'1252' => 'Cultura de outros frutos em árvores e arbustos',
		'1260' => 'Cultura de frutos oleaginosos',
		'1270' => 'Cultura de plantas para bebidas',
		'1280' => 'Cultura de especiarias, plantas aromáticas, medicinais e farmacêuticas',
		'1410' => 'Criação de bovinos para produção de leite',
		'1420' => 'Criação de outros bovinos e búfalos',
		'1430' => 'Criação de cavalos e outros equinos',
		'1440' => 'Criação de camelos e camelídeos',
		'1450' => 'Criação de ovinos e caprinos',
		'1460' => 'Suinicultura',
		'1470' => 'Avicultura',
		'1490' => 'Outra produção animal, n.e.',
		'1510' => 'Agricultura mista',
		'1610' => 'Atividades de apoio à agricultura',
		'1620' => 'Atividades de apoio à produção animal',
		'1630' => 'Atividades pós-colheita',
		'1640' => 'Preparação de sementes para propagação',
		'1700' => 'Caça, repovoamento cinegético e atividades dos serviços relacionados',
		'2100' => 'Silvicultura e outras atividades florestais',
		'2200' => 'Exploração florestal',
		'2300' => 'Extração de cortiça, resina e de outros produtos florestais',
		'2400' => 'Atividades de apoio à silvicultura e à exploração florestal',
		'3110' => 'Pesca marítima',
		'3120' => 'Pesca em águas interiores',
		'3210' => 'Aquicultura marítima',
		'3220' => 'Aquicultura em águas interiores',
		'10110' => 'Processamento e conservação de carne, excepto carne de aves',
		'10120' => 'Processamento e conservação de carne de aves',
		'10200' => 'Processamento e conservação de peixes, crustáceos e moluscos',
		'10310' => 'Processamento e conservação de batatas',
		'10320' => 'Fabricação de sumos de frutos e de produtos hortícolas',
		'10390' => 'Outro processamento e conservação de frutos e de produtos hortícolas',
		'10410' => 'Fabricação de óleos e gorduras',
		'10420' => 'Fabricação de margarinas e de gorduras alimentares similares',
		'10510' => 'Operações de laticínios e queijarias',
		'10520' => 'Fabricação de gelados e sorvetes',
		'10610' => 'Fabricação de produtos amiláceos',
		'10620' => 'Fabricação de amidos e de produtos amiláceos',
		'10710' => 'Fabricação de pão; fabricação de produtos de pastelaria fresca',
		'10720' => 'Fabricação de bolachas, biscoitos e produtos de pastelaria de conservação',
		'10730' => 'Fabricação de massas alimentícias, cuscuz e similares',
		'10810' => 'Fabricação de açúcar',
		'10820' => 'Fabricação de cacau, chocolate e de produtos de confeitaria',
		'10830' => 'Processamento de chá e de café',
		'10840' => 'Fabricação de condimentos e temperos',
		'10850' => 'Fabricação de refeições e pratos pré-cozinhados',
		'10860' => 'Fabricação de preparados alimentares homogeneizados e de alimentos dietéticos',
		'10890' => 'Fabricação de outros produtos alimentares, n.e.',
		'10910' => 'Fabricação de rações para animais de criação',
		'10920' => 'Fabricação de alimentos para animais de estimação',
		'11010' => 'Destilação, retificação e mistura de bebidas alcoólicas',
		'11020' => 'Fabricação de vinhos',
		'11030' => 'Fabricação de cidra e de outros vinhos de frutos',
		'11040' => 'Fabricação de outras bebidas fermentadas não destiladas',
		'11050' => 'Fabricação de cerveja',
		'11060' => 'Fabricação de malte',
		'11070' => 'Fabricação de refrigerantes; produção de águas minerais e de outras águas engarrafadas',
		'62010' => 'Actividades de programação informática',
		'62020' => 'Actividades de consultoria em informática',
		'62030' => 'Gestão e exploração de equipamento informático',
		'62090' => 'Outras actividades relacionadas com as tecnologias da informação e informática',
		'70220' => 'Outras actividades de consultoria para os negócios e a gestão',
		'71120' => 'Actividades de engenharia e técnicas afins',
		'72110' => 'Investigação e desenvolvimento experimental em biotecnologia',
		'72190' => 'Outra investigação e desenvolvimento experimental em ciências físicas e naturais',
		'72200' => 'Investigação e desenvolvimento experimental em ciências sociais e humanas',
	);

	/**
	 * Allow filtering or extending the CAE list from a plugin or child theme.
	 */
	$codes = apply_filters( 'eyes_cae_codes', $codes );

	return $codes;
}

/**
 * Format a CAE code and label for display.
 *
 * @param string $code  CAE code.
 * @param string $label Activity label.
 */
function eyes_format_cae_label( $code, $label ) {
	return $code . ' - ' . $label;
}

/**
 * Seed cae_activity taxonomy terms from the CAE list.
 */
function eyes_seed_cae_activity_terms() {
	if ( ! taxonomy_exists( 'cae_activity' ) ) {
		return;
	}

	$seed_version = '2026-06-06';
	if ( get_option( 'eyes_cae_terms_version' ) === $seed_version ) {
		return;
	}

	$codes       = eyes_get_cae_codes();
	$valid_slugs = array_map( 'sanitize_title', array_keys( $codes ) );

	foreach ( $codes as $code => $label ) {
		$slug = sanitize_title( $code );
		$name = eyes_format_cae_label( $code, $label );
		$existing = term_exists( $slug, 'cae_activity' );

		if ( $existing ) {
			wp_update_term(
				(int) $existing['term_id'],
				'cae_activity',
				array(
					'name' => $name,
					'slug' => $slug,
				)
			);
			continue;
		}

		wp_insert_term(
			$name,
			'cae_activity',
			array(
				'slug' => $slug,
			)
		);
	}

	$existing_terms = get_terms(
		array(
			'taxonomy'   => 'cae_activity',
			'hide_empty' => false,
			'fields'     => 'all',
		)
	);

	if ( ! is_wp_error( $existing_terms ) ) {
		foreach ( $existing_terms as $term ) {
			if ( in_array( $term->slug, $valid_slugs, true ) ) {
				continue;
			}

			wp_delete_term( $term->term_id, 'cae_activity' );
		}
	}

	update_option( 'eyes_cae_terms_version', $seed_version, false );
}
add_action( 'init', 'eyes_seed_cae_activity_terms', 20 );

/**
 * Sort CAE terms by numeric code.
 *
 * @param WP_Term[] $terms Terms to sort.
 * @return WP_Term[]
 */
function eyes_sort_cae_terms( $terms ) {
	usort(
		$terms,
		static function ( $a, $b ) {
			return strnatcmp( $a->slug, $b->slug );
		}
	);

	return $terms;
}
