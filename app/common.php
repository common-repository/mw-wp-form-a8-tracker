<?php
class MW_WP_Form_A8_Tracker_Common {

	public static function get_forms() {
		return get_posts(
			array(
				'post_type'      => MWF_Config::NAME,
				'posts_per_page' => -1,
			)
		);
	}

}
