<?php
/**
 * Pro-user email alerts when a new matching concurso is published.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post meta key: alert already sent for this concurso.
 */
function eyes_concurso_alert_sent_meta_key() {
	return 'eyes_pro_alert_sent';
}

/**
 * Whether Pro alerts are enabled (filterable for tests/staging).
 */
function eyes_pro_new_fund_alerts_enabled() {
	return (bool) apply_filters( 'eyes_pro_new_fund_alerts_enabled', true );
}

/**
 * Whether a concurso is eligible for the first Pro alert send.
 *
 * @param int $post_id Post ID.
 */
function eyes_concurso_ready_for_pro_alert( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || 'concurso' !== get_post_type( $post_id ) ) {
		return false;
	}

	if ( 'publish' !== get_post_status( $post_id ) ) {
		return false;
	}

	if ( get_post_meta( $post_id, eyes_concurso_alert_sent_meta_key(), true ) ) {
		return false;
	}

	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	return 'abertos' === (string) get_field( 'concurso_status', $post_id );
}

/**
 * Human-readable match status label for emails.
 *
 * @param string $status match|national|open|no_match.
 */
function eyes_format_match_status_label( $status ) {
	$labels = array(
		'match'     => __( 'Corresponde ao seu perfil', 'eyes-child' ),
		'national'  => __( 'Programa nacional (todas as regiões)', 'eyes-child' ),
		'open'      => __( 'Aberto a todos os enquadramentos PME', 'eyes-child' ),
		'no_match'  => __( 'Não corresponde', 'eyes-child' ),
	);

	return $labels[ $status ] ?? $status;
}

/**
 * Build the Pro alert email for one user.
 *
 * @param int                  $post_id   Concurso ID.
 * @param int                  $user_id   User ID.
 * @param array<string, mixed> $breakdown Match breakdown from eyes_get_concurso_match_breakdown().
 * @return array{subject:string,body:string}|null
 */
function eyes_build_pro_new_fund_email( $post_id, $user_id, $breakdown ) {
	$user = get_userdata( $user_id );

	if ( ! $user || ! is_email( $user->user_email ) ) {
		return null;
	}

	$title = function_exists( 'eyes_get_concurso_meta_value' )
		? (string) eyes_get_concurso_meta_value( $post_id, 'concurso_title' )
		: get_the_title( $post_id );

	if ( '' === trim( $title ) ) {
		$title = get_the_title( $post_id );
	}

	$entity   = function_exists( 'eyes_get_concurso_meta_value' )
		? (string) eyes_get_concurso_meta_value( $post_id, 'concurso_entity' )
		: '';
	$deadline = function_exists( 'eyes_get_concurso_meta_value' ) && function_exists( 'eyes_format_concurso_deadline' )
		? eyes_format_concurso_deadline( eyes_get_concurso_meta_value( $post_id, 'concurso_deadline' ) )
		: '';
	$region_label = function_exists( 'eyes_get_concurso_detail_region_label' )
		? eyes_get_concurso_detail_region_label( $post_id )
		: eyes_get_taxonomy_term_list( $post_id, 'regiao' );
	$pme_label = eyes_get_taxonomy_term_list( $post_id, 'enquadramento_pme' );

	$subject = sprintf(
		/* translators: %s: concurso title */
		__( '[Solvian Eyes] Nova oportunidade para si: %s', 'eyes-child' ),
		$title
	);

	$dashboard_url = add_query_arg(
		'dashboard_tab',
		'recomendadas',
		get_post_type_archive_link( 'concurso' ) ?: home_url( '/concurso/' )
	);

	$lines = array(
		sprintf(
			/* translators: %s: user display name */
			__( 'Olá %s,', 'eyes-child' ),
			$user->display_name
		),
		'',
		__( 'Foi publicado um novo fundo na Solvian Eyes que corresponde ao perfil da sua empresa.', 'eyes-child' ),
		'',
		sprintf( '%s: %s', __( 'Oportunidade', 'eyes-child' ), $title ),
	);

	if ( $entity ) {
		$lines[] = sprintf( '%s: %s', __( 'Entidade', 'eyes-child' ), $entity );
	}

	if ( $deadline ) {
		$lines[] = sprintf( '%s: %s', __( 'Prazo', 'eyes-child' ), $deadline );
	}

	if ( $region_label ) {
		$lines[] = sprintf( '%s: %s', __( 'Região do concurso', 'eyes-child' ), $region_label );
	}

	if ( $pme_label ) {
		$lines[] = sprintf( '%s: %s', __( 'Enquadramento PME', 'eyes-child' ), $pme_label );
	}

	$lines[] = '';
	$lines[] = __( 'Correspondência com o seu perfil:', 'eyes-child' );
	$lines[] = sprintf(
		'- %s: %s (%s)',
		__( 'Região', 'eyes-child' ),
		$breakdown['user_regiao'],
		eyes_format_match_status_label( $breakdown['region_status'] )
	);
	$lines[] = sprintf(
		'- %s: %s (%s)',
		__( 'Enquadramento PME', 'eyes-child' ),
		$breakdown['user_pme_label'],
		eyes_format_match_status_label( $breakdown['pme_status'] )
	);
	$lines[] = sprintf(
		'- %s: %d%%',
		__( 'Pontuação de correspondência', 'eyes-child' ),
		(int) $breakdown['score']
	);
	$lines[] = '';
	$lines[] = sprintf( '%s: %s', __( 'Ver oportunidade', 'eyes-child' ), get_permalink( $post_id ) );
	$lines[] = sprintf( '%s: %s', __( 'As suas recomendações', 'eyes-child' ), $dashboard_url );
	$lines[] = '';
	$lines[] = __( 'Este alerta é exclusivo para utilizadores Pro.', 'eyes-child' );
	$lines[] = '';
	$lines[] = __( 'Solvian Eyes', 'eyes-child' );

	return array(
		'subject' => $subject,
		'body'    => implode( "\n", $lines ),
	);
}

/**
 * Send a one-off sample Pro alert email (does not mark the concurso as notified).
 *
 * @param string   $email   Recipient email.
 * @param int|null $post_id Optional concurso ID; uses the latest open concurso when omitted.
 * @return true|WP_Error
 */
function eyes_send_pro_new_fund_sample_email( $email, $post_id = null ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Email inválido.', 'eyes-child' ) );
	}

	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Utilizador não encontrado.', 'eyes-child' ) );
	}

	if ( ! function_exists( 'eyes_user_has_pro_plan' ) || ! eyes_user_has_pro_plan( $user->ID ) ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Este utilizador não tem plano Pro.', 'eyes-child' ) );
	}

	if ( ! eyes_user_profile_ready_for_matching( null, $user->ID ) ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Complete localidade e enquadramento PME no perfil.', 'eyes-child' ) );
	}

	if ( null === $post_id ) {
		$candidates = get_posts(
			array(
				'post_type'      => 'concurso',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'concurso_status',
						'value'   => 'abertos',
						'compare' => '=',
					),
				),
			)
		);

		if ( empty( $candidates ) ) {
			return new WP_Error( 'eyes_pro_alert_sample', __( 'Não existem concursos abertos para usar no email de teste.', 'eyes-child' ) );
		}

		$post_id = (int) $candidates[0]->ID;
	}

	$post_id = (int) $post_id;

	if ( $post_id <= 0 || 'concurso' !== get_post_type( $post_id ) ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Concurso inválido.', 'eyes-child' ) );
	}

	$profile   = eyes_get_user_match_profile( $user->ID );
	$breakdown = eyes_get_concurso_match_breakdown( $post_id, $profile );
	$email_data = eyes_build_pro_new_fund_email( $post_id, $user->ID, $breakdown );

	if ( ! $email_data ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Não foi possível preparar o email.', 'eyes-child' ) );
	}

	$subject = '[TESTE] ' . $email_data['subject'];
	$body    = $email_data['body'] . "\n\n" . __( '(Email de teste — os próximos alertas serão enviados apenas quando publicar um novo fundo.)', 'eyes-child' );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	$sent    = wp_mail( $email, $subject, $body, $headers );

	if ( ! $sent ) {
		return new WP_Error( 'eyes_pro_alert_sample', __( 'Não foi possível enviar o email. Verifique a configuração SMTP.', 'eyes-child' ) );
	}

	return true;
}

/**
 * Send new-fund alerts to matching Pro users.
 *
 * @param int $post_id Concurso ID.
 * @return int Number of emails sent.
 */
function eyes_notify_pro_users_new_concurso( $post_id ) {
	$post_id = (int) $post_id;

	if ( ! eyes_pro_new_fund_alerts_enabled() || ! eyes_concurso_ready_for_pro_alert( $post_id ) ) {
		return 0;
	}

	if ( ! function_exists( 'eyes_get_pro_user_ids' ) || ! function_exists( 'eyes_get_concurso_match_breakdown' ) ) {
		return 0;
	}

	$pro_users = eyes_get_pro_user_ids();

	if ( empty( $pro_users ) ) {
		update_post_meta( $post_id, eyes_concurso_alert_sent_meta_key(), current_time( 'mysql' ) );
		return 0;
	}

	$minimum_score = eyes_get_match_minimum_score();
	$sent          = 0;

	foreach ( $pro_users as $user_id ) {
		$user_id = (int) $user_id;

		if ( $user_id <= 0 || ! function_exists( 'eyes_user_has_pro_plan' ) || ! eyes_user_has_pro_plan( $user_id ) ) {
			continue;
		}

		if ( ! eyes_user_profile_ready_for_matching( null, $user_id ) ) {
			continue;
		}

		$profile   = eyes_get_user_match_profile( $user_id );
		$breakdown = eyes_get_concurso_match_breakdown( $post_id, $profile );

		if ( (int) $breakdown['score'] < $minimum_score ) {
			continue;
		}

		$email = eyes_build_pro_new_fund_email( $post_id, $user_id, $breakdown );

		if ( ! $email ) {
			continue;
		}

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		if ( wp_mail( get_userdata( $user_id )->user_email, $email['subject'], $email['body'], $headers ) ) {
			$sent++;
		}
	}

	update_post_meta( $post_id, eyes_concurso_alert_sent_meta_key(), current_time( 'mysql' ) );

	return $sent;
}

/**
 * Trigger Pro alerts after ACF saves an open published concurso.
 *
 * @param int|string $post_id Post ID from ACF.
 */
function eyes_maybe_notify_pro_users_new_concurso( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	eyes_notify_pro_users_new_concurso( $post_id );
}
add_action( 'acf/save_post', 'eyes_maybe_notify_pro_users_new_concurso', 25 );
