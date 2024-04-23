<?php

namespace Opencart\Admin\Controller\Extension\AntispamByCleantalk\Module;

class AntispamByCleantalk extends \Opencart\System\Engine\Controller
{
    /**
     * @var string
     */
    private $path = 'extension/antispambycleantalk/module/antispambycleantalk';

    /**
     * @var string
     */
    private $module = 'module_antispambycleantalk';

    /**
     * @var array
     */
    private $settings = [
        'access_key',
        'status',
        'check_registrations',
        'check_orders',
        'check_return',
        'check_contact_form',
        'check_reviews',
        'enable_sfw'
    ];

    /**
     * @var string
     */
    private $event = 'extension/antispambycleantalk/event/antispambycleantalk';

    public function index()
    {
        $this->load->language($this->path);

        $this->document->setTitle($this->language->get('heading_title_without_logo'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=module',
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_without_logo'),
            'href' => $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link(
            $this->path . '|save',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        $data['cancel'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=module',
            true
        );


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Get settings value from DB
        foreach ( $this->settings as $setting_name ) {
            $data[$this->module . '_' . $setting_name] = $this->config->get($this->module . '_' . $setting_name);
        }

        $this->response->setOutput($this->load->view($this->path, $data));
    }

    public function save()
    {
        $this->load->language($this->path);
        $json = [];

        if ( !$this->user->hasPermission('modify', $this->path) ) {
            $json['error'] = $this->language->get('error_permission');
        }

        if ( isset($this->request->post['module_antispambycleantalk_enable_sfw']) && isset($this->request->post['module_antispambycleantalk_access_key']) ) {
            $this->extension_antispambycleantalk_core->sfw->sfwUpdate(
                $this->request->post['module_antispambycleantalk_access_key']
            );
            $this->extension_antispambycleantalk_core->sfw->logsSend(
                $this->request->post['module_antispambycleantalk_access_key']
            );
            $this->request->post['module_antispambycleantalk_int_sfw_last_check'] = time();
            $this->request->post['module_antispambycleantalk_int_sfw_last_send_logs'] = time();
        }

        if ( !$json ) {
            $this->init();
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting($this->module, $this->request->post);
            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function init()
    {
        $this->model_setting_startup->deleteStartupByCode($this->module);
        $this->model_setting_startup->addStartup([
            'code' => $this->module,
            'description' => 'Anti-Spam by CleanTalk',
            'action' => 'catalog/extension/antispambycleantalk/startup/antispambycleantalk',
            'status' => true,
            'sort_order' => 1
        ]);
        $this->model_setting_startup->addStartup([
            'code' => $this->module,
            'description' => 'Anti-Spam by CleanTalk',
            'action' => 'admin/extension/antispambycleantalk/startup/antispambycleantalk',
            'status' => true,
            'sort_order' => 1
        ]);

        $this->model_setting_event->deleteEventByCode($this->module);
        $events = [
            'injecting JS to the document' => [
                $this->event . '.injectJs' => [
                    'catalog/controller/common/header/before',
                ]
            ],
            'adding hidden field' => [
                $this->event . '.addHiddenField' => [
                    'catalog/view/account/register/after',
                    'catalog/view/checkout/register/after',
                    'catalog/view/account/returns_form',
                    'catalog/view/information/contact',
                    'catalog/view/product/review',
                ]
            ],
            'checking register' => [
                $this->event . '.checkRegister' => [
                    'catalog/controller/account/register.register/before',
                    'catalog/controller/checkout/register.save/before',
                ]
            ],
            'checking order' => [
                $this->event . '.checkOrder' => [
                    'catalog/controller/extension/opencart/payment/bank_transfer.confirm/before',
                    'catalog/controller/extension/opencart/payment/cheque.confirm/before',
                    'catalog/controller/extension/opencart/payment/cod.confirm/before',
                    'catalog/controller/extension/opencart/payment/free_checkout.confirm/before',
                ]
            ],
            'checking returns' => [
                $this->event . '.checkReturns' => [
                    'catalog/controller/account/returns.save/before',
                ]
            ],
            'checking contact form' => [
                $this->event . '.checkContactForm' => [
                    'catalog/controller/information/contact.send/before',
                ]
            ],
            'checking reviews' => [
                $this->event . '.checkReviews' => [
                    'catalog/controller/product/review.write/before',
                ]
            ],
        ];
        foreach ( $events as $event_description => $event_group ) {
            foreach ( $event_group as $event_name => $events ) {
                foreach ( $events as $event ) {
                    $this->model_setting_event->addEvent([
                        'code' => $this->module,
                        'description' => 'Hook: Anti-Spam by CleanTalk ' . $event_description,
                        'trigger' => $event,
                        'action' => $event_name,
                        'status' => true,
                        'sort_order' => 1
                    ]);
                }
            }
        }

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cleantalk_sfw` (
            `network` int(10) unsigned NOT NULL, 
            `mask` int(10) unsigned NOT NULL, 
            `status` TINYINT(1) NOT NULL DEFAULT 0,
		    INDEX (  `network` ,  `mask` )
		)"
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cleantalk_sfw_logs` (
            `ip` varchar(15) NOT NULL, 
            `all_entries` int(11) NOT NULL, 
            `blocked_entries` int(11) NOT NULL, 
            `entries_timestamp` int(11) NOT NULL, 
            PRIMARY KEY `ip` (`ip`)
		)"
        );
    }

    public function install()
    {
        if ( $this->user->hasPermission('modify', $this->path) ) {
            $this->init();
        }
    }

    public function uninstall()
    {
        if ( $this->user->hasPermission('modify', $this->path) ) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->deleteSetting($this->module);
            $this->model_setting_startup->deleteStartupByCode($this->module);
            $this->model_setting_event->deleteEventByCode($this->module);
            $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cleantalk_sfw");
            $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cleantalk_sfw_logs");
        }
    }
}
