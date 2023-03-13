<?php

namespace Opencart\Catalog\Controller\Extension\AntispamByCleantalk\Startup;

/**
 * Loading library on client side
 * Calling library: $this->registry->get('extension_antispambycleantalk_core')
 */
class AntispamByCleantalk extends \Opencart\System\Engine\Controller
{
    public function index()
    {
        if ( ! $this->registry->get('extension_antispambycleantalk_core') ) {
            $constructor_parameters = [
                $this->registry->db,
                $this->config,
                $this
            ];
            $this->load->library('extension/antispambycleantalk/core', $constructor_parameters);
        }
    }
}
