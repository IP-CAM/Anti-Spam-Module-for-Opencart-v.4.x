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
        if ( !$this->config->get('module_antispambycleantalk_status') ) {
            return;
        }

        $ver = '?v=' . $this->extension_antispambycleantalk_core->getVersion();
        $this->document->addScript(
            'extension/antispambycleantalk/catalog/view/javascript/antispambycleantalk.js' . $ver
        );
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
        if ( !$this->config->get('module_antispambycleantalk_status') ) {
            return;
        }

        $forms_patterns = [
            '@<form\sid="form-register".*>@',
            '@<form\sid="form-return".*>@',
            '@<form\sid="form-contact".*>@',
            '@<form\sid="form-review".*>@',
        ];

        $output = preg_replace_callback($forms_patterns, function ($matches) {
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
        if (
            ! $this->config->get('module_antispambycleantalk_status') ||
            ! $this->config->get('module_antispambycleantalk_check_registrations')
        ) {
            return;
        }

        // Register: Skip checking if the $_POST is empty
        if ( empty($this->request->post) ) {
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
        if (
            ! $this->config->get('module_antispambycleantalk_status') ||
            ! $this->config->get('module_antispambycleantalk_check_orders')
        ) {
            return;
        }

        if ( ! isset($this->session->data['customer']) ) {
            return;
        }

        $this->check('order');
    }

    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
    public function checkReturns(&$route, &$args)
    {
        if (
            ! $this->config->get('module_antispambycleantalk_status') ||
            ! $this->config->get('module_antispambycleantalk_check_return')
        ) {
            return;
        }

        $this->check('return');
    }

    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
    public function checkContactForm(&$route, &$args)
    {
        if (
            ! $this->config->get('module_antispambycleantalk_status') ||
            ! $this->config->get('module_antispambycleantalk_check_contact_form')
        ) {
            return;
        }

        $this->check('contact');
    }

    /**
     * (HOOK) Event handler
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
    public function checkReviews(&$route, &$args)
    {
        if (
            ! $this->config->get('module_antispambycleantalk_status') ||
            ! $this->config->get('module_antispambycleantalk_check_reviews')
        ) {
            return;
        }

        $this->check('comment');
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
        if ( $this->extension_antispambycleantalk_core->isSpam($this, $content_type) ) {
            if ( $content_type === 'order' ) {
                $json['error'] = $this->extension_antispambycleantalk_core->getBlockComment();
            } else {
                $json['error']['warning'] = $this->extension_antispambycleantalk_core->getBlockComment();
            }
            die(json_encode($json));
        }
    }
}
