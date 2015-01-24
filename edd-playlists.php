<?php
/**
 * Plugin Name: Easy Digital Downloads - Playlists
 */

class EDD_Playlists {
	
	private $plugin_url;
	private $plugin_dir;
	
	public function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_dir = plugin_dir_path( __FILE__ );

		add_filter( 'edd_download_price_table_head', array( $this, 'edd_download_price_table_head' ) );
		add_action( 'edd_download_price_table_row', array( $this, 'edd_download_price_table_row' ), 10, 3 );
		add_filter( 'edd_price_row_args', array( $this, 'edd_price_row_args' ), 10, 2 );
		
		add_action( 'edd_after_price_option', array( $this, 'edd_after_price_option' ), 10, 3 );

		add_action( 'admin_enqueue_scripts', array( $this, 'edd_load_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		add_filter( 'edd_purchase_download_form', array( $this, 'edd_purchase_download_form' ), 10, 2 );
	}

	public function edd_purchase_download_form( $form, $args ) {
		$form = str_replace( 'class="edd_download_purchase_form ', 'class="edd_download_purchase_form playlist ', $form );

		return $form;
	}

	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'edd-playlists', $this->plugin_url . 'assets/css/app.css' );
	}

	public function edd_load_admin_scripts( $hook ) {
		if ( ! apply_filters( 'edd_load_admin_scripts', edd_is_admin_page(), $hook ) ) {
			return;
		}

		wp_enqueue_script( 'edd-playlists', $this->plugin_url . 'assets/js/app.min.js', array( 'jquery' ) );

		$localize = array(
			'i18n' => array(
				'title' => __( 'Choose an Audio File', 'edd-playlists' ),
				'button' => __( 'Use File', 'edd-playlists' )
			)
		);

		wp_localize_script( 'edd-playlists', 'EDDPlaylists', $localize );
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
		echo '<th width="55">' . __( 'Preview', 'edd-playlists' ) . '</th>';
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

new EDD_Playlists;
