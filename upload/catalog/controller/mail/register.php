<?php
class ControllerMailRegister extends Controller {
	public function index(&$route, &$args, &$output) {
		$this->load->language('mail/register');

		$data['text_welcome']  = sprintf($this->language->get('text_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$data['text_login']    = $this->language->get('text_login');
		$data['text_approval'] = $this->language->get('text_approval');
		$data['text_service']  = $this->language->get('text_service');
		$data['text_thanks']   = $this->language->get('text_thanks');

		$this->load->model('account/customer_group');
		$this->load->model('account/custom_field');

		if (isset($args[0]['customer_group_id'])) {
			$customer_group_id = (int)$args[0]['customer_group_id'];
		} else {
			$customer_group_id = (int)$this->config->get('config_customer_group_id');
		}

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		if ($customer_group_info) {
			$data['approval'] = $customer_group_info['approval'];
		} else {
			$data['approval'] = '';
		}

		// ---------------- CUSTOM FIELDOVI – KORISNIČKI MAIL ----------------
		$data['custom_fields'] = array();

		// Svi custom fieldovi (za taj customer group)
		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		// $args[0]['custom_field'] može biti ili [id] ili ['account'][id]
		$account_custom_field = array();

		if (isset($args[0]['custom_field'])) {
			if (isset($args[0]['custom_field']['account'])) {
				$account_custom_field = $args[0]['custom_field']['account'];
			} else {
				$account_custom_field = $args[0]['custom_field'];
			}
		}

		foreach ($custom_fields as $custom_field) {
			// U mailu za registraciju obično želiš samo 'account' location
			if ($custom_field['location'] != 'account') {
				continue;
			}

			$custom_field_id = $custom_field['custom_field_id'];

			if (!isset($account_custom_field[$custom_field_id])) {
				continue;
			}

			$value = $account_custom_field[$custom_field_id];

			// Mapiranje ID -> naziv za select/radio/checkbox
			if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
				// single value id
				foreach ($custom_field['custom_field_value'] as $cfv) {
					if ((int)$cfv['custom_field_value_id'] == (int)$value) {
						$value = $cfv['name'];
						break;
					}
				}
			} elseif ($custom_field['type'] == 'checkbox') {
				$names = array();

				if (is_array($value)) {
					foreach ($value as $value_id) {
						foreach ($custom_field['custom_field_value'] as $cfv) {
							if ((int)$cfv['custom_field_value_id'] == (int)$value_id) {
								$names[] = $cfv['name'];
								break;
							}
						}
					}
				}

				if ($names) {
					$value = implode(', ', $names);
				} else {
					$value = '';
				}
			}

			$data['custom_fields'][] = array(
				'name'  => $custom_field['name'],
				'value' => $value
			);
		}
		// -------------------------------------------------------------------

		$data['login'] = $this->url->link('account/login', '', true);
		$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter     = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port     = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($args[0]['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
		$mail->setText($this->load->view('mail/register', $data)); // ili setHtml ako koristiš HTML
		$mail->send();
	}

	public function alert(&$route, &$args, &$output) {
		// Send to main admin email if new account email is enabled
		if (in_array('account', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('mail/register');

			$data['text_signup']         = $this->language->get('text_signup');
			$data['text_firstname']      = $this->language->get('text_firstname');
			$data['text_lastname']       = $this->language->get('text_lastname');
			$data['text_customer_group'] = $this->language->get('text_customer_group');
			$data['text_email']          = $this->language->get('text_email');
			$data['text_telephone']      = $this->language->get('text_telephone');

			$data['firstname'] = $args[0]['firstname'];
			$data['lastname']  = $args[0]['lastname'];

			$this->load->model('account/customer_group');
			$this->load->model('account/custom_field');

			if (isset($args[0]['customer_group_id'])) {
				$customer_group_id = (int)$args[0]['customer_group_id'];
			} else {
				$customer_group_id = (int)$this->config->get('config_customer_group_id');
			}

			$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}

			$data['email']     = $args[0]['email'];
			$data['telephone'] = $args[0]['telephone'];

			// ---------------- CUSTOM FIELDOVI – ADMIN MAIL ----------------
			$data['custom_fields'] = array();

			$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

			$account_custom_field = array();

			if (isset($args[0]['custom_field'])) {
				if (isset($args[0]['custom_field']['account'])) {
					$account_custom_field = $args[0]['custom_field']['account'];
				} else {
					$account_custom_field = $args[0]['custom_field'];
				}
			}

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] != 'account') {
					continue;
				}

				$custom_field_id = $custom_field['custom_field_id'];

				if (!isset($account_custom_field[$custom_field_id])) {
					continue;
				}

				$value = $account_custom_field[$custom_field_id];

				if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
					foreach ($custom_field['custom_field_value'] as $cfv) {
						if ((int)$cfv['custom_field_value_id'] == (int)$value) {
							$value = $cfv['name'];
							break;
						}
					}
				} elseif ($custom_field['type'] == 'checkbox') {
					$names = array();

					if (is_array($value)) {
						foreach ($value as $value_id) {
							foreach ($custom_field['custom_field_value'] as $cfv) {
								if ((int)$cfv['custom_field_value_id'] == (int)$value_id) {
									$names[] = $cfv['name'];
									break;
								}
							}
						}
					}

					if ($names) {
						$value = implode(', ', $names);
					} else {
						$value = '';
					}
				}

				$data['custom_fields'][] = array(
					'name'  => $custom_field['name'],
					'value' => $value
				);
			}
			// ----------------------------------------------------------------

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter     = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port     = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('mail/register_alert', $data));
			$mail->send();

			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}
}
