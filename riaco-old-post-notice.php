<?php
/**
 * Plugin Name:       RIACO Old Post Notice
 * Description:       Mostra un avviso sugli articoli che non vengono aggiornati da molto tempo.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Roberto Iacono
 * Author URI:        https://robertoiacono.it
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       riaco-old-post-notice
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Impostazioni di default.
 */
function riaco_opn_defaults() {
	return array(
		'months'  => 12,
		'message' => __( 'This article has not been updated for a while. Some information may be out of date.', 'riaco-old-post-notice' ),
		'auto'    => 1,
	);
}

/**
 * Legge le impostazioni salvate, unite a quelle di default.
 */
function riaco_opn_get_settings() {
	$saved = get_option( 'riaco_opn_settings', array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), riaco_opn_defaults() );
}

/**
 * Restituisce l'HTML dell'avviso, oppure una stringa vuota se l'articolo è recente.
 */
function riaco_opn_get_notice_html( $post_id ) {
	$settings = riaco_opn_get_settings();
	$modified = get_post_modified_time( 'U', true, $post_id );
	$limit    = strtotime( '-' . absint( $settings['months'] ) . ' months' );

	if ( ! $modified || $modified > $limit ) {
		return '';
	}

	return sprintf(
		'<div class="riaco-opn-notice"><p>%s</p><p class="riaco-opn-date">%s %s</p></div>',
		esc_html( $settings['message'] ),
		esc_html__( 'Last updated:', 'riaco-old-post-notice' ),
		esc_html( get_the_modified_date( '', $post_id ) )
	);
}

/**
 * Filter: aggiunge l'avviso in cima al contenuto degli articoli.
 */
function riaco_opn_add_notice( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$settings = riaco_opn_get_settings();

	if ( empty( $settings['auto'] ) ) {
		return $content;
	}

	return riaco_opn_get_notice_html( get_the_ID() ) . $content;
}
add_filter( 'the_content', 'riaco_opn_add_notice' );

/**
 * Action: carica il CSS solo negli articoli singoli.
 */
function riaco_opn_enqueue_style() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_style(
		'riaco-opn',
		plugin_dir_url( __FILE__ ) . 'assets/style.css',
		array(),
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'riaco_opn_enqueue_style' );

/**
 * Shortcode [riaco_old_notice]: mostra l'avviso dove vuoi tu.
 */
function riaco_opn_shortcode() {
	wp_enqueue_style( 'riaco-opn', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), '1.0.0' );

	return riaco_opn_get_notice_html( get_the_ID() );
}
add_shortcode( 'riaco_old_notice', 'riaco_opn_shortcode' );

/**
 * Pagina di impostazioni in Impostazioni > Avviso articoli vecchi.
 */
function riaco_opn_add_settings_page() {
	add_options_page(
		__( 'Old Post Notice', 'riaco-old-post-notice' ),
		__( 'Old Post Notice', 'riaco-old-post-notice' ),
		'manage_options',
		'riaco-opn',
		'riaco_opn_render_settings_page'
	);
}
add_action( 'admin_menu', 'riaco_opn_add_settings_page' );

/**
 * Registra l'opzione, la sezione e i campi con la Settings API.
 */
function riaco_opn_register_settings() {
	register_setting(
		'riaco_opn',
		'riaco_opn_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'riaco_opn_sanitize_settings',
			'default'           => riaco_opn_defaults(),
		)
	);

	add_settings_section( 'riaco_opn_main', '', '__return_false', 'riaco-opn' );

	add_settings_field( 'riaco_opn_months', __( 'Months', 'riaco-old-post-notice' ), 'riaco_opn_field_months', 'riaco-opn', 'riaco_opn_main' );
	add_settings_field( 'riaco_opn_message', __( 'Message', 'riaco-old-post-notice' ), 'riaco_opn_field_message', 'riaco-opn', 'riaco_opn_main' );
	add_settings_field( 'riaco_opn_auto', __( 'Automatic insertion', 'riaco-old-post-notice' ), 'riaco_opn_field_auto', 'riaco-opn', 'riaco_opn_main' );
}
add_action( 'admin_init', 'riaco_opn_register_settings' );

function riaco_opn_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();

	return array(
		'months'  => isset( $input['months'] ) ? max( 1, absint( $input['months'] ) ) : 12,
		'message' => isset( $input['message'] ) ? sanitize_text_field( $input['message'] ) : '',
		'auto'    => empty( $input['auto'] ) ? 0 : 1,
	);
}

function riaco_opn_field_months() {
	$settings = riaco_opn_get_settings();
	printf(
		'<input type="number" min="1" name="riaco_opn_settings[months]" value="%s" class="small-text">',
		esc_attr( $settings['months'] )
	);
}

function riaco_opn_field_message() {
	$settings = riaco_opn_get_settings();
	printf(
		'<input type="text" name="riaco_opn_settings[message]" value="%s" class="large-text">',
		esc_attr( $settings['message'] )
	);
}

function riaco_opn_field_auto() {
	$settings = riaco_opn_get_settings();
	printf(
		'<label><input type="checkbox" name="riaco_opn_settings[auto]" value="1" %s> %s</label>',
		checked( 1, $settings['auto'], false ),
		esc_html__( 'Show the notice at the top of old posts', 'riaco-old-post-notice' )
	);
}

function riaco_opn_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'riaco_opn' );
			do_settings_sections( 'riaco-opn' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Attivazione: salva le impostazioni di default se non esistono già.
 */
function riaco_opn_activate() {
	add_option( 'riaco_opn_settings', riaco_opn_defaults() );
}
register_activation_hook( __FILE__, 'riaco_opn_activate' );
