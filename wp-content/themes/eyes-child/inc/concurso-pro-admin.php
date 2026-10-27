<?php
/**
 * Admin UI for Pro concurso fields (ACF Free does not render repeater fields).
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Pro field auto-seeding may write defaults into empty fields.
 */
function eyes_concurso_pro_auto_seed_enabled() {
	return (bool) apply_filters( 'eyes_concurso_pro_auto_seed', true );
}

/**
 * Read a Pro field for the edit screen.
 *
 * @param string $field_name Field name.
 * @param int    $post_id    Post ID.
 */
function eyes_admin_get_concurso_pro_field( $field_name, $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, $post_id );
		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	return get_post_meta( $post_id, $field_name, true );
}

/**
 * Resolve attachment ID from an ACF file value.
 *
 * @param mixed $file File field value.
 */
function eyes_admin_resolve_file_id( $file ) {
	if ( is_numeric( $file ) ) {
		return (int) $file;
	}

	if ( is_array( $file ) && ! empty( $file['ID'] ) ) {
		return (int) $file['ID'];
	}

	return 0;
}

/**
 * Render Pro fields inside Concurso Details → Conteúdo Pro tab.
 *
 * @param array<string, mixed> $field ACF field settings.
 */
function eyes_render_concurso_pro_acf_field( $field ) {
	global $post;

	if ( ! $post instanceof WP_Post || 'concurso' !== $post->post_type ) {
		return;
	}

	eyes_render_concurso_pro_admin_fields( $post );
}
add_action( 'acf/render_field/key=field_eyes_concurso_pro_admin_ui', 'eyes_render_concurso_pro_acf_field' );

/**
 * Enqueue media uploader scripts on concurso edit screens.
 *
 * @param string $hook Current admin page hook.
 */
function eyes_enqueue_concurso_pro_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'concurso' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'eyes-concurso-pro-admin',
		get_stylesheet_directory_uri() . '/assets/js/concurso-pro-admin.js',
		array( 'jquery' ),
		(string) filemtime( get_stylesheet_directory() . '/assets/js/concurso-pro-admin.js' ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'eyes_enqueue_concurso_pro_admin_assets' );

/**
 * Render repeater-style rows for Pro fields.
 *
 * @param WP_Post $post Post object.
 */
function eyes_render_concurso_pro_admin_fields( $post ) {
	$post_id = (int) $post->ID;

	wp_nonce_field( 'eyes_save_concurso_pro_admin', 'eyes_concurso_pro_admin_nonce' );

	$legal_intro = (string) eyes_admin_get_concurso_pro_field( 'concurso_legal_intro', $post_id );
	$legal_points = eyes_admin_get_concurso_pro_field( 'concurso_legal_points', $post_id );
	$documents    = eyes_admin_get_concurso_pro_field( 'concurso_documents', $post_id );
	$forms        = eyes_admin_get_concurso_pro_field( 'concurso_application_forms', $post_id );
	$legislation  = eyes_admin_get_concurso_pro_field( 'concurso_legislation', $post_id );

	if ( ! is_array( $legal_points ) ) {
		$legal_points = array();
	}
	if ( ! is_array( $documents ) ) {
		$documents = array();
	}
	if ( ! is_array( $forms ) ) {
		$forms = array();
	}
	if ( ! is_array( $legislation ) ) {
		$legislation = array();
	}

	?>
	<style>
		.eyes-pro-admin-field-wrap > .acf-label { display: none; }
		.eyes-pro-admin section { margin: 1.25em 0; padding-top: 1em; border-top: 1px solid #dcdcde; }
		.eyes-pro-admin section:first-child { border-top: 0; padding-top: 0; }
		.eyes-pro-admin h3 { margin: 0 0 .75em; font-size: 14px; }
		.eyes-pro-admin .widefat td { vertical-align: middle; }
		.eyes-pro-admin .eyes-pro-row-actions { white-space: nowrap; }
		.eyes-pro-admin textarea.large-text { width: 100%; min-height: 90px; }
		.eyes-pro-admin .eyes-pro-file-preview { display: block; margin-top: 4px; font-size: 12px; color: #50575e; }
	</style>

	<div class="eyes-pro-admin">
		<section>
			<h3><? esc_html_e( 'Base legal — introdução', 'eyes-child' ); ?></h3>
			<textarea class="large-text" name="eyes_pro[concurso_legal_intro]" rows="4"><?php echo esc_textarea( $legal_intro ); ?></textarea>
		</section>

		<section data-eyes-pro-repeater="legal-points">
			<h3><? esc_html_e( 'Base legal — pontos', 'eyes-child' ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><? esc_html_e( 'Ponto', 'eyes-child' ); ?></th>
						<th class="eyes-pro-row-actions"><? esc_html_e( 'Ações', 'eyes-child' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $legal_points as $index => $row ) : ?>
						<?php $point = is_array( $row ) && isset( $row['point'] ) ? (string) $row['point'] : ''; ?>
						<tr>
							<td><input type="text" class="large-text" name="eyes_pro[concurso_legal_points][<?php echo esc_attr( (string) $index ); ?>][point]" value="<?php echo esc_attr( $point ); ?>" /></td>
							<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row"><? esc_html_e( 'Remover', 'eyes-child' ); ?></button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button eyes-pro-add-row" data-row-type="legal-point"><? esc_html_e( 'Adicionar ponto', 'eyes-child' ); ?></button></p>
		</section>

		<section data-eyes-pro-repeater="documents">
			<h3><? esc_html_e( 'Documentos', 'eyes-child' ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><? esc_html_e( 'Nome', 'eyes-child' ); ?></th>
						<th><? esc_html_e( 'Ficheiro', 'eyes-child' ); ?></th>
						<th><? esc_html_e( 'Complemento', 'eyes-child' ); ?></th>
						<th class="eyes-pro-row-actions"><? esc_html_e( 'Ações', 'eyes-child' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $documents as $index => $row ) : ?>
						<?php
						$label = is_array( $row ) && isset( $row['label'] ) ? (string) $row['label'] : '';
						$file_id = is_array( $row ) ? eyes_admin_resolve_file_id( $row['file'] ?? 0 ) : 0;
						$is_complement = is_array( $row ) && ! empty( $row['is_complement'] ) ? 1 : 0;
						$file_label = $file_id ? basename( (string) get_attached_file( $file_id ) ) : '';
						?>
						<tr>
							<td><input type="text" class="large-text" name="eyes_pro[concurso_documents][<?php echo esc_attr( (string) $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" /></td>
							<td>
								<input type="hidden" class="eyes-pro-file-id" name="eyes_pro[concurso_documents][<?php echo esc_attr( (string) $index ); ?>][file]" value="<?php echo esc_attr( (string) $file_id ); ?>" />
								<button type="button" class="button eyes-pro-select-file"><?php esc_html_e( 'Escolher ficheiro', 'eyes-child' ); ?></button>
								<span class="eyes-pro-file-preview"><?php echo esc_html( $file_label ); ?></span>
							</td>
							<td><label><input type="checkbox" name="eyes_pro[concurso_documents][<?php echo esc_attr( (string) $index ); ?>][is_complement]" value="1" <?php checked( $is_complement, 1 ); ?> /> <? esc_html_e( 'Sim', 'eyes-child' ); ?></label></td>
							<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row"><? esc_html_e( 'Remover', 'eyes-child' ); ?></button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button eyes-pro-add-row" data-row-type="document"><? esc_html_e( 'Adicionar documento', 'eyes-child' ); ?></button></p>
		</section>

		<section data-eyes-pro-repeater="forms">
			<h3><? esc_html_e( 'Formulários de candidatura', 'eyes-child' ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><? esc_html_e( 'Nome', 'eyes-child' ); ?></th>
						<th><? esc_html_e( 'Ficheiro', 'eyes-child' ); ?></th>
						<th class="eyes-pro-row-actions"><? esc_html_e( 'Ações', 'eyes-child' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $forms as $index => $row ) : ?>
						<?php
						$label = is_array( $row ) && isset( $row['label'] ) ? (string) $row['label'] : '';
						$file_id = is_array( $row ) ? eyes_admin_resolve_file_id( $row['file'] ?? 0 ) : 0;
						$file_label = $file_id ? basename( (string) get_attached_file( $file_id ) ) : '';
						?>
						<tr>
							<td><input type="text" class="large-text" name="eyes_pro[concurso_application_forms][<?php echo esc_attr( (string) $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" /></td>
							<td>
								<input type="hidden" class="eyes-pro-file-id" name="eyes_pro[concurso_application_forms][<?php echo esc_attr( (string) $index ); ?>][file]" value="<?php echo esc_attr( (string) $file_id ); ?>" />
								<button type="button" class="button eyes-pro-select-file"><?php esc_html_e( 'Escolher ficheiro', 'eyes-child' ); ?></button>
								<span class="eyes-pro-file-preview"><?php echo esc_html( $file_label ); ?></span>
							</td>
							<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row"><? esc_html_e( 'Remover', 'eyes-child' ); ?></button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button eyes-pro-add-row" data-row-type="form"><? esc_html_e( 'Adicionar formulário', 'eyes-child' ); ?></button></p>
		</section>

		<section data-eyes-pro-repeater="legislation">
			<h3><? esc_html_e( 'Legislação associada', 'eyes-child' ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><? esc_html_e( 'Título', 'eyes-child' ); ?></th>
						<th><? esc_html_e( 'Resumo', 'eyes-child' ); ?></th>
						<th class="eyes-pro-row-actions"><? esc_html_e( 'Ações', 'eyes-child' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $legislation as $index => $row ) : ?>
						<?php
						$title = is_array( $row ) && isset( $row['title'] ) ? (string) $row['title'] : '';
						$summary = is_array( $row ) && isset( $row['summary'] ) ? (string) $row['summary'] : '';
						?>
						<tr>
							<td><input type="text" class="large-text" name="eyes_pro[concurso_legislation][<?php echo esc_attr( (string) $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" /></td>
							<td><textarea class="large-text" rows="3" name="eyes_pro[concurso_legislation][<?php echo esc_attr( (string) $index ); ?>][summary]"><?php echo esc_textarea( $summary ); ?></textarea></td>
							<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row"><? esc_html_e( 'Remover', 'eyes-child' ); ?></button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button eyes-pro-add-row" data-row-type="legislation"><? esc_html_e( 'Adicionar legislação', 'eyes-child' ); ?></button></p>
		</section>
	</div>
	<?php
}

/**
 * Save Pro fields from the custom meta box.
 *
 * @param int $post_id Post ID.
 */
function eyes_save_concurso_pro_admin_meta_box( $post_id ) {
	if ( ! isset( $_POST['eyes_concurso_pro_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eyes_concurso_pro_admin_nonce'] ) ), 'eyes_save_concurso_pro_admin' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'concurso' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( empty( $_POST['eyes_pro'] ) || ! is_array( $_POST['eyes_pro'] ) ) {
		return;
	}

	$input = wp_unslash( $_POST['eyes_pro'] );

	$legal_intro = isset( $input['concurso_legal_intro'] ) ? sanitize_textarea_field( $input['concurso_legal_intro'] ) : '';
	$legal_points = array();
	$documents    = array();
	$forms        = array();
	$legislation  = array();

	if ( ! empty( $input['concurso_legal_points'] ) && is_array( $input['concurso_legal_points'] ) ) {
		foreach ( $input['concurso_legal_points'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$point = isset( $row['point'] ) ? sanitize_text_field( $row['point'] ) : '';
			if ( '' === trim( $point ) ) {
				continue;
			}
			$legal_points[] = array( 'point' => $point );
		}
	}

	if ( ! empty( $input['concurso_documents'] ) && is_array( $input['concurso_documents'] ) ) {
		foreach ( $input['concurso_documents'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
			$file  = isset( $row['file'] ) ? absint( $row['file'] ) : 0;
			if ( '' === trim( $label ) && $file <= 0 ) {
				continue;
			}
			$documents[] = array(
				'label'         => $label,
				'file'          => $file,
				'is_complement' => ! empty( $row['is_complement'] ) ? 1 : 0,
			);
		}
	}

	if ( ! empty( $input['concurso_application_forms'] ) && is_array( $input['concurso_application_forms'] ) ) {
		foreach ( $input['concurso_application_forms'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
			$file  = isset( $row['file'] ) ? absint( $row['file'] ) : 0;
			if ( '' === trim( $label ) && $file <= 0 ) {
				continue;
			}
			$forms[] = array(
				'label' => $label,
				'file'  => $file,
			);
		}
	}

	if ( ! empty( $input['concurso_legislation'] ) && is_array( $input['concurso_legislation'] ) ) {
		foreach ( $input['concurso_legislation'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
			$summary = isset( $row['summary'] ) ? sanitize_textarea_field( $row['summary'] ) : '';
			if ( '' === trim( $title ) && '' === trim( $summary ) ) {
				continue;
			}
			$legislation[] = array(
				'title'   => $title,
				'summary' => $summary,
			);
		}
	}

	$values = array(
		'concurso_legal_intro'       => $legal_intro,
		'concurso_legal_points'      => $legal_points,
		'concurso_documents'       => $documents,
		'concurso_application_forms' => $forms,
		'concurso_legislation'     => $legislation,
	);

	foreach ( $values as $field_name => $field_value ) {
		if ( function_exists( 'update_field' ) ) {
			update_field( $field_name, $field_value, $post_id );
		} else {
			update_post_meta( $post_id, $field_name, $field_value );
		}
	}
}
add_action( 'save_post_concurso', 'eyes_save_concurso_pro_admin_meta_box' );
