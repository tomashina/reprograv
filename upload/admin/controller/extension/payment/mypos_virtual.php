<?php
class ControllerExtensionPaymentMyposVirtual extends Controller {

    public function index() {
        // Load language
        $this->load->language('extension/payment/mypos_virtual');

        // Load settings
        $this->load->model('setting/setting');

        // Set document title
        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->session->data['success']))
        {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        // If isset request to change settings
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            // Default values
            if ($this->request->post['payment_mypos_virtual_developer_url'] == null)
            {
                $this->request->post['payment_mypos_virtual_developer_url'] = 'https://www.mypos.com/vmp/checkout-test';
            }

            if ($this->request->post['payment_mypos_virtual_production_url'] == null)
            {
                $this->request->post['payment_mypos_virtual_production_url'] = 'https://www.mypos.com/vmp/checkout';
            }

            if (!$this->config->has('payment_mypos_virtual_test_prefix')) {
                $this->request->post['payment_mypos_virtual_test_prefix'] = uniqid() . '_';
            } else {
            	$this->request->post['payment_mypos_virtual_test_prefix'] = $this->config->get('payment_mypos_virtual_test_prefix');
            }

            // Edit settings
            $this->model_setting_setting->editSetting('payment_mypos_virtual', $this->request->post);

            // Set success message
            $this->session->data['success'] = $this->language->get('text_success');

            // Return to extensions page
            $this->response->redirect($this->url->link('extension/payment/mypos_virtual', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        }

        // Load default layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Load language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_mypos_join'] = $this->language->get('text_mypos_join');
        $data['help_total'] = $this->language->get('help_total');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_full_requests'] = $this->language->get('text_full_requests');
        $data['text_simplified_request'] = $this->language->get('text_simplified_request');
        $data['text_simplified_payment_page'] = $this->language->get('text_simplified_payment_page');

        $data['text_payment_method'] = $this->language->get('text_payment_method');
        $data['text_card_payment'] = $this->language->get('text_card_payment');
        $data['text_ideal'] = $this->language->get('text_ideal');
        $data['text_all'] = $this->language->get('text_all');

        // Field entries
        $data['entry_status']			   = $this->language->get('entry_status');
        $data['entry_test']		           = $this->language->get('entry_test');
        $data['entry_sid']				   = $this->language->get('entry_sid');
        $data['entry_wallet_number']	   = $this->language->get('entry_wallet_number');
        $data['entry_private_key']		   = $this->language->get('entry_private_key');
        $data['entry_public_certificate']  = $this->language->get('entry_public_certificate');
        $data['entry_developer_keyindex']  = $this->language->get('entry_developer_keyindex');
        $data['entry_production_keyindex'] = $this->language->get('entry_production_keyindex');
        $data['entry_developer_url']	   = $this->language->get('entry_developer_url');
        $data['entry_production_url']	   = $this->language->get('entry_production_url');
        $data['entry_sort_order']  	       = $this->language->get('entry_sort_order');
        $data['entry_logging']  	       = $this->language->get('entry_logging');
        $data['entry_ppr']                 = $this->language->get('entry_ppr');
        $data['configuration_package']     = $this->language->get('configuration_package');

        // Tooltips
        $data['tooltip_sid'] = $this->language->get('tooltip_sid');
        $data['tooltip_wallet_number'] = $this->language->get('tooltip_wallet_number');
        $data['tooltip_private_key'] = $this->language->get('tooltip_private_key');
        $data['tooltip_public_certificate'] = $this->language->get('tooltip_public_certificate');
        $data['tooltip_keyindex'] = $this->language->get('tooltip_keyindex');
        $data['tooltip_ppr'] = $this->language->get('tooltip_ppr');
        $data['tooltip_config_pack'] = $this->language->get('tooltip_config_pack');

        $data['text_easy_setup'] = $this->language->get('easy_setup');
        $data['text_advanced_setup'] = $this->language->get('advanced_setup');
        $data['text_generate_new_pack'] = $this->language->get('generate_new_pack');
        $data['text_configure'] = $this->language->get('configure');

        // Load breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/mypos_virtual', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Load action buttons urls
        $data['action'] = $this->url->link('extension/payment/mypos_virtual', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL');

        // Set default values for fields

        // Status of payment module Enabled/Disabled
        if (isset($this->request->post['payment_mypos_virtual_status'])) {
            $data['payment_mypos_virtual_status'] = $this->request->post['payment_mypos_virtual_status'];
        } else {
            $data['payment_mypos_virtual_status'] = $this->config->get('payment_mypos_virtual_status');
        }

        // Logging Enabled/Disabled
        if (isset($this->request->post['payment_mypos_virtual_logging'])) {
            $data['payment_mypos_virtual_logging'] = $this->request->post['payment_mypos_virtual_logging'];
        } else {
            $data['payment_mypos_virtual_logging'] = $this->config->get('payment_mypos_virtual_logging');
        }

        // Sort order
        if (isset($this->request->post['payment_mypos_virtual_sort_order'])) {
            $data['payment_mypos_virtual_sort_order'] = $this->request->post['payment_mypos_virtual_sort_order'];
        } else {
            $data['payment_mypos_virtual_sort_order'] = $this->config->get('payment_mypos_virtual_sort_order');
        }

        // Test / Production
        if (isset($this->request->post['payment_mypos_virtual_test'])) {
            $data['payment_mypos_virtual_test'] = $this->request->post['payment_mypos_virtual_test'];
        } else {
            $data['payment_mypos_virtual_test'] = $this->config->get('payment_mypos_virtual_test');
        }

        // Site ID
        if (isset($this->request->post['payment_mypos_virtual_developer_sid'])) {
            $data['payment_mypos_virtual_developer_sid'] = $this->request->post['payment_mypos_virtual_developer_sid'];
        } else {
            $data['payment_mypos_virtual_developer_sid'] = $this->config->get('payment_mypos_virtual_developer_sid');
        }

        // Wallet number
        if (isset($this->request->post['payment_mypos_virtual_developer_wallet_number'])) {
            $data['payment_mypos_virtual_developer_wallet_number'] = $this->request->post['payment_mypos_virtual_developer_wallet_number'];
        } else {
            $data['payment_mypos_virtual_developer_wallet_number'] = $this->config->get('payment_mypos_virtual_developer_wallet_number');
        }

        // Private key
        if (isset($this->request->post['payment_mypos_virtual_developer_private_key'])) {
            $data['payment_mypos_virtual_developer_private_key'] = trim($this->request->post['payment_mypos_virtual_developer_private_key']);
        } else {
            $data['payment_mypos_virtual_developer_private_key'] = trim($this->config->get('payment_mypos_virtual_developer_private_key'));
        }

        // Public certificate
        if (isset($this->request->post['payment_mypos_virtual_developer_public_certificate'])) {
            $data['payment_mypos_virtual_developer_public_certificate'] = trim($this->request->post['payment_mypos_virtual_developer_public_certificate']);
        } else {
            $data['payment_mypos_virtual_developer_public_certificate'] = trim($this->config->get('payment_mypos_virtual_developer_public_certificate'));
        }

        // Developer url
        if (isset($this->request->post['payment_mypos_virtual_developer_url'])) {
            $data['payment_mypos_virtual_developer_url'] = $this->request->post['payment_mypos_virtual_developer_url'];
        } else {
            $data['payment_mypos_virtual_developer_url'] = $this->config->get('payment_mypos_virtual_developer_url');
        }

        // Developer keyindex
        if (isset($this->request->post['payment_mypos_virtual_developer_keyindex'])) {
            $data['payment_mypos_virtual_developer_keyindex'] = $this->request->post['payment_mypos_virtual_developer_keyindex'];
        } else {
            $data['payment_mypos_virtual_developer_keyindex'] = $this->config->get('payment_mypos_virtual_developer_keyindex');
        }

        // Developer Configuration pack
        if (isset($this->request->post['payment_mypos_virtual_test_configuration_package'])) {
            $data['payment_mypos_virtual_test_configuration_package'] = $this->request->post['payment_mypos_virtual_test_configuration_package'];
        } else {
            $data['payment_mypos_virtual_test_configuration_package'] = $this->config->get('payment_mypos_virtual_test_configuration_package');
        }

        // Developer payment parameters required
        if (isset($this->request->post['payment_mypos_virtual_developer_ppr'])) {
            $data['payment_mypos_virtual_developer_ppr'] = $this->request->post['payment_mypos_virtual_developer_ppr'];
        } else {
            $data['payment_mypos_virtual_developer_ppr'] = $this->config->get('payment_mypos_virtual_developer_ppr');
        }

        // Production payment method
        if (isset($this->request->post['payment_mypos_virtual_developer_payment_method'])) {
            $data['payment_mypos_virtual_developer_payment_method'] = $this->request->post['payment_mypos_virtual_developer_payment_method'];
        } else {
            $data['payment_mypos_virtual_developer_payment_method'] = $this->config->get('payment_mypos_virtual_developer_payment_method');
        }

        // Site ID
        if (isset($this->request->post['payment_mypos_virtual_production_sid'])) {
            $data['payment_mypos_virtual_production_sid'] = $this->request->post['payment_mypos_virtual_production_sid'];
        } else {
            $data['payment_mypos_virtual_production_sid'] = $this->config->get('payment_mypos_virtual_production_sid');
        }

        // Wallet number
        if (isset($this->request->post['payment_mypos_virtual_production_wallet_number'])) {
            $data['payment_mypos_virtual_production_wallet_number'] = $this->request->post['payment_mypos_virtual_production_wallet_number'];
        } else {
            $data['payment_mypos_virtual_production_wallet_number'] = $this->config->get('payment_mypos_virtual_production_wallet_number');
        }

        // Private key
        if (isset($this->request->post['payment_mypos_virtual_production_private_key'])) {
            $data['payment_mypos_virtual_production_private_key'] = trim($this->request->post['payment_mypos_virtual_production_private_key']);
        } else {
            $data['payment_mypos_virtual_production_private_key'] = trim($this->config->get('payment_mypos_virtual_production_private_key'));
        }

        // Public certificate
        if (isset($this->request->post['payment_mypos_virtual_production_public_certificate'])) {
            $data['payment_mypos_virtual_production_public_certificate'] = trim($this->request->post['payment_mypos_virtual_production_public_certificate']);
        } else {
            $data['payment_mypos_virtual_production_public_certificate'] = trim($this->config->get('payment_mypos_virtual_production_public_certificate'));
        }

        // Production url
        if (isset($this->request->post['payment_mypos_virtual_production_url'])) {
            $data['payment_mypos_virtual_production_url'] = $this->request->post['payment_mypos_virtual_production_url'];
        } else {
            $data['payment_mypos_virtual_production_url'] = $this->config->get('payment_mypos_virtual_production_url');
        }

        // Production keyindex
        if (isset($this->request->post['payment_mypos_virtual_production_keyindex'])) {
            $data['payment_mypos_virtual_production_keyindex'] = $this->request->post['payment_mypos_virtual_production_keyindex'];
        } else {
            $data['payment_mypos_virtual_production_keyindex'] = $this->config->get('payment_mypos_virtual_production_keyindex');
        }

        // Production payment parameters required
        if (isset($this->request->post['payment_mypos_virtual_production_ppr'])) {
            $data['payment_mypos_virtual_production_ppr'] = $this->request->post['payment_mypos_virtual_production_ppr'];
        } else {
            $data['payment_mypos_virtual_production_ppr'] = $this->config->get('payment_mypos_virtual_production_ppr');
        }

        // Production payment method
        if (isset($this->request->post['payment_mypos_virtual_production_payment_method'])) {
            $data['payment_mypos_virtual_production_payment_method'] = $this->request->post['payment_mypos_virtual_production_payment_method'];
        } else {
            $data['payment_mypos_virtual_production_payment_method'] = $this->config->get('payment_mypos_virtual_production_payment_method');
        }

        // Production Configuration pack
        if (isset($this->request->post['payment_mypos_virtual_production_configuration_package'])) {
            $data['payment_mypos_virtual_production_configuration_package'] = $this->request->post['payment_mypos_virtual_production_configuration_package'];
        } else {
            $data['payment_mypos_virtual_production_configuration_package'] = $this->config->get('payment_mypos_virtual_production_configuration_package');
        }

        // Default values
        if ($data['payment_mypos_virtual_developer_url'] == null)
        {
            $data['payment_mypos_virtual_developer_url'] = 'https://www.mypos.com/vmp/checkout-test';
        }

        if ($data['payment_mypos_virtual_developer_keyindex'] == null)
        {
            $data['payment_mypos_virtual_developer_keyindex'] = '1';
        }

        if ($data['payment_mypos_virtual_developer_ppr'] == null)
        {
            $data['payment_mypos_virtual_developer_ppr'] = '3';
        }

        if ($data['payment_mypos_virtual_developer_payment_method'] == null)
        {
            $data['payment_mypos_virtual_developer_payment_method'] = '1';
        }

        if ($data['payment_mypos_virtual_production_url'] == null)
        {
            $data['payment_mypos_virtual_production_url'] = 'https://www.mypos.com/vmp/checkout';
        }

        if ($data['payment_mypos_virtual_production_keyindex'] == null)
        {
            $data['payment_mypos_virtual_production_keyindex'] = '1';
        }

        if ($data['payment_mypos_virtual_production_ppr'] == null)
        {
            $data['payment_mypos_virtual_production_ppr'] = '3';
        }

        if ($data['payment_mypos_virtual_production_payment_method'] == null)
        {
            $data['payment_mypos_virtual_production_payment_method'] = '1';
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


        // Developer keyindex
        if (isset($this->request->post['payment_mypos_virtual_order_statuses'])) {
            $data['payment_mypos_virtual_order_statuses'] = $this->request->post['payment_mypos_virtual_order_statuses'];
        } else {
            $data['payment_mypos_virtual_order_statuses'] = $this->config->get('payment_mypos_virtual_order_statuses');
        }

        $data['order_status_settings_list'] = $this->getStatusesConfig($data['payment_mypos_virtual_order_statuses']);

        $this->response->setOutput($this->load->view('extension/payment/mypos_virtual', $data));
    }

    public function order() {
        $this->load->language('extension/payment/mypos_virtual');

        $data['order_id'] = $this->request->get['order_id'];

        $url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);

        $data['action'] = $url->link('extension/payment/mypos_virtual/check_payment_status', '', true);
        $data['return_url'] = $this->url->link('sale/order/info', ['user_token' => $this->session->data['user_token'], 'order_id' => $this->request->get['order_id']]) . '#input-order-status';
        return $this->load->view('extension/payment/mypos_virtual_order', $data);
    }

    protected function getStatusesConfig($orderStatusValues)
    {
        $orderStatusSettingsList = array(
            'completed' => array(
                'name' => 'text_completed_status',
                'default_id' => 5
            ),
            'canceled' => array(
                'name' => 'text_canceled_status',
                'default_id' => 7
            ),
            'pending' => array(
                'name' => 'text_pending_status',
                'default_id' => 1
            ),
            'refunded' => array(
                'name' => 'text_refunded_status',
                'default_id' => 11
            ),
            'reversed' => array(
                'name' => 'text_reversed_status',
                'default_id' => 12
            )
        );

        foreach ($orderStatusSettingsList as $code => $orderStatusSettings) {
            if (!empty($orderStatusValues[$code]['id'])) {
                $orderStatusSettingsList[$code]['id'] = $orderStatusValues[$code]['id'];
            } else {
                $orderStatusSettingsList[$code]['id'] = $orderStatusSettings['default_id'];
            }
        }

        return $orderStatusSettingsList;
    }
}