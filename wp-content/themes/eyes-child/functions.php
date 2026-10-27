<?php
/**
 * Eyes Child Theme bootstrap.
 *
 * @package Eyes_Child
 */

defined( 'ABSPATH' ) || exit;

$eyes_child_includes = array(
	'inc/setup.php',
	'inc/helpers.php',
	'inc/post-types.php',
	'inc/cae-codes.php',
	'inc/financing-types.php',
	'inc/sector-activities.php',
	'inc/concurso-public-filters.php',
	'inc/concurso-dashboard.php',
	'inc/concurso-matching.php',
	'inc/concurso-notifications.php',
	'inc/concurso-filters.php',
	'inc/concurso-detail.php',
	'inc/acf-concurso-pro.php',
	'inc/acf-concurso-details-sync.php',
	'inc/concurso-pro-admin.php',
	'inc/profile.php',
	'inc/planos.php',
	'inc/auth.php',
	'inc/report-problem.php',
	'inc/acf-report-problem.php',
	'inc/enqueue.php',
);

foreach ( $eyes_child_includes as $eyes_child_file ) {
	$eyes_child_path = get_stylesheet_directory() . '/' . $eyes_child_file;

	if ( is_readable( $eyes_child_path ) ) {
		require $eyes_child_path;
	}
}
