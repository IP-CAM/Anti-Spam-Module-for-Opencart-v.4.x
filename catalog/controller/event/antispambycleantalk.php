<?php

namespace Opencart\Catalog\Controller\Extension\AntispamByCleantalk\Event;

class AntispamByCleantalk extends \Opencart\System\Engine\Controller
{
    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
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

    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     * @param string $output
     *
     * @return void
     */
    public function addHiddenField(&$route, &$args, &$output)
    {
        if( ! $this->config->get('module_antispambycleantalk_status') ) {
            return;
        }

        $forms_patterns = [
            '@<form\sid="form-register".*>@',
            '@<form\sid="form-return".*>@',
        ];

        $output = preg_replace_callback($forms_patterns, function ($matches){
            $hidden_field = '<input type="hidden" name="ct_checkjs" id="ct_checkjs" value="0" />';
            return $matches[0] . $hidden_field;
        }, $output);
    }

    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
    public function checkRegister(&$route, &$args)
    {
        if ( ! $this->config->get('module_antispambycleantalk_status') || ! $this->config->get('module_antispambycleantalk_check_registrations')  ) {
            return;
        }

        // Register: Skip checking if the $_POST is empty
        if ( empty($this->request->post) ) {
            return;
        }

        // Checkout: Skip checking for the guests checkout
        if ( isset($this->request->post['account']) && $this->request->post['account'] === '0' ) {
            return;
        }

        $this->check('register');

    }

    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
    public function checkOrder(&$route, &$args)
    {
        // @todo Not implemented!
        return;

        if ( ! $this->config->get('module_antispambycleantalk_status') || ! $this->config->get('module_antispambycleantalk_check_orders')  ) {
            return;
        }

        // Register: Skip checking if the $_POST is empty
        if ( empty($this->request->post) ) {
            return;
        }

        // Checkout: Check orders for only guests customers
        if ( isset($this->request->post['account']) && $this->request->post['account'] === '0' ) {
            $this->check('order');
        }
    }

    public function checkReturns(&$route, &$args)
    {
        if ( ! $this->config->get('module_antispambycleantalk_status') || ! $this->config->get('module_antispambycleantalk_check_return')  ) {
            return;
        }

        $this->check('return');
    }

    /**
     * Wrapper for checking spam by various comment types
     *
     * @param string $content_type
     *
     * @return void
     */
    private function check($content_type = '')
    {
        if( $this->extension_antispambycleantalk_core->isSpam($this, $content_type) ) {
            //$this->response->setOutput($this->load->view('extension/antispambycleantalk/module/antispambycleantalk', ['block_message'=>$this->extension_antispambycleantalk_core->get_block_comment()]));
            $json['error']['warning'] = $this->extension_antispambycleantalk_core->get_block_comment();
            die(json_encode($json));
        }
    }
}
