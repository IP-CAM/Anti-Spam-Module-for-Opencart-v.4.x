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
        if( ! $this->config->get('module_antispambycleantalk_status') ) {
            return;
        }

        if ( ! $this->registry->get('extension_antispambycleantalk_core') ) {
            $constructor_parameters = [
                $this->registry->db,
                $this->config,
                $this
            ];
            $this->load->library('extension/antispambycleantalk/core', $constructor_parameters);
        }

        // Checking and performing remote call
        if( $this->extension_antispambycleantalk_core->rc->check($this->config->get('module_antispambycleantalk_access_key')) ) {
            $this->extension_antispambycleantalk_core->rc->perform($this->config->get('module_antispambycleantalk_access_key'));
        } elseif ( $this->request->server['REQUEST_METHOD'] === 'GET' )
        {
            $this->extension_antispambycleantalk_core->sfw->run($this->config->get('module_antispambycleantalk_access_key'));

            $settings_array = array();

            if ($this->config->get('module_antispambycleantalk_int_sfw_last_check') && time() - $this->config->get('module_antispambycleantalk_int_sfw_last_check') > 86400)
            {
                $this->extension_antispambycleantalk_core->sfw->sfw_update($this->config->get('module_antispambycleantalk_access_key'));
                $settings_array['module_antispambycleantalk_int_sfw_last_check'] = time();
            }

            if ($this->config->get('module_antispambycleantalk_int_sfw_last_send_logs') && time() - $this->config->get('module_antispambycleantalk_int_sfw_last_send_logs') > 3600)
            {
                $this->extension_antispambycleantalk_core->sfw->logs__send($this->config->get('module_antispambycleantalk_access_key'));
                $settings_array['module_antispambycleantalk_int_sfw_last_send_logs'] = time();
            }
            if (count($settings_array) > 0)
            {
                foreach ( $settings_array as $setting_name => $setting_value) {
                    $this->db->query( 'UPDATE `' . DB_PREFIX . 'setting` SET `value` = "' . $setting_value . '" WHERE `key` = "' . $setting_name . '"' );
                }
            }
        }
    }
}
