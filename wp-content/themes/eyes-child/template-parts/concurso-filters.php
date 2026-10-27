<?php
/**
 * Concurso archive filter bar (search + filter pills).
 *
 * @package Eyes_Child
 *
 * @var array $args {
 *     @type bool $show_search         Whether to render the search field.
 *     @type bool $extended_filters    Whether to show logged-in-only filters.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'show_search'      => true,
		'extended_filters' => function_exists( 'eyes_concurso_has_extended_filters' ) ? eyes_concurso_has_extended_filters() : false,
		'wrapper_class'    => '',
	)
);

$render_taxonomy_filter = function ( $taxonomy, $label, $filter_key, $panel_key, $input_name = null ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return;
	}

	$terms = function_exists( 'eyes_get_terms_for_concurso_filter' )
		? eyes_get_terms_for_concurso_filter( $taxonomy )
		: get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

	if ( empty( $terms ) ) {
		return;
	}

	if ( 'cae_activity' === $taxonomy && function_exists( 'eyes_sort_cae_terms' ) ) {
		$terms = eyes_sort_cae_terms( $terms );
	}

	$input_name = $input_name ?: $taxonomy;
	$panel_id   = 'concursos-filter-panel-' . sanitize_html_class( $panel_key );
	$trigger_id = 'concursos-filter-trigger-' . sanitize_html_class( $panel_key );
	$panel_class = 'cae_activity' === $taxonomy ? ' filter-dropdown__panel--cae' : '';
	?>
	<div class="filter-dropdown" data-filter-dropdown="<?php echo esc_attr( $filter_key ); ?>">
		<button type="button" class="filter-pill filter-dropdown__trigger" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="<?php echo esc_attr( $trigger_id ); ?>">
			<?php echo esc_html( $label ); ?>
			<img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/icon-chevron-down.svg" width="16" height="16" alt="" />
		</button>
		<div class="filter-dropdown__panel<?php echo esc_attr( $panel_class ); ?>" id="<?php echo esc_attr( $panel_id ); ?>" role="group" aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>" hidden>
			<ul class="filter-dropdown__list">
				<?php foreach ( $terms as $term ) : ?>
					<li>
						<label class="filter-dropdown__option">
							<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" />
							<span class="filter-dropdown__checkbox" aria-hidden="true"></span>
							<span class="filter-dropdown__label"><?php echo esc_html( $term->name ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php
};

$render_meta_filter = function ( $input_name, $label, $filter_key, $panel_key, $options ) {
	if ( empty( $options ) ) {
		return;
	}

	$panel_id   = 'concursos-filter-panel-' . sanitize_html_class( $panel_key );
	$trigger_id = 'concursos-filter-trigger-' . sanitize_html_class( $panel_key );
	?>
	<div class="filter-dropdown" data-filter-dropdown="<?php echo esc_attr( $filter_key ); ?>">
		<button type="button" class="filter-pill filter-dropdown__trigger" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" id="<?php echo esc_attr( $trigger_id ); ?>">
			<?php echo esc_html( $label ); ?>
			<img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/icon-chevron-down.svg" width="16" height="16" alt="" />
		</button>
		<div class="filter-dropdown__panel" id="<?php echo esc_attr( $panel_id ); ?>" role="group" aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>" hidden>
			<ul class="filter-dropdown__list">
				<?php foreach ( $options as $value => $option_label ) : ?>
					<li>
						<label class="filter-dropdown__option">
							<input type="checkbox" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
							<span class="filter-dropdown__checkbox" aria-hidden="true"></span>
							<span class="filter-dropdown__label"><?php echo esc_html( $option_label ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php
};

$filter_definitions = function_exists( 'eyes_get_concurso_archive_filter_definitions' )
	? eyes_get_concurso_archive_filter_definitions( (bool) $args['extended_filters'] )
	: array();
?>

<div class="concursos-filters<?php echo ! empty( $args['extended_filters'] ) ? ' concursos-filters--extended' : ''; ?><?php echo ! empty( $args['wrapper_class'] ) ? ' ' . esc_attr( $args['wrapper_class'] ) : ''; ?>">
	<?php if ( $args['show_search'] ) : ?>
		<div class="concursos-search-wrap" data-concursos-search-wrap>
			<label class="concursos-filters__search concursos-search">
				<span class="icon-search" aria-hidden="true"></span>
				<input
					class="concursos-search__input"
					type="search"
					name="q"
					placeholder="<?php esc_attr_e( 'Pesquisar concursos', 'eyes-child' ); ?>"
					autocomplete="off"
					aria-label="<?php esc_attr_e( 'Pesquisar concursos', 'eyes-child' ); ?>"
					aria-expanded="false"
					aria-controls="concursos-search-suggestions"
					data-concursos-search-input
				/>
			</label>
			<div
				class="concursos-search-suggestions"
				id="concursos-search-suggestions"
				data-concursos-search-suggestions
				role="listbox"
				aria-label="<?php esc_attr_e( 'Sugestões de concursos', 'eyes-child' ); ?>"
				hidden
			></div>
		</div>
		<span class="concursos-filters__divider" aria-hidden="true"></span>
	<?php endif; ?>

	<div class="concursos-filters__pills" role="group" aria-label="<?php esc_attr_e( 'Filtrar concursos', 'eyes-child' ); ?>" data-filter-dropdowns>
		<?php foreach ( $filter_definitions as $definition ) : ?>
			<?php
			if ( 'taxonomy' === $definition['type'] ) {
				$render_taxonomy_filter(
					$definition['key'],
					$definition['label'],
					$definition['filter_key'],
					$definition['panel_key']
				);
				continue;
			}

			if ( 'meta' === $definition['type'] && ! empty( $definition['options'] ) ) {
				$render_meta_filter(
					$definition['key'],
					$definition['label'],
					$definition['filter_key'],
					$definition['panel_key'],
					$definition['options']
				);
			}
			?>
		<?php endforeach; ?>
	</div>
</div>
