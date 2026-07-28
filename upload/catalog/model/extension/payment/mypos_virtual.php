<?php
class ModelExtensionPaymentMyposVirtual extends Model {
	public function getMethod($address, $total) {

		$this->load->language('extension/payment/mypos_virtual');

		if ($this->config->get('payment_mypos_virtual_test') === '1') {
            $paymentMethod = $this->config->get('payment_mypos_virtual_developer_payment_method');
        } else {
            $paymentMethod = $this->config->get('payment_mypos_virtual_production_payment_method');
        }

        $status = $paymentMethod == 3 ? $this->isValidIdealCurrency() : $this->isValidCardCurrency();

        $method_data = array();

        if ($status) {
            if ( $paymentMethod == 3 && $this->isValidIdealCurrency()) {
                $image = 'card_schemes_ideal_no_bg.png';
            } else if ($paymentMethod == 2) {
                $image = 'mypos_ideal_no_bg.png';
            } else {
                $image = 'card_schemes_no_bg.png';
            }

            $method_data = array(
                'code'       => 'mypos_virtual',
                'title'      => $this->language->get('text_title') . '<br /><img alt="mypos_checkout_logo" src="catalog/view/theme/default/image/mypos/' . $image . '" />',
                'terms'      => '',
                'sort_order' => $this->config->get('payment_mypos_virtual_sort_order')
            );
        }

        return $method_data;
	}

	public function isValidCardCurrency()
    {
        $currencies = array(
            'BGN', 'USD', 'EUR', 'GBP', 'CHF', 'JPY', 'RON', 'HRK', 'NOK', 'SEK', 'CZK', 'HUF', 'PLN', 'DKK', 'ISK',
        );

        foreach ($currencies as $currency) {
            if ($this->currency->has($currency)) {
                return true;
                break;
            }
        }

        return false;
    }

	public function isValidIdealCurrency()
    {
        $currencies = array(
            'EUR',
        );

        foreach ($currencies as $currency) {
            if ($this->currency->has($currency)) {
                return true;
                break;
            }
        }

        return false;
    }
}