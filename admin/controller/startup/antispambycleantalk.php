<?php

namespace Opencart\Admin\Controller\Extension\AntispamByCleantalk\Startup;

use Opencart\System\Library\Extension\AntispamByCleantalk\Core;

/**
 * Loading library on admin side
 * Calling library: $this->registry->get('extension_antispambycleantalk_core')
 */
class AntispamByCleantalk extends \Opencart\System\Engine\Controller
{
    public function index()
    {
        if ( ! $this->registry->get('extension_antispambycleantalk_core') ) {
            $constructor_parameters = [
                $this->registry->db,
                $this->config
            ];
            require_once(DIR_EXTENSION . 'antispambycleantalk/system/library/core.php');
            $this->registry->set('extension_antispambycleantalk_core', new Core($constructor_parameters));
        }
    }
}
