<?php
class ControllerSaleContractWithdrawal extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('sale/contract_withdrawal');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/contract_withdrawal');

		$filter_reference = isset($this->request->get['filter_reference']) ? trim((string)$this->request->get['filter_reference']) : '';
		$filter_order_number = isset($this->request->get['filter_order_number']) ? trim((string)$this->request->get['filter_order_number']) : '';
		$filter_email = isset($this->request->get['filter_email']) ? trim((string)$this->request->get['filter_email']) : '';
		$filter_status = isset($this->request->get['filter_status']) ? trim((string)$this->request->get['filter_status']) : '';
		$filter_date_added = isset($this->request->get['filter_date_added']) ? trim((string)$this->request->get['filter_date_added']) : '';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;
		$limit = (int)$this->config->get('config_limit_admin');

		if ($limit < 1) {
			$limit = 20;
		}

		$filter_data = array(
			'filter_reference' => $filter_reference,
			'filter_order_number' => $filter_order_number,
			'filter_email' => $filter_email,
			'filter_status' => $filter_status,
			'filter_date_added' => $filter_date_added,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$total = $this->model_sale_contract_withdrawal->getTotalWithdrawals($filter_data);
		$results = $this->model_sale_contract_withdrawal->getWithdrawals($filter_data);
		$statuses = $this->statuses();
		$data = $this->languageData();
		$data['withdrawals'] = array();

		foreach ($results as $result) {
			$data['withdrawals'][] = array(
				'contract_withdrawal_id' => $result['contract_withdrawal_id'],
				'reference' => $result['reference'],
				'order_number' => $result['order_number'],
				'full_name' => $result['full_name'],
				'email' => $result['email'],
				'status' => isset($statuses[$result['status']]) ? $statuses[$result['status']] : $result['status'],
				'submitted_at' => $result['submitted_at'],
				'notification_warning' => !empty($result['notification_error']),
				'edit' => $this->url->link('sale/contract_withdrawal/edit', 'user_token=' . $this->session->data['user_token'] . '&contract_withdrawal_id=' . (int)$result['contract_withdrawal_id'], true)
			);
		}

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'], true)
			)
		);

		$url = '';
		foreach (array('filter_reference', 'filter_order_number', 'filter_email', 'filter_status', 'filter_date_added') as $key) {
			if (isset($this->request->get[$key]) && $this->request->get[$key] !== '') {
				$url .= '&' . $key . '=' . urlencode((string)$this->request->get[$key]);
			}
		}

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), $total ? (($page - 1) * $limit) + 1 : 0, min($total, $page * $limit), $total, $limit ? ceil($total / $limit) : 1);
		$data['filter_reference'] = $filter_reference;
		$data['filter_order_number'] = $filter_order_number;
		$data['filter_email'] = $filter_email;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;
		$data['statuses'] = $statuses;
		$data['user_token'] = $this->session->data['user_token'];
		$data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
		unset($this->session->data['success']);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/contract_withdrawal_list', $data));
	}

	public function edit() {
		$this->load->language('sale/contract_withdrawal');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/contract_withdrawal');

		$withdrawal_id = isset($this->request->get['contract_withdrawal_id']) ? (int)$this->request->get['contract_withdrawal_id'] : 0;
		$withdrawal = $this->model_sale_contract_withdrawal->getWithdrawal($withdrawal_id);

		if (!$withdrawal) {
			$this->response->redirect($this->url->link('sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateForm()) {
			$this->model_sale_contract_withdrawal->editWithdrawal($withdrawal_id, array(
				'status' => $this->request->post['status'],
				'internal_note' => isset($this->request->post['internal_note']) ? $this->request->post['internal_note'] : '',
				'handled_by' => $this->user->getId()
			));

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('sale/contract_withdrawal/edit', 'user_token=' . $this->session->data['user_token'] . '&contract_withdrawal_id=' . $withdrawal_id, true));
			return;
		}

		$data = $this->languageData();
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['withdrawal'] = $withdrawal;
		$data['statuses'] = $this->statuses();
		$data['status'] = isset($this->request->post['status']) ? $this->request->post['status'] : $withdrawal['status'];
		$data['internal_note'] = isset($this->request->post['internal_note']) ? $this->request->post['internal_note'] : $withdrawal['internal_note'];
		$data['action'] = $this->url->link('sale/contract_withdrawal/edit', 'user_token=' . $this->session->data['user_token'] . '&contract_withdrawal_id=' . $withdrawal_id, true);
		$data['cancel'] = $this->url->link('sale/contract_withdrawal', 'user_token=' . $this->session->data['user_token'], true);
		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $data['cancel']
			),
			array(
				'text' => $withdrawal['reference'],
				'href' => $data['action']
			)
		);
		$data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
		unset($this->session->data['success']);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/contract_withdrawal_form', $data));
	}

	private function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/contract_withdrawal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['status']) || !array_key_exists($this->request->post['status'], $this->statuses())) {
			$this->error['warning'] = $this->language->get('error_status');
		}

		if (isset($this->request->post['internal_note']) && utf8_strlen($this->request->post['internal_note']) > 10000) {
			$this->error['warning'] = $this->language->get('error_note');
		}

		return !$this->error;
	}

	private function statuses() {
		return array(
			'received' => $this->language->get('status_received'),
			'in_progress' => $this->language->get('status_in_progress'),
			'completed' => $this->language->get('status_completed')
		);
	}

	private function languageData() {
		$keys = array(
			'heading_title', 'text_home', 'text_list', 'text_form', 'text_no_results',
			'text_filter', 'text_notification_warning', 'entry_reference', 'entry_order_number',
			'entry_customer', 'entry_email', 'entry_phone', 'entry_address', 'entry_contract_date',
			'entry_received_date', 'entry_items', 'entry_note', 'entry_declaration', 'entry_status',
			'entry_internal_note', 'entry_submitted_at', 'entry_consumer_notified_at',
			'entry_admin_notified_at', 'entry_snapshot_hash', 'button_filter', 'button_save',
			'button_cancel', 'button_edit'
		);
		$data = array();
		foreach ($keys as $key) {
			$data[$key] = $this->language->get($key);
		}
		return $data;
	}
}
