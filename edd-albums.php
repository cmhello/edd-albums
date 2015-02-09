<?php
/**
 * Plugin Name: Easy Digital Downloads - Albums
 * Plugin URI: http://easydigitaldownloads.com/extensions/albums
 * Description: Output a preview button for audio files in Easy Digital Downloads.
 * Author: Spencer Finnell
 * Author URI: http://github.com/spencerfinnell
 * Version: 1.0.0
 * Text Domain: edd-albums
 * Domain Path: languages
 */

if( ! class_exists( 'EDD_License' ) ) {
	include( dirname( __FILE__ ) . '/license/EDD_License_Handler.php' );
}

if( class_exists( 'EDD_License' ) )
    $license = new EDD_License( __FILE__, 'Easy Digital Downloads - Albums', '1.0.0', 'Spencer Finnell' );
}

class EDD_Albums {

	private static $instance;

	private $plugin_url;
	private $plugin_dir;
	private $suffix;
	
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Albums ) ) {
			self::$instance = new EDD_Albums;
		}

		return self::$instance;
	}
	
	public function __construct() {
		if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			return;
		}

		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_dir = plugin_dir_path( __FILE__ );

		$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// textdomain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// admin
		add_filter( 'edd_download_price_table_head', array( $this, 'edd_download_price_table_head' ) );
		add_action( 'edd_download_price_table_row', array( $this, 'edd_download_price_table_row' ), 10, 3 );
		add_filter( 'edd_price_row_args', array( $this, 'edd_price_row_args' ), 10, 2 );
		
		// frontend
		add_action( 'edd_after_price_option', array( $this, 'edd_after_price_option' ), 10, 3 );
		add_filter( 'edd_purchase_download_form', array( $this, 'edd_purchase_download_form' ), 10, 2 );

		add_filter( 'body_class', array( $this, 'body_class' ) );

		// scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'edd_load_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
	}
	
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-albums' );

		load_textdomain( 'edd-albums', WP_LANG_DIR . "/edd-albums/edd-albums-$locale.mo" );
		load_plugin_textdomain( 'edd-albums', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'edd-albums', $this->plugin_url . 'assets/css/app' . $this->suffix . '.css' );
	}

	public function edd_load_admin_scripts( $hook ) {
		if ( ! apply_filters( 'edd_load_admin_scripts', edd_is_admin_page(), $hook ) ) {
			return;
		}

		wp_enqueue_script( 'edd-albums', $this->plugin_url . 'assets/js/app' . $this->suffix . '.js', array( 'jquery' ) );

		$localize = array(
			'i18n' => array(
				'title' => __( 'Choose an Audio File', 'edd-albums' ),
				'button' => __( 'Use File', 'edd-albums' )
			)
		);

		wp_localize_script( 'edd-albums', 'EDDPlaylists', $localize );
	}

	public function edd_download_price_table_row( $post_id, $key, $args ) {
	?>
		<td class="edd-preview" style="text-align: center;">
			<a href="#" class="edd-add-preview"><span class="dashicons dashicons-format-audio"></span></a>
			<input type="hidden" name="edd_variable_prices[<?php echo $key; ?>][preview]" value="<?php echo $args[ 'preview' ]; ?>" class="edd_repeatable_index" />
		</td>
	<?php
	}

	public function edd_price_row_args( $args, $value ) {
		$args[ 'preview' ] = isset( $value[ 'preview' ] ) ? $value[ 'preview' ] : '';

		return $args;
	}

	public function edd_download_price_table_head( $post_id ) {
		echo '<th width="55">' . __( 'Preview', 'edd-albums' ) . '</th>';
	}

	public function body_class( $classes ) {
		$theme = wp_get_theme();

		$classes[] = 'theme-' . sanitize_title( $theme->Name );

		return $classes;
	}

	public function edd_purchase_download_form( $form, $args ) {
		$form = str_replace( 'class="edd_download_purchase_form ', 'class="edd_download_purchase_form playlist ', $form );

		return $form;
	}

	public function edd_after_price_option( $key, $price, $download_id ) {
		if ( '' == $price[ 'preview' ] ) {
			return;
		}

		$audio = wp_get_attachment_url( $price[ 'preview' ] );

		echo wp_audio_shortcode( array(
			'src' => $audio
		) );
	}
}

function edd_albums() {
	return EDD_Albums::instance();
}
add_action( 'plugins_loaded', 'edd_albums', 5 );
