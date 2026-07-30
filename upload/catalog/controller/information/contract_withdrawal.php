<?php
class ControllerInformationContractWithdrawal extends Controller {
	private $error = array();
	private $draft_session_prefix = 'contract_withdrawal_draft_';

	public function index() {
		$this->load->language('information/contract_withdrawal');
		$this->load->model('information/contract_withdrawal');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('meta_description'));
		$this->document->addLink($this->url->link('information/contract_withdrawal'), 'canonical');
		$this->document->addStyle('catalog/view/theme/basel/stylesheet/contract-withdrawal.css?v=20260730.2');

		$this->ensureCsrfToken();

		$step = 'form';
		$form = $this->getFormValues();
		$draft_token = '';
		$success = array();

		if (isset($this->session->data['contract_withdrawal_success'])) {
			$success = $this->session->data['contract_withdrawal_success'];
			unset($this->session->data['contract_withdrawal_success']);
		}

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$action = isset($this->request->post['withdrawal_action']) ? (string)$this->request->post['withdrawal_action'] : 'review';

			if ($action === 'confirm') {
				$this->confirmWithdrawal();
				return;
			}

			if ($this->validateForm()) {
				$form = $this->normalizeForm($this->request->post);
				$draft_token = $this->newToken();
				$this->session->data[$this->draft_session_prefix . $draft_token] = array(
					'data'       => $form,
					'created_at' => time(),
					'expires_at' => time() + 1800
				);
				$step = 'review';
			}
		}

		$data = $this->languageData();
		$data['step'] = $step;
		$data['form'] = $form;
		$data['errors'] = $this->error;
		$data['draft_token'] = $draft_token;
		$data['csrf_token'] = $this->session->data['contract_withdrawal_csrf'];
		$data['form_started_at'] = time();
		$data['max_date'] = date('Y-m-d');
		$data['success'] = $success;
		$data['declaration'] = $step === 'review' ? sprintf($this->language->get('text_declaration'), $form['order_number']) : '';
		$data['return_address'] = trim((string)$this->config->get('contract_withdrawal_return_address'));
		$data['instructions'] = trim((string)$this->config->get('contract_withdrawal_instructions'));

		if ($data['return_address'] === '') {
			$data['return_address'] = trim((string)$this->config->get('config_address'));
		}

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('information/contract_withdrawal')
			)
		);

		$data['action'] = $this->url->link('information/contract_withdrawal', '', true);
		$data['edit_url'] = $this->url->link('information/contract_withdrawal', $draft_token ? 'edit=' . urlencode($draft_token) : '');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/contract_withdrawal', $data));
	}

	private function confirmWithdrawal() {
		if (!$this->validateCsrf()) {
			$this->session->data['contract_withdrawal_success'] = array(
				'type' => 'error',
				'message' => $this->language->get('error_security')
			);
			$this->response->redirect($this->url->link('information/contract_withdrawal'));
			return;
		}

		$draft_token = isset($this->request->post['draft_token']) ? (string)$this->request->post['draft_token'] : '';
		$draft_key = $this->draft_session_prefix . $draft_token;
		$draft = isset($this->session->data[$draft_key]) ? $this->session->data[$draft_key] : array();

		if (!$draft || empty($draft['data']) || empty($draft['expires_at']) || (int)$draft['expires_at'] < time()) {
			unset($this->session->data[$draft_key]);
			$this->session->data['contract_withdrawal_success'] = array(
				'type' => 'error',
				'message' => $this->language->get('error_draft_expired')
			);
			$this->response->redirect($this->url->link('information/contract_withdrawal'));
			return;
		}

		$form = $draft['data'];
		$submission_key = hash('sha256', $draft_token);
		$withdrawal = $this->model_information_contract_withdrawal->getBySubmissionKey($submission_key);
		$created = false;

		if (!$withdrawal) {
			$submitted_at = date('Y-m-d H:i:s');
			$declaration = sprintf($this->language->get('text_declaration'), $form['order_number']);
			$snapshot = array(
				'version' => '2026-07-30',
				'submitted_at' => date(DATE_ATOM),
				'confirmation_channel' => 'email',
				'data' => $form,
				'declaration' => $declaration
			);
			$snapshot_json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

			$withdrawal_id = $this->model_information_contract_withdrawal->addWithdrawal(array(
				'reference' => $this->newReference(),
				'submission_key' => $submission_key,
				'customer_id' => $this->customer->isLogged() ? $this->customer->getId() : 0,
				'order_id' => $this->model_information_contract_withdrawal->resolveOrderId(
					$form['order_number'],
					$form['email'],
					$this->customer->isLogged() ? $this->customer->getId() : 0
				),
				'order_number' => $form['order_number'],
				'full_name' => $form['full_name'],
				'email' => $form['email'],
				'phone' => $form['phone'],
				'address_line' => $form['address_line'],
				'postal_code' => $form['postal_code'],
				'city' => $form['city'],
				'country_code' => $form['country_code'],
				'contract_date' => $form['contract_date'],
				'received_date' => $form['received_date'],
				'items' => $form['items'],
				'note' => $form['note'],
				'declaration' => $declaration,
				'request_snapshot' => $snapshot_json,
				'snapshot_hash' => hash('sha256', $snapshot_json),
				'language_id' => (int)$this->config->get('config_language_id'),
				'submitted_at' => $submitted_at,
				'ip_address' => isset($this->request->server['REMOTE_ADDR']) ? $this->request->server['REMOTE_ADDR'] : '',
				'user_agent' => isset($this->request->server['HTTP_USER_AGENT']) ? utf8_substr($this->request->server['HTTP_USER_AGENT'], 0, 512) : ''
			));

			$withdrawal = $this->model_information_contract_withdrawal->getWithdrawal($withdrawal_id);
			$created = true;
		}

		unset($this->session->data[$draft_key]);

		$mail_warning = '';
		if ($created) {
			$mail_warning = $this->sendNotifications($withdrawal);
		}

		$this->session->data['contract_withdrawal_success'] = array(
			'type' => 'success',
			'message' => sprintf($this->language->get('text_success'), $withdrawal['reference']),
			'reference' => $withdrawal['reference'],
			'warning' => $mail_warning
		);

		$this->response->redirect($this->url->link('information/contract_withdrawal'));
	}

	private function sendNotifications($withdrawal) {
		$errors = array();
		$mail_data = $withdrawal;
		$mail_data['store_name'] = $this->config->get('config_name');
		$mail_data['return_address'] = trim((string)$this->config->get('contract_withdrawal_return_address'));
		$mail_data['instructions'] = trim((string)$this->config->get('contract_withdrawal_instructions'));
		$mail_data['labels'] = $this->languageData();

		if ($mail_data['return_address'] === '') {
			$mail_data['return_address'] = trim((string)$this->config->get('config_address'));
		}

		try {
			$mail = $this->newMail();
			$mail->setTo($withdrawal['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(sprintf($this->language->get('mail_customer_subject'), $withdrawal['reference']));
			$mail->setHtml($this->load->view('mail/contract_withdrawal_customer', $mail_data));
			$mail->setText($this->plainMail($withdrawal, false));
			$mail->send();
			$this->model_information_contract_withdrawal->markConsumerNotified($withdrawal['contract_withdrawal_id']);
		} catch (Throwable $exception) {
			$errors[] = 'customer: ' . $exception->getMessage();
		}

		$admin_email = trim((string)$this->config->get('contract_withdrawal_admin_email'));
		if ($admin_email === '') {
			$admin_email = trim((string)$this->config->get('config_email'));
		}

		try {
			$mail = $this->newMail();
			$mail->setTo($admin_email);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($withdrawal['email']);
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(sprintf($this->language->get('mail_admin_subject'), $withdrawal['order_number'], $withdrawal['reference']));
			$mail->setHtml($this->load->view('mail/contract_withdrawal_admin', $mail_data));
			$mail->setText($this->plainMail($withdrawal, true));
			$mail->send();
			$this->model_information_contract_withdrawal->markAdminNotified($withdrawal['contract_withdrawal_id']);
		} catch (Throwable $exception) {
			$errors[] = 'admin: ' . $exception->getMessage();
		}

		if ($errors) {
			$this->model_information_contract_withdrawal->setNotificationError(
				$withdrawal['contract_withdrawal_id'],
				implode("\n", $errors)
			);

			return $this->language->get('text_confirmation_email_failed');
		}

		return '';
	}

	private function newMail() {
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		return $mail;
	}

	private function plainMail($withdrawal, $for_admin) {
		$lines = array(
			$for_admin ? $this->language->get('mail_admin_heading') : $this->language->get('mail_customer_heading'),
			'',
			$this->language->get('mail_reference') . ': ' . $withdrawal['reference'],
			$this->language->get('mail_submitted_at') . ': ' . $withdrawal['submitted_at'],
			$this->language->get('mail_full_name') . ': ' . $withdrawal['full_name'],
			$this->language->get('mail_email') . ': ' . $withdrawal['email'],
			$this->language->get('mail_phone') . ': ' . ($withdrawal['phone'] ?: '—'),
			$this->language->get('mail_address') . ': ' . $withdrawal['address_line'] . ', ' . $withdrawal['postal_code'] . ' ' . $withdrawal['city'] . ', ' . $withdrawal['country_code'],
			$this->language->get('mail_order_number') . ': ' . $withdrawal['order_number'],
			$this->language->get('mail_contract_date') . ': ' . ($withdrawal['contract_date'] ?: '—'),
			$this->language->get('mail_received_date') . ': ' . ($withdrawal['received_date'] ?: '—'),
			'',
			$this->language->get('mail_declaration') . ':',
			$withdrawal['declaration'],
			'',
			$this->language->get('mail_items') . ':',
			$withdrawal['items'],
			'',
			$this->language->get('mail_note') . ':',
			$withdrawal['note'] ?: '—'
		);

		return implode("\n", $lines);
	}

	private function validateForm() {
		if (!$this->validateCsrf()) {
			$this->error['warning'] = $this->language->get('error_security');
		}

		if (!empty($this->request->post['website'])) {
			$this->error['warning'] = $this->language->get('error_security');
		}

		$started_at = isset($this->request->post['form_started_at']) ? (int)$this->request->post['form_started_at'] : 0;
		if ($started_at <= 0 || $started_at > time() || (time() - $started_at) > 86400) {
			$this->error['warning'] = $this->language->get('error_security');
		}

		$rules = array(
			'full_name' => array(2, 191),
			'address_line' => array(3, 255),
			'postal_code' => array(2, 32),
			'city' => array(2, 120),
			'order_number' => array(1, 80),
			'items' => array(2, 5000)
		);

		foreach ($rules as $field => $lengths) {
			$value = isset($this->request->post[$field]) ? trim((string)$this->request->post[$field]) : '';
			$length = utf8_strlen($value);
			if ($length < $lengths[0] || $length > $lengths[1]) {
				$this->error[$field] = sprintf($this->language->get('error_required'), $this->language->get('entry_' . $field));
			}
		}

		$email = isset($this->request->post['email']) ? trim((string)$this->request->post['email']) : '';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || utf8_strlen($email) > 191) {
			$this->error['email'] = $this->language->get('error_email');
		}

		$phone = isset($this->request->post['phone']) ? trim((string)$this->request->post['phone']) : '';
		if (utf8_strlen($phone) > 80) {
			$this->error['phone'] = $this->language->get('error_phone');
		}

		$country_code = isset($this->request->post['country_code']) ? trim((string)$this->request->post['country_code']) : '';
		if (!preg_match('/^[A-Za-z]{2}$/', $country_code)) {
			$this->error['country_code'] = $this->language->get('error_country_code');
		}

		$note = isset($this->request->post['note']) ? trim((string)$this->request->post['note']) : '';
		if (utf8_strlen($note) > 5000) {
			$this->error['note'] = $this->language->get('error_note');
		}

		$contract_date = isset($this->request->post['contract_date']) ? trim((string)$this->request->post['contract_date']) : '';
		$received_date = isset($this->request->post['received_date']) ? trim((string)$this->request->post['received_date']) : '';

		if ($contract_date !== '' && !$this->validPastDate($contract_date)) {
			$this->error['contract_date'] = $this->language->get('error_date');
		}

		if ($received_date !== '' && !$this->validPastDate($received_date)) {
			$this->error['received_date'] = $this->language->get('error_date');
		}

		if ($contract_date !== '' && $received_date !== '' && $received_date < $contract_date) {
			$this->error['received_date'] = $this->language->get('error_received_date');
		}

		return !$this->error;
	}

	private function validPastDate($value) {
		$date = DateTime::createFromFormat('!Y-m-d', $value);

		return $date && $date->format('Y-m-d') === $value && $value <= date('Y-m-d');
	}

	private function validateCsrf() {
		$posted = isset($this->request->post['csrf_token']) ? (string)$this->request->post['csrf_token'] : '';
		$stored = isset($this->session->data['contract_withdrawal_csrf']) ? (string)$this->session->data['contract_withdrawal_csrf'] : '';

		return $posted !== '' && $stored !== '' && hash_equals($stored, $posted);
	}

	private function ensureCsrfToken() {
		if (empty($this->session->data['contract_withdrawal_csrf'])) {
			$this->session->data['contract_withdrawal_csrf'] = $this->newToken();
		}
	}

	private function newToken() {
		try {
			return bin2hex(random_bytes(32));
		} catch (Exception $exception) {
			return hash('sha256', uniqid((string)mt_rand(), true));
		}
	}

	private function newReference() {
		do {
			try {
				$suffix = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
			} catch (Exception $exception) {
				$suffix = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 6));
			}
			$reference = 'JR-' . date('Ymd') . '-' . $suffix;
		} while ($this->model_information_contract_withdrawal->referenceExists($reference));

		return $reference;
	}

	private function normalizeForm($source) {
		$fields = array(
			'full_name', 'email', 'phone', 'address_line', 'postal_code', 'city',
			'country_code', 'order_number', 'contract_date', 'received_date', 'items', 'note'
		);
		$form = array();

		foreach ($fields as $field) {
			$value = isset($source[$field]) ? trim((string)$source[$field]) : '';
			$form[$field] = trim(strip_tags($value));
		}

		$form['email'] = utf8_strtolower($form['email']);
		$form['country_code'] = utf8_strtoupper($form['country_code']);

		return $form;
	}

	private function getFormValues() {
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			return $this->normalizeForm($this->request->post);
		}

		if (!empty($this->request->get['edit'])) {
			$draft_key = $this->draft_session_prefix . (string)$this->request->get['edit'];
			$draft = isset($this->session->data[$draft_key]) ? $this->session->data[$draft_key] : array();

			if ($draft && !empty($draft['data']) && !empty($draft['expires_at']) && (int)$draft['expires_at'] >= time()) {
				return $draft['data'];
			}
		}

		$form = array(
			'full_name' => '',
			'email' => '',
			'phone' => '',
			'address_line' => '',
			'postal_code' => '',
			'city' => '',
			'country_code' => 'HR',
			'order_number' => '',
			'contract_date' => '',
			'received_date' => '',
			'items' => '',
			'note' => ''
		);

		if ($this->customer->isLogged()) {
			$form['full_name'] = trim($this->customer->getFirstName() . ' ' . $this->customer->getLastName());
			$form['email'] = $this->customer->getEmail();
			$form['phone'] = $this->customer->getTelephone();

			$this->load->model('account/address');
			$address = $this->model_account_address->getAddress($this->customer->getAddressId());
			if ($address) {
				$form['address_line'] = trim($address['address_1'] . ($address['address_2'] ? ', ' . $address['address_2'] : ''));
				$form['postal_code'] = $address['postcode'];
				$form['city'] = $address['city'];
				$form['country_code'] = $address['iso_code_2'] ?: 'HR';
			}
		}

		return $form;
	}

	private function languageData() {
		$keys = array(
			'heading_title', 'text_home', 'text_eyebrow', 'text_subheading', 'text_intro',
			'text_scope_note', 'text_identity_section', 'text_contract_section', 'text_email_help',
			'text_privacy_note', 'text_help_title', 'text_help_1', 'text_help_2', 'text_help_3',
			'text_return_address', 'text_instructions', 'text_review_eyebrow', 'text_review_heading',
			'text_review_subheading', 'text_declaration_title', 'text_confirmation_notice',
			'text_not_provided', 'entry_full_name', 'entry_email', 'entry_phone', 'entry_address_line',
			'entry_postal_code', 'entry_city', 'entry_country_code', 'entry_order_number',
			'entry_contract_date', 'entry_received_date', 'entry_items', 'entry_note',
			'placeholder_items', 'placeholder_note', 'button_review', 'button_edit', 'button_confirm',
			'mail_customer_heading', 'mail_admin_heading', 'mail_customer_intro', 'mail_admin_intro',
			'mail_reference', 'mail_submitted_at', 'mail_full_name', 'mail_email', 'mail_phone',
			'mail_address', 'mail_order_number', 'mail_contract_date', 'mail_received_date',
			'mail_items', 'mail_note', 'mail_declaration'
		);
		$data = array();

		foreach ($keys as $key) {
			$data[$key] = $this->language->get($key);
		}

		return $data;
	}
}
