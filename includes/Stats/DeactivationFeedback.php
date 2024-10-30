<?php

namespace Cartimize\Stats;

use Cartimize\Stats\Actions\ReportCollection;
use Cartimize\Stats\Actions\SendDeactivationReport;
use Cartimize\Controllers\AjaxController;

class DeactivationFeedback{
	
	private $plugin_instace;

	private $ajax_manager;

	public function __construct($plugin_instace){
		$this->plugin_instace = $plugin_instace;
		$this->init();
	}

	public function init(){
		$this->create_objects();
		$this->configure_objects();
	}

	public function create_objects(){
		$this->ajax_manager = new AjaxController( $this->get_ajax_actions() );
	}

	private function configure_objects() {
		$this->ajax_manager->load_all();
	}

	public function get_ajax_actions(){
		return apply_filters('cartimize_deactivation_feedback_ajax_actions', array(
			new ReportCollection( 'report_collection', true, 'wp_ajax_' ),
			new SendDeactivationReport( 'send_report', true, 'wp_ajax_' ),
		));
	}
}