<?php

namespace Opencart\Catalog\Controller\Extension\AntispamByCleantalk\Module;

class AntispamByCleantalk extends \Opencart\System\Engine\Controller {
	public function index() {
		$this->load->language('extension/module/module');

		$data['module'] = '';

        error_log(var_export($this->registry->get('extension_antispambycleantalk_core'),1));
		return $this->load->view('extension/module/module', $data);
	}

	public function check() {
		$error = false;

		return !$error;
	}
}
