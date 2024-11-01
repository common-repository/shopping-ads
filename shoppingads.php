<?php
/**
 * Plugin Name: Shopping Ads
 * Description: Permite la activación de la API REST de Woocommerce (solo lectura) y su conexión con Shopping Ads.
 * Version: 1.0.0
 * Author: Shopping Ads
 * Author URI: https://shoppingads.com.ar/app
 * Requires PHP: 5.6.4
 * Requires at least: 5.2.3
 * License: GPL2
 *
 * @package ShoppingAds
 */

/**
 * Generate keys API REST.
 * User admin is the owner of keys.
 * read-only keys.
 *
 * @return void
 */
function shopping_ADS_generate_keys() {
	global $wpdb;

	$generated_key_id = -1;

	$option_value = get_option('shopping_ADS_generated_key_id', -1);

	if ($option_value > 0) return;

	if (current_user_can('administrator')) {

		$description = sprintf(
			__( '%1$s - API %2$s (created on %3$s at %4$s).', 'woocommerce' ),
			wc_clean('REST Activator'), // nombre de la app
			__( 'Read/Write', 'woocommerce' ),  // permisos
			date_i18n( wc_date_format() ),
			date_i18n( wc_time_format() )
		);

		$permissions = 'read';

		$consumer_key    = 'ck_' . wc_rand_hash();
		$consumer_secret = 'cs_' . wc_rand_hash();

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_api_keys',
			array(
				'user_id'         => get_current_user_id(),
				'description'     => $description,
				'permissions'     => $permissions,
				'consumer_key'    => wc_api_hash( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		$generated_key_id = $wpdb->insert_id;

		update_option('shopping_ADS_generated_key_id', $generated_key_id);

		update_option('shopping_ADS_generated_consumer_key', $consumer_key);

		add_option('shopping_ADS_redirected_first_time', true);

	}

}

/**
 * Admin page
 *
 * @return void
 */
function shopping_ADS_render_plugin_page() {
	global $wpdb;

	$option_value = get_option('shopping_ADS_generated_key_id', -1);

	$key_data = null;

	if ($option_value > 0) {

		$key_data = $wpdb->get_row(
			"SELECT consumer_key, consumer_secret 
			FROM {$wpdb->prefix}woocommerce_api_keys 
			WHERE key_id = {$option_value}"
		);

		$key_data->consumer_key = get_option('shopping_ADS_generated_consumer_key');
	}

	?>
	<div class="wrap">
		<?php if ($option_value < 0) : ?>
		<div class="error">
			<p>No se han podido generar las claves para la API REST.</p>
			<p>Para generar las claves, siga los siguientes pasos:</p>
			<ul>
				<li>Desactive el plugin REST Activator</li>
				<li>Inicie sesión como Administrador</li>
				<li>Vuelva a activar el plugin</li>
				<li>Si el problema persiste, repórtelo al proveedor del plugin</li>
			</ul>
		</div>
		<?php else : ?>

		<div style="text-align: center;">
			<img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/shoppingads_banner.jpg">
			<h1 style="font-size: 3rem; text-align: center; font-weight: 300;">Shopping Ads</h1>
			<h3 style="font-weight: 300;">Generaremos un Link entre tu tienda y tu cuenta de Shopping Ads, haz click en el botón para dirigirte a Shopping Ads.</h3>
			<p style="font-weight: 300;">Si no tienes una cuenta, podrás crearte una teniendo tus productos sincronizados.</p>
			
			<a id="link-button" 
				href="https://shoppingads.com.ar/app/woocommerce/link"
				target="_blank">
				<button id="shoppingads-button"
				>CONECTAR SHOPPING ADS</button>
			</a>

			<p class="pie">
				Nota: Este link creado es de <b>solo lectura</b>. <br/>Por seguridad ningun dato se puede modificar. Desde tu cuenta de Shopping Ads solo podrás importar tus productos, no modificar los que existen en esta tienda.
			</p>

			<table class="form-table" style="display: none;">
				<tbody>
					<tr>
						<th scope="row">Consumer Key:</th>
						<td>
							<input id="key_consumer_key" type="text" value="<?=$key_data->consumer_key?>" size="55" readonly="readonly"> <button type="button" class="button-secondary copy-key" data-tip="<?php esc_attr_e( 'Copied!', 'woocommerce' ); ?>" onclick="shopping_ADS_copy_key('key_consumer_key')"><?php esc_html_e( 'Copy', 'woocommerce' ); ?></button>
						</td>
					</tr>
					<tr>
						<th scope="row">Consumer Secret:</th>
						<td>
							<input id="key_consumer_secret" type="text" value="<?=$key_data->consumer_secret?>" size="55" readonly="readonly"> <button type="button" class="button-secondary copy-secret" data-tip="<?php esc_attr_e( 'Copied!', 'woocommerce' ); ?>" onclick="shopping_ADS_copy_key('key_consumer_secret')"><?php esc_html_e( 'Copy', 'woocommerce' ); ?></button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Redirect to admin page plugin after install
 *
 * @return void
 */
function shopping_ADS_redirect_first_time() {
	if (get_option('shopping_ADS_redirected_first_time', false)) {
		delete_option('shopping_ADS_redirected_first_time');
		wp_redirect("admin.php?page=shopping-ads");
		exit;
	}
}

// PLUGIN CONFIG
register_activation_hook( __FILE__, 'shopping_ADS_generate_keys');

add_action('admin_menu', function() {
	add_menu_page(
		__('Shopping Ads', 'rest-on'),
		__('Shopping Ads', 'rest-on'),
		'administrator',
		'shopping-ads',
		'shopping_ADS_render_plugin_page',
		'dashicons-networking',
		76
	);
});

add_action('admin_init', 'shopping_ADS_redirect_first_time');

// Import crypto-js for encrypting in admin section

function shoppingads_script_enqueue_script() {
  global $parent_file;
  if( 'shopping-ads' == $parent_file ) {
  	wp_enqueue_script( 'crypt_js', plugin_dir_url( __FILE__ ) . 'js/crypto-js.min.js' );
  	wp_enqueue_script( 'scripts', plugin_dir_url( __FILE__ ) . 'js/scripts.js' );
  }     
}
add_action( 'admin_print_scripts', 'shoppingads_script_enqueue_script' );

function shoppingads_enqueue_style() {
  global $parent_file;
  if( 'shopping-ads' == $parent_file ) {
  	wp_enqueue_style( 'shoppingads', plugin_dir_url( __FILE__ ) . 'css/style.css' );
  }     
}
add_action( 'admin_print_styles', 'shoppingads_enqueue_style' );
