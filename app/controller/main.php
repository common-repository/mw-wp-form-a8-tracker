<?php
class MW_WP_Form_A8_Tracker_Main_Controller
{

	public function __construct()
	{
		$forms = MW_WP_Form_A8_Tracker_Common::get_forms();
		foreach ($forms as $form) {
			add_filter('mwform_complete_content_mw-wp-form-' . $form->ID, array($this, 'mwform_complete_content'), 10, 2);
		}
		wp_enqueue_script('a8sales', '//statics.a8.net/a8sales/a8sales.js', array(), null, false);
	}

	public function mwform_complete_content($content, $data)
	{
		$form_id = $data->get_post_value_by_key('mw-wp-form-form-id');
		$settings = get_post_meta($form_id, MWF_Config::NAME, true);
		if (empty($settings['usedb'])) {
			return $content;
		}

		$form_setting = new MW_WP_Form_Setting($form_id);

		// {tracking_number} が管理者メール本文にない場合は A8 トラッキングコードを出力しない
		$admin_mail_content = $form_setting->get('admin_mail_content');
		if (false === strpos($admin_mail_content, '{tracking_number}')) {
			return $content;
		}

		$a8_tracker_meta = get_post_meta($form_id, 'mw_wp_form_a8_tracker', true);

		if (!$a8_tracker_meta) {
			return $content;
		}

		if (empty($a8_tracker_meta['pid'])) {
			return $content;
		}

		if (empty($a8_tracker_meta['items'])) {
			return $content;
		}

		if (!is_array($a8_tracker_meta['items'])) {
			return $content;
		}

		// 「次」の番号が取得されてしまうため -1 する
		$order_number = $form_setting->get_tracking_number();
		if (0 < $order_number) {
			$order_number--;
		}

		$pid = $a8_tracker_meta['pid'];
		$items = $a8_tracker_meta['items'];

		$total_price = 0;
		foreach ($items as $key => $item) {
			$item['price']    = intval($item['price']);
			$item['quantity'] = intval($item['quantity']);
			$total_price += $item['price'] * $item['quantity'];
			$encoded_item = (object) $item;
			$items[$key] = $encoded_item;
		}
		$total_price = intval($total_price);

		// @codingStandardsIgnoreStart
		$content .= '<span id="a8sales"></span>' . "\n";
		$content .= '<script src="//statics.a8.net/a8sales/a8sales.js"></script>' . "\n";
		$content .= '<script>' . "\n";
		$content .= 'a8sales({' . "\n";
		$content .= '  "pid": "' . esc_attr($pid) . '",' . "\n";
		$content .= '  "order_number": "' . esc_attr($order_number) . '",' . "\n";
		$content .= '  "currency": "JPY",' . "\n";
		$content .= '  "items": ' . json_encode($items) . ',' . "\n";
		$content .= '  "total_price": ' . esc_attr($total_price) . "\n";
		$content .= '});' . "\n";
		$content .= '</script>' . "\n";
		// @codingStandardsIgnoreEnd

		return $content;
	}
}
