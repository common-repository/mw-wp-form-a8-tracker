<?php
class MW_WP_Form_A8_Tracker_Admin_Controller {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	public function admin_init() {
		$post_id = filter_input( INPUT_GET, 'post' );
		if ( ! $post_id ) {
			$post_id = filter_input( INPUT_POST, 'post' );
		}

		if ( ! $post_id ) {
			return;
		}

		$settings = get_post_meta( $post_id, MWF_Config::NAME, true );
		if ( empty( $settings['usedb'] ) ) {
			return;
		}

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function save_post( $post_id ) {
		$settings = get_post_meta( $post_id, MWF_Config::NAME, true );
		if ( empty( $settings['usedb'] ) ) {
			return;
		}

		$form_key = MWF_Functions::get_form_key_from_form_id( $post_id );
		add_action( 'mwform_settings_save_' . $form_key, array( $this, 'mwform_settings_save' ) );
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_style(
			'mw-wp-form-a8-tracker-admin',
			MW_WP_FORM_A8_TRACKER_URL . '/assets/css/admin.css',
			array(),
			filemtime( MW_WP_FORM_A8_TRACKER_PATH . '/assets/css/admin.css' )
		);

		wp_enqueue_script(
			'mw-wp-form-a8-tracker-admin',
			MW_WP_FORM_A8_TRACKER_URL . '/assets/js/admin.js',
			array( 'jquery-ui-dialog', 'jquery-ui-sortable', MWF_Config::NAME . '-repeatable' ),
			filemtime( MW_WP_FORM_A8_TRACKER_PATH . '/assets/js/admin.js' )
		);
	}

	public function add_meta_boxes() {
		add_meta_box(
			'mw_wp_form_a8_tracker_metabox',
			esc_html__( 'A8 Tracker', 'mw-wp-form-a8-tracker' ),
			array( $this, 'a8_tracker_metabox' ),
			MWF_Config::NAME,
			'normal'
		);
	}

	public function a8_tracker_metabox( $post ) {
		$meta = get_post_meta( $post->ID, 'mw_wp_form_a8_tracker', true );
		$pid = '';
		$items = array(
			array(
				'code'     => '',
				'price'    => 0,
				'quantity' => 0,
			),
		);
		if ( isset( $meta['pid'] ) ) {
			$pid = $meta['pid'];
		}
		if ( isset( $meta['items'] ) ) {
			$items = array_merge( $items, $meta['items'] );
		}
		?>
		<table border="0" cellpadding="0" cellspacing="4">
			<tr>
				<th><?php esc_html_e( 'pid', 'mw-wp-form-a8-tracker' ); ?></th>
				<td>
					<input type="text" name="mw_wp_form_a8_tracker[pid]" value="<?php echo esc_attr( $pid ); ?>" />
				</td>
			</tr>
		</table>

		<b class="add-btn"><?php esc_html_e( 'Add Item', 'mw-wp-form-a8-tracker' ); ?></b>
		<div class="repeatable-boxes">
			<?php foreach ( $items as $key => $value ) : ?>
			<div class="repeatable-box" <?php echo 0 === $key ? 'style="display: none;"' : ''; ?>>
				<div class="sortable-icon-handle"></div>
				<div class="remove-btn"><b>×</b></div>
				<div class="open-btn"><span><?php echo esc_attr( $value['code'] ); ?></span><b>▼</b></div>
				<div class="repeatable-box-content">
					<table border="0" cellpadding="0" cellspacing="4">
						<tr>
							<th><?php esc_html_e( 'code', 'mw-wp-form-a8-tracker' ); ?></th>
							<td>
								<input type="text" name="mw_wp_form_a8_tracker[items][<?php echo esc_attr( $key ); ?>][code]" value="<?php echo esc_attr( $value['code'] ); ?>" class="targetKey" />
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'price', 'mw-wp-form-a8-tracker' ); ?></th>
							<td>
								<input type="number" name="mw_wp_form_a8_tracker[items][<?php echo esc_attr( $key ); ?>][price]" value="<?php echo esc_attr( $value['price'] ); ?>" />
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'quantity', 'mw-wp-form-a8-tracker' ); ?></th>
							<td>
								<input type="number" name="mw_wp_form_a8_tracker[items][<?php echo esc_attr( $key ); ?>][quantity]" value="<?php echo esc_attr( $value['quantity'] ); ?>" />
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function mwform_settings_save( $post_id ) {
		$new_meta = array();
		$mw_wp_form_a8_tracker = filter_input( INPUT_POST, 'mw_wp_form_a8_tracker', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( isset( $mw_wp_form_a8_tracker['pid'] ) ) {
			$new_meta['pid'] = $mw_wp_form_a8_tracker['pid'];
		}

		if ( isset( $mw_wp_form_a8_tracker['items'] ) && is_array( $mw_wp_form_a8_tracker['items'] ) ) {
			$meta_items = array();
			foreach ( $mw_wp_form_a8_tracker['items'] as $item ) {
				if ( empty( $item['code'] ) ) {
					continue;
				}
				$meta_items[] = $item;
			}
			$new_meta['items'] = $meta_items;
		}

		if ( $new_meta ) {
			update_post_meta(
				$post_id,
				'mw_wp_form_a8_tracker',
				$new_meta
			);
		} else {
			delete_meta( $post_id, 'mw_wp_form_a8_tracker' );
		}
	}
}
