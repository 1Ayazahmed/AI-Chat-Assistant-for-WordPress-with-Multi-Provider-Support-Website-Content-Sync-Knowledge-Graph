<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEAC_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ceac_chatbot';
	}

	public function get_title() {
		return __( 'AI Assistant Chatbot', 'ceac' );
	}

	public function get_icon() {
		return 'eicon-comments';
	}

	public function get_categories() {
		return array( 'general' );
	}

	protected function render() {
		echo do_shortcode( '[ceac_chatbot inline="true"]' );
	}
}
