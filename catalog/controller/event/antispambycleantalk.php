<?php

namespace Opencart\Catalog\Controller\Extension\AntispamByCleantalk\Event;

class AntispamByCleantalk extends \Opencart\System\Engine\Controller
{
    /**
     * @var string
     */
    private $path = 'extension/antispambycleantalk/module/antispambycleantalk';

	public function injectJs(&$route, &$args)
    {
        if( ! $this->config->get('module_antispambycleantalk_status') ) {
            return;
        }
        $ver = '?v=' . $this->extension_antispambycleantalk_core->getVersion();
        $this->document->addScript('extension/antispambycleantalk/catalog/view/javascript/antispambycleantalk.js' . $ver);
        $this->document->addScript('https://moderate.cleantalk.org/ct-bot-detector-wrapper.js');
        $this->extension_antispambycleantalk_core->setCookie();
	}

    public function addHiddenField(&$route, &$args, &$output)
    {
        $output = preg_replace_callback('@<form\sid="form-register".*>@', function ($matches){
            $hidden_field = '<input type="hidden" name="ct_checkjs" id="ct_checkjs" value="0" />';
            return $matches[0] . $hidden_field;
        }, $output);
    }

    public function checkRegister(&$route, &$args)
    {
        if ( empty($this->request->post) ) {
            return;
        }

        if ($this->config->get('module_antispambycleantalk_status') && $this->config->get('module_antispambycleantalk_check_registrations'))
        {
            if( $this->extension_antispambycleantalk_core->isSpam($this) )
            {
                $json['error']['warning'] =$this->extension_antispambycleantalk_core->get_block_comment();
                die(json_encode($json));
            }
        }
    }
}
