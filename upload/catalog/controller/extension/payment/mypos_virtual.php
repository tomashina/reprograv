<?php

class ControllerExtensionPaymentMyposVirtual extends Controller
{
    /**
     * @var Log $log
     */
    private $log;
    private $isTest;
    private $testPrefix;
    private $keyindex;
    private $sid;
    private $wallet_number;
    private $private_key;
    private $public_key;
    private $version;
    private $action;
    private $logging;
    private $paymentParametersRequired;
    private $paymentMethod;
    private $orderStatuses;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->log = new Log('mypos_virtual.log');

        $this->isTest = $this->config->get('payment_mypos_virtual_test') === '1' ? true : false;
        $this->logging = $this->config->get('payment_mypos_virtual_logging') === '1' ? true : false;
        $this->version = '1.4';

        $this->orderStatuses = $this->config->get('payment_mypos_virtual_order_statuses');

        if (!$this->isTest)
        {
            $packageData = json_decode(base64_decode($this->config->get('payment_mypos_virtual_production_configuration_package')), true);
            $this->sid           = $packageData['sid'] ? $packageData['sid'] : $this->config->get('payment_mypos_virtual_production_sid');
            $this->wallet_number = $packageData['cn'] ? $packageData['cn'] : $this->config->get('payment_mypos_virtual_production_wallet_number');
            $this->private_key   = $packageData['pk'] ? $packageData['pk'] : $this->config->get('payment_mypos_virtual_production_private_key');
            $this->public_key    = $packageData['pc'] ? $packageData['pc'] : $this->config->get('payment_mypos_virtual_production_public_certificate');
            $this->action        = $this->config->get('payment_mypos_virtual_production_url');

            // .COM migration: force update tld to .com
            if (empty($this->action) || false !== stripos((parse_url($this->action)['host']), 'mypos.eu')) {
                $this->action = 'https://mypos.com/vmp/checkout';
                $this->db->query("UPDATE `" . DB_PREFIX . "setting` 
                SET `value` = '" . $this->action . "'
                WHERE (store_id = '0' OR store_id = '" . (int)$this->config->get('config_store_id') . "') 
                AND `key` = 'payment_mypos_virtual_production_url' AND `code` = 'payment_mypos_virtual'");
            }

            $this->keyindex      = $packageData['idx'] ? $packageData['idx'] : $this->config->get('payment_mypos_virtual_production_keyindex');
            $this->paymentParametersRequired = $this->config->get('payment_mypos_virtual_production_ppr');
            $this->testPrefix = null;
            $this->paymentMethod = $this->config->get('payment_mypos_virtual_production_payment_method');
        }
        else
        {
            $packageData = json_decode(base64_decode($this->config->get('payment_mypos_virtual_test_configuration_package')), true);
            $this->sid           = $packageData['sid'] ? $packageData['sid'] :  $this->config->get('payment_mypos_virtual_developer_sid');
            $this->wallet_number = $packageData['cn'] ? $packageData['cn'] : $this->config->get('payment_mypos_virtual_developer_wallet_number');
            $this->private_key   = $packageData['pk'] ? $packageData['pk'] : $this->config->get('payment_mypos_virtual_developer_private_key');
            $this->public_key    = $packageData['pc'] ? $packageData['pc'] : $this->config->get('payment_mypos_virtual_developer_public_certificate');
            $this->action        = $this->config->get('payment_mypos_virtual_developer_url');

            // .COM migration: force update tld to .com
            if (empty($this->action) || false !== stripos((parse_url($this->action)['host']), 'mypos.eu')) {
                $this->action = 'https://mypos.com/vmp/checkout-test';
                $this->db->query("UPDATE `" . DB_PREFIX . "setting` 
                SET `value` = '" . $this->action . "'
                WHERE (store_id = '0' OR store_id = '" . (int)$this->config->get('config_store_id') . "') 
                AND `key` = 'payment_mypos_virtual_developer_url' AND `code` = 'payment_mypos_virtual'");
            }

            $this->keyindex      = $packageData['idx'] ? $packageData['idx'] : $this->config->get('payment_mypos_virtual_developer_keyindex');
            $this->paymentParametersRequired = $this->config->get('payment_mypos_virtual_developer_ppr');
            $this->testPrefix = $this->config->get('payment_mypos_virtual_test_prefix');
            $this->paymentMethod = $this->config->get('payment_mypos_virtual_developer_payment_method');
        }
    }

    public function index() {
        $this->language->load('extension/payment/mypos_virtual');
        $data['action'] = $this->url->link('extension/payment/mypos_virtual/process_order', '', true);
        $data['button_confirm'] = $this->language->get('button_confirm');

        return $this->load->view('extension/payment/mypos_virtual', $data);
    }

    public function process_order() {
        $this->load->model('checkout/order');
        $data['continue'] = $this->url->link('checkout/success');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['post'] = $this->create_post($order_info);

        if (in_array((int) $this->paymentMethod, [2, 3])) {
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->getStatusId('pending', 1), 'Awaiting payment', true);
            $this->cart->clear();
        }

        $return = '<form name="mypos_virtual" action="' . $this->action . '" method="post">';
        foreach ($data['post'] as $k => $value) {
            $return .= '<input type="hidden" name="' . $k . '" value="' . $value . '">';
        }
        $return .= '
            </form>
            <script>
                document.mypos_virtual.submit();
            </script>
        ';

        echo $return;
    }

    public function check_payment_status() {

        if (array_key_exists('mypos-check-payment-status', $this->request->post) && $this->request->post['mypos-check-payment-status']
            && array_key_exists('order_id', $this->request->post)) {
            $this->load->model('checkout/order');
            $order_id = $this->request->post['order_id'];
            $data = $this->create_check_status_data($this->request->post['order_id']);

            //open connection
            $ch = curl_init($this->action);

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $this->action);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
            curl_setopt($ch, CURLOPT_HEADER ,0); // DO NOT RETURN HTTP HEADERS
            curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // Timeout on connect (2 minutes)

            //execute post
            $result = curl_exec($ch);
            curl_close($ch);
            ;

            // Parse xml
            $post = json_decode($result, true);

            $order = $this->model_checkout_order->getOrder($order_id);

            if ($this->is_valid_signature($post) && array_key_exists('PaymentStatus', $post)
                && array_key_exists('Amount', $post) && $post['Amount'] == $this->currency->format($order['total'], $order['currency_code'], false, false)
                && array_key_exists('Currency', $post) && $post['Currency'] == $order['currency_code'])
            {
                switch ($post['PaymentStatus']) {

                    case 1:
                        if ($order['order_status_id'] == $this->getStatusId('pending', 1)) {
                            $this->model_checkout_order->addOrderHistory(
                                $order_id,
                                $this->getStatusId('completed', 2),
                                'myPOS Checkout has authorized transaction. Transaction ID: ' . $post['IPC_Trnref'],
                                true
                            );
                        }
                        break;
                    case 3:
                        if ($order['order_status_id'] == $this->getStatusId('pending', 1)) {
                            $this->model_checkout_order->addOrderHistory(
                                $order_id,
                                $this->getStatusId('canceled', 7),
                                'Order canceled.'
                            );
                        }
                        break;
                }
            }

            if (array_key_exists('return_url', $this->request->post)) {
                $this->response->redirect($this->request->post['return_url']);
            }
        }
    }

    public function callback() {
        if ($this->is_valid_signature($this->request->post))
        {
            if (isset($this->request->post['OrderID'])) {
                if ($this->isTest) {
                    $this->request->post['OrderID'] = str_replace($this->testPrefix, '', $this->request->post['OrderID']);
                }
                $order_id = $this->request->post['OrderID'];
            } else {
                die('Illegal Access');
            }

            $this->load->model('checkout/order');

            if ($this->request->post['IPCmethod'] == 'IPCPurchaseNotify')
            {
                $order = $this->model_checkout_order->getOrder($order_id);

                $this->log('Received ' . $this->request->post['IPCmethod'] . ' request for order: ' . $order_id . '.');

                if ($order['order_status_id'] == $this->getStatusId('reversed', 12))
                {
                    $this->log('Warning: '. $this->request->post['IPCmethod']. 'received but order was reversed (ignoring complete status). request for order: ' . $order_id . '.');
                    echo 'KO - Payment already reversed!';
                    exit;
                }

                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->getStatusId('completed', 2),
                    'myPOS Checkout has authorized transaction. Transaction ID: ' . $this->request->post['IPC_Trnref'],
                    true
                );

                echo 'OK';
                exit;
            }
            else if ($this->request->post['IPCmethod'] == 'IPCPurchaseRollback')
            {
                $this->log('Received ' . $this->request->post['IPCmethod'] . ' request for order: ' . $order_id . '.');
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    $this->getStatusId('reversed', 12),
                    'myPOS Checkout has denied transaction.'
                );

                echo 'OK';
                exit;
            }
            else if ($this->request->post['IPCmethod'] == 'IPCPurchaseCancel')
            {
                $order = $this->model_checkout_order->getOrder($order_id);

                if ($order['order_status_id'] != $this->getStatusId('reversed', 12)) {
                    $this->log('Received ' . $this->request->post['IPCmethod'] . ' request for order: ' . $order_id . '.');
                    $this->model_checkout_order->addOrderHistory(
                        $order_id,
                        $this->getStatusId('canceled', 7),
                        'User has cancelled the order.'
                    );
                }


                $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
                exit;
            }
            else if ($this->request->post['IPCmethod'] == 'IPCPurchaseOK')
            {
                $this->log('Received ' . $this->request->post['IPCmethod'] . ' request for order: ' . $order_id . '.');
                $this->response->redirect($this->url->link('checkout/success'));
                exit;
            }
            else
            {
                $this->log('Invalid method received on notify url. Method name:' . $this->request->post['IPCmethod'] . '.');
                echo 'INVALID METHOD';
                exit;
            }
        }
        else
        {
            echo 'INVALID SIGNATURE';
            exit;
        }
    }

    public function create_post($order_info)
    {
        $post = array();

        $post['IPCmethod'] = 'IPCPurchase';
        $post['IPCVersion'] = $this->version;
        $post['IPCLanguage'] = $this->language->get('code');
        $post['WalletNumber'] = $this->wallet_number;
        $post['SID'] = $this->sid;
        $post['keyindex'] = $this->keyindex;
        $post['Source'] = 'sc_opencart 1.8.1 ' . PHP_VERSION_ID . ' ' . VERSION;
        $post['CardTokenRequest'] = 0;
        $post['PaymentParametersRequired'] = $this->paymentParametersRequired;
        $post['PaymentMethod'] = $this->paymentMethod;

        $post['Amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
        $post['Currency'] = $order_info['currency_code'];
        $post['OrderID'] = (!is_null($this->testPrefix) ? $this->testPrefix : '') . $this->session->data['order_id'];
        $post['URL_OK'] = $this->url->link('extension/payment/mypos_virtual/callback', '', true);
        $post['URL_CANCEL'] = $this->url->link('extension/payment/mypos_virtual/callback', '', true);
        $post['URL_Notify'] = $this->url->link('extension/payment/mypos_virtual/callback', '', true);
        $post['CustomerIP'] = $_SERVER['REMOTE_ADDR'];
        $post['CustomerEmail'] = $order_info['email'];
        $post['CustomerFirstNames'] =  $order_info['payment_firstname'];
        $post['CustomerFamilyName'] = $order_info['payment_lastname'];
        $post['CustomerCountry'] = $order_info['payment_iso_code_3'];
        $post['CustomerCity'] = $order_info['payment_city'];
        $post['CustomerZIPCode'] = $order_info['payment_postcode'];
        $post['CustomerAddress'] = $order_info['payment_address_1'];
        $post['CustomerPhone'] = $order_info['telephone'];
        $post['Note'] = 'myPOS Checkout OpenCart Extension';
        $post['CartItems'] = 0;

        $cartTotal = 0;

        $index = 1;

        $data['products'] = array();

        foreach ($this->cart->getProducts() as $product) {
            $post['Article_' . $index] = html_entity_decode(strip_tags($product['name']));
            $post['Quantity_' . $index] = $product['quantity'];
            $post['CartItems'] -= $product['quantity'] - 1;
            $post['Price_' . $index] = $this->currency->format($product['price'], $order_info['currency_code'], false, false);
            $post['Amount_' . $index] = $this->currency->format($product['total'], $order_info['currency_code'], false, false);
            $post['Currency_' . $index] = $order_info['currency_code'];
            $cartTotal += $post['Amount_' . $index];
            $index++;
        }

        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $totals);

        $json['totals'] = array();

        foreach ($totals as $total) {
            if ($total['code'] === 'sub_total' || $total['code'] === 'total') {
                continue;
            }

            $post['CartItems'] += 1;
            $post['Article_' . $index] = html_entity_decode(strip_tags($total['title']));
            $post['Quantity_' . $index] = 1;
            $post['Price_' . $index] = $this->currency->format($total['value'], $order_info['currency_code'], false, false);
            $post['Amount_' . $index] = $this->currency->format($total['value'], $order_info['currency_code'], false, false);
            $post['Currency_' . $index] = $order_info['currency_code'];
            $cartTotal += $post['Amount_' . $index];
            $index++;
        }

        //Cart total round
        $deviationAmount = $this->currency->format($post['Amount'] - $cartTotal, $order_info['currency_code'], false, false);
        if ($post['Amount'] != $cartTotal && abs($deviationAmount) <= 0.03) {
            $this->language->load('extension/payment/mypos_virtual');
            $post['CartItems'] += 1;
            $post['Article_' . $index] = $this->language->get('text_rounding');
            $post['Quantity_' . $index] = 1;
            $post['Price_' . $index] = $deviationAmount;
            $post['Amount_' . $index] = $deviationAmount;
            $post['Currency_' . $index] = $order_info['currency_code'];
            $index++;
        }

        $post['CartItems'] = $index - 1;

        foreach ($post as $k => $v) {
            $post[$k] = htmlspecialchars($v);
        }

        $post['Signature'] = $this->create_signature($post);

        $this->log('Created POST data for order: ' . $post['OrderID'] . '.');

        return $post;
    }

    private function create_check_status_data($order_id)
    {

        $post = array();
        $post['IPCmethod'] = 'IPCGetPaymentStatus';
        $post['IPCVersion'] = $this->version;
        $post['IPCLanguage'] = 'en';
        $post['WalletNumber'] = $this->wallet_number;
        $post['SID'] = $this->sid;
        $post['keyindex'] = $this->keyindex;
        $post['Source'] = 'sc_opencart 1.8.1 ' . PHP_VERSION_ID . ' ' . VERSION;

        $post['OrderID'] = $order_id;

        $post['OutputFormat'] = 'json';

        $post['Signature'] = $this->create_signature($post);

        return $post;
    }

    public function create_signature($post)
    {
        $concData = base64_encode(implode('-', $post));
        $privKeyObj = openssl_get_privatekey($this->private_key);
        openssl_sign($concData, $signature, $privKeyObj, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public function is_valid_signature($post)
    {
        // Save signature
        $signature = $post['Signature'];

        // Remove signature from POST data array
        unset($post['Signature']);

        // Concatenate all values
        $concData = base64_encode(implode('-', $post));

        // Extract public key from certificate
        $pubKeyId = openssl_get_publickey($this->public_key);

        // Verify signature
        $result = openssl_verify($concData, base64_decode($signature), $pubKeyId, OPENSSL_ALGO_SHA256);

        //Free key resource
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            openssl_free_key($pubKeyId);
        }

        if ($result == 1)
        {
            $this->log('Valid signature.');
            return true;
        }
        else
        {
            $this->log('Invalid signature.');
            return false;
        }
    }

    protected function getStatusId($code, $defaultStatus)
    {
        return !empty($this->orderStatuses[$code]['id']) ? $this->orderStatuses[$code]['id'] : $defaultStatus;
    }

    private function log($message) {
        if ($this->logging)
        {
            $this->log->write($message);
        }
    }
}