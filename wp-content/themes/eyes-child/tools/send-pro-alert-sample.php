<?php
/**
 * One-off CLI script: send a sample Pro new-fund alert email.
 *
 * Usage: php send-pro-alert-sample.php vinu.puthuvaya@tekzenit.com
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$email = $argv[1] ?? '';

if ( '' === $email ) {
	fwrite( STDERR, "Usage: php send-pro-alert-sample.php user@example.com\n" );
	exit( 1 );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Could not load WordPress at {$wp_load}\n" );
	exit( 1 );
}

require $wp_load;

if ( ! function_exists( 'eyes_send_pro_new_fund_sample_email' ) ) {
	fwrite( STDERR, "eyes_send_pro_new_fund_sample_email() not available.\n" );
	exit( 1 );
}

$result = eyes_send_pro_new_fund_sample_email( $email );

if ( is_wp_error( $result ) ) {
	fwrite( STDERR, 'Error: ' . $result->get_error_message() . "\n" );
	exit( 1 );
}

echo "Sample Pro alert sent to {$email}\n";
