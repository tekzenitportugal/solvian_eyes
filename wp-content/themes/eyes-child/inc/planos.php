<?php
/**
 * Plan selection and subscription helpers.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Planos page URL.
 */
function eyes_planos_url() {
	return home_url( '/planos/' );
}

/**
 * Plan catalogue used by the planos page.
 *
 * @return array<string, array<string, mixed>>
 */
function eyes_get_plan_definitions() {
	return array(
		'guest'    => array(
			'id'       => 'guest',
			'name'     => __( 'Plano Gratuito (S/ Registo)', 'eyes-child' ),
			'price'    => 0,
			'features' => array(
				__( 'Acesso à lista de projetos', 'eyes-child' ),
				__( 'Filtros básicos', 'eyes-child' ),
			),
		),
		'free'     => array(
			'id'       => 'free',
			'name'     => __( 'Plano Gratuito (C/ Registo)', 'eyes-child' ),
			'price'    => 0,
			'features' => array(
				__( 'Acesso à lista de projetos', 'eyes-child' ),
				__( 'Pesquisa avançada e mapa interativo', 'eyes-child' ),
				__( 'Favoritos, histórico e alertas', 'eyes-child' ),
			),
		),
		'advanced' => array(
			'id'       => 'advanced',
			'name'     => __( 'Plano Avançado', 'eyes-child' ),
			'monthly'  => 30,
			'annual'   => 300,
			'features' => array(
				__( 'Acesso à lista de projetos', 'eyes-child' ),
				__( 'Pesquisa avançada e mapa interativo', 'eyes-child' ),
				__( 'Favoritos, histórico e alertas', 'eyes-child' ),
				__( 'Apoio especializado à candidatura', 'eyes-child' ),
				__( 'Legislação consolidada por oportunidade', 'eyes-child' ),
				__( 'Formulários de candidatura editáveis', 'eyes-child' ),
				__( 'Diagnóstico gratuito (3x ano)', 'eyes-child' ),
				__( 'Match inteligente com IA (brevemente)', 'eyes-child' ),
				__( 'Chatbot de apoio', 'eyes-child' ),
				__( 'Sem fidelização', 'eyes-child' ),
			),
		),
	);
}

/**
 * Billing period labels.
 *
 * @return array<string, string>
 */
function eyes_get_plan_billing_periods() {
	return array(
		'monthly' => __( 'Mensal', 'eyes-child' ),
		'annual'  => __( 'Anual', 'eyes-child' ),
	);
}

/**
 * Current plan slug for a user session.
 *
 * @param int $user_id User ID.
 */
function eyes_get_user_current_plan( $user_id = 0 ) {
	if ( ! is_user_logged_in() && 0 === $user_id ) {
		return 'guest';
	}

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( $user_id <= 0 ) {
		return 'guest';
	}

	if ( eyes_user_has_pro_plan( $user_id ) ) {
		return 'advanced';
	}

	return 'free';
}

/**
 * Button label/state for a plan card on the planos page.
 *
 * Only the first plan shows "Plano atual" for non-Pro users.
 *
 * @param string $plan_id      guest|free|advanced.
 * @param string $current_plan Current plan slug.
 * @return array{label: string, disabled: bool, current: bool}
 */
function eyes_get_planos_card_button( $plan_id, $current_plan ) {
	$is_logged_in = is_user_logged_in();
	$is_pro       = 'advanced' === $current_plan;

	if ( 'guest' === $plan_id ) {
		if ( ! $is_pro ) {
			return array(
				'label'    => __( 'Plano atual', 'eyes-child' ),
				'disabled' => true,
				'current'  => true,
			);
		}

		return array(
			'label'    => __( 'Seleccionar', 'eyes-child' ),
			'disabled' => false,
			'current'  => false,
		);
	}

	if ( 'free' === $plan_id ) {
		if ( $is_logged_in && ! $is_pro ) {
			return array(
				'label'    => __( 'Incluído', 'eyes-child' ),
				'disabled' => true,
				'current'  => false,
			);
		}

		return array(
			'label'    => __( 'Seleccionar', 'eyes-child' ),
			'disabled' => false,
			'current'  => false,
		);
	}

	if ( $is_pro ) {
		return array(
			'label'    => __( 'Plano atual', 'eyes-child' ),
			'disabled' => true,
			'current'  => true,
		);
	}

	return array(
		'label'    => __( 'Seleccionar', 'eyes-child' ),
		'disabled' => false,
		'current'  => false,
	);
}

/**
 * Persist a plan choice for the current user.
 *
 * @param int    $user_id User ID.
 * @param string $plan    guest|free|advanced.
 * @param string $billing monthly|annual.
 * @return true|WP_Error
 */
function eyes_save_user_plan_selection( $user_id, $plan, $billing = 'monthly' ) {
	$user_id = (int) $user_id;
	$plan    = sanitize_key( $plan );
	$billing = sanitize_key( $billing );

	if ( $user_id <= 0 ) {
		return new WP_Error( 'eyes_planos', __( 'Tem de iniciar sessão para seleccionar um plano.', 'eyes-child' ) );
	}

	$allowed_plans = array( 'free', 'advanced' );

	if ( ! in_array( $plan, $allowed_plans, true ) ) {
		return new WP_Error( 'eyes_planos', __( 'Plano inválido.', 'eyes-child' ) );
	}

	if ( 'advanced' === $plan ) {
		if ( ! in_array( $billing, array( 'monthly', 'annual' ), true ) ) {
			$billing = 'monthly';
		}

		update_user_meta( $user_id, 'eyes_plan', 'pro' );
		update_user_meta( $user_id, 'eyes_plan_billing', $billing );
		update_user_meta( $user_id, 'eyes_plan_selected_at', current_time( 'mysql' ) );
	} else {
		delete_user_meta( $user_id, 'eyes_plan' );
		delete_user_meta( $user_id, 'eyes_plan_billing' );
		delete_user_meta( $user_id, 'eyes_plan_selected_at' );
	}

	return true;
}

/**
 * Summary shown on the success step.
 *
 * @param int $user_id User ID.
 * @return array<string, string>
 */
function eyes_get_plan_success_summary( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	$billing = sanitize_key( (string) get_user_meta( $user_id, 'eyes_plan_billing', true ) );

	if ( ! in_array( $billing, array( 'monthly', 'annual' ), true ) ) {
		$billing = 'monthly';
	}

	$periods = eyes_get_plan_billing_periods();
	$amount  = 'annual' === $billing ? 300 : 30;

	$profile = function_exists( 'eyes_get_user_profile_form_data' )
		? eyes_get_user_profile_form_data( $user_id )
		: array();

	$address_parts = array_filter(
		array(
			$profile['endereco'] ?? '',
			$profile['localidade'] ?? '',
		)
	);

	return array(
		'billing_label' => $periods[ $billing ],
		'price_label'   => sprintf(
			/* translators: %d: plan price in euros */
			__( '%d€ (renovação automática)', 'eyes-child' ),
			$amount
		),
		'company'       => $profile['empresa'] ?? '',
		'nif'           => $profile['nif'] ?? '',
		'address'       => implode( ' ', $address_parts ),
	);
}

/**
 * AJAX: save plan selection (payment stub — activates Pro without charging).
 */
function eyes_ajax_select_plan() {
	check_ajax_referer( 'eyes_planos', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'Tem de iniciar sessão para continuar.', 'eyes-child' ),
			),
			401
		);
	}

	$plan    = isset( $_POST['plan'] ) ? sanitize_key( wp_unslash( $_POST['plan'] ) ) : '';
	$billing = isset( $_POST['billing'] ) ? sanitize_key( wp_unslash( $_POST['billing'] ) ) : 'monthly';
	$result  = eyes_save_user_plan_selection( get_current_user_id(), $plan, $billing );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			array(
				'message' => $result->get_error_message(),
			),
			400
		);
	}

	wp_send_json_success(
		array(
			'summary' => eyes_get_plan_success_summary( get_current_user_id() ),
		)
	);
}
add_action( 'wp_ajax_eyes_select_plan', 'eyes_ajax_select_plan' );

/**
 * Whether the current request is the planos page.
 */
function eyes_is_planos_page() {
	return is_page( 'planos' );
}

/**
 * User IDs with an active Pro plan.
 *
 * @return int[]
 */
function eyes_get_pro_user_ids() {
	$user_ids = get_users(
		array(
			'meta_key'   => 'eyes_plan',
			'meta_value' => 'pro',
			'fields'     => 'ID',
		)
	);

	return array_values( array_filter( array_map( 'absint', (array) $user_ids ) ) );
}
