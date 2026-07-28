<?php
class ControllerExtensionMpadditionalfieldMpadditionalfield extends Controller {
	private $error = array();

	use mpadditional_field\trait_mpadditional_field;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpAdditionalField($registry);

	}

	public function index() {
		$this->load->language($this->extension_path . 'mpadditional_field/mpadditional_field');
		$this->load->language($this->extension_path . 'mpadditional_field/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');
		
		$this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->alterTables();

		$this->getList();
	}

	public function add() {
		$this->load->language($this->extension_path . 'mpadditional_field/mpadditional_field');
		$this->load->language($this->extension_path . 'mpadditional_field/product_mpadditional_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->addMpAdditionalField($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language($this->extension_path . 'mpadditional_field/mpadditional_field');
		$this->load->language($this->extension_path . 'mpadditional_field/product_mpadditional_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->editMpAdditionalField($this->request->get['mpadditional_field_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language($this->extension_path . 'mpadditional_field/mpadditional_field');

		$this->load->language($this->extension_path . 'mpadditional_field/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $mpadditional_field_id) {
				$this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->deleteMpAdditionalField($mpadditional_field_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$this->document->addStyle('view/stylesheet/mpadditional_field/stylesheet.css');
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'afd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', ''. $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url, true)
		);

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpadditionalfield($data);
		}
		// 17-march-2023: improvements end

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;
		$data['languages'] = $this->getLanguages();

		$data['add'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field/add', $this->token . '=' . $this->session->data[$this->token] . $url, true);
		$data['delete'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field/delete', $this->token . '=' . $this->session->data[$this->token] . $url, true);

		// additional field settings menu start
		$this->load->language($this->extension_path . 'mpadditional_field/menu');

		$data['mpadditional_field_config'] = $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true);

		if (!$this->load->controller($this->extension_path . 'module/mpadditional_field/isInstall')) {
			$data['mpadditional_field_config'] = $this->url->link('marketplace/extension', $this->token . '=' . $this->session->data[$this->token], true);
		}
		$data['text_additional_field_config'] = $this->language->get('text_additional_field_config');
		// additional field settings menu end

		// additional field status start
		$this->load->language($this->extension_path . 'mpadditional_field/menu');
		$data['mpadditional_field_status'] = $this->config->get('module_mpadditional_field_status');
		$data['text_additional_field_disable'] = sprintf($this->language->get('text_additional_field_disable'), $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true));
		// additional field status end


		$data['mpadditional_fields'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$mpadditional_field_total = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getTotalMpAdditionalFields();

		$results = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFields($filter_data);

		foreach ($results as $result) {
			$data['mpadditional_fields'][] = array(
				'mpadditional_field_id'  => $result['mpadditional_field_id'],
				'name'       => $result['name'],
				'sort_order' => $result['sort_order'],
				'ostatus' => $result['status'],
				'status' => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'       => $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field/edit', $this->token . '=' . $this->session->data[$this->token] . '&mpadditional_field_id=' . $result['mpadditional_field_id'] . $url, true)
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . '&sort=afd.name' . $url, true);
		$data['sort_status'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . '&sort=af.status' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . '&sort=af.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $mpadditional_field_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($mpadditional_field_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($mpadditional_field_total - $this->config->get('config_limit_admin'))) ? $mpadditional_field_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $mpadditional_field_total, ceil($mpadditional_field_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'mpadditional_field/mpadditional_field_list', $data));
	}

	protected function getForm() {
		$this->document->addStyle('view/stylesheet/mpadditional_field/stylesheet.css');

		$this->load->model('tool/image');

		$data['text_form'] = !isset($this->request->get['mpadditional_field_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['maf_product'])) {
			$data['error_maf_product'] = $this->error['maf_product'];
		} else {
			$data['error_maf_product'] = array();
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', ''. $url, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url, true)
		);

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpadditionalfield($data);
		}
		// 17-march-2023: improvements end

		if (!isset($this->request->get['mpadditional_field_id'])) {
			$data['action'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field/add', $this->token . '=' . $this->session->data[$this->token] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field/edit', $this->token . '=' . $this->session->data[$this->token] . '&mpadditional_field_id=' . $this->request->get['mpadditional_field_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token] . $url, true);

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;
		$data['languages'] = $this->getLanguages();

		if (isset($this->request->get['mpadditional_field_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$mpadditional_field_info = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalField($this->request->get['mpadditional_field_id']);
		}

		$data['mpadditional_field_id'] = 0;
		if (isset($this->request->get['mpadditional_field_id'])) {
			$data['mpadditional_field_id'] = (int)$this->request->get['mpadditional_field_id'];
		}

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (isset($this->request->get['mpadditional_field_id'])) {
			$data['description'] = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFieldDescriptions($this->request->get['mpadditional_field_id']);
		} else {
			$data['description'] = array();
		}
		
		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($mpadditional_field_info)) {
			$data['sort_order'] = $mpadditional_field_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($mpadditional_field_info)) {
			$data['status'] = $mpadditional_field_info['status'];
		} else {
			$data['status'] = '1';
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['maf_product'])) {

			$maf_products = array();
			foreach ($this->request->post['maf_product'] as $key => $value) {
				foreach ($value['mpadditional_fields'] as $key2 => $value2) {
					$value2['product_id'] = $value['product_id'];
					$value2['mpadditional_field_id'] = isset($this->request->get['mpadditional_field_id']) ? $this->request->get['mpadditional_field_id'] : 0;
					$maf_products[] = $value2;
				}

			}
		} elseif (isset($this->request->get['mpadditional_field_id'])) {
			$maf_products = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFieldsInProduct($this->request->get['mpadditional_field_id']);
		} else {
			$maf_products = array();
		}

		$data['maf_products'] = array();

		$products_info = array();

		$this->load->model('catalog/product');

		foreach ($maf_products as $key => $value) {
			if (!isset($products_info[ $value['product_id'] ])) {
				$products_info[ $value['product_id'] ] = $this->model_catalog_product->getProduct($value['product_id']);
			}

			$maf_products[$key]['ostatus'] = $value['ostatus'] = $value['status'];

			$maf_products[$key]['status'] = $value['status'] = $value['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');

			$thumb = 'no_image.png';
			if (!empty($value['image']) && is_file(DIR_IMAGE . $value['image'])) {
				$thumb = $value['image'];
			}

			$maf_products[$key]['thumb'] = $value['thumb'] = $this->model_tool_image->resize($thumb, 100, 100);

			$product = $products_info[ $value['product_id'] ];

			if ($product) {

				if (!isset($data['maf_products'][$product['product_id']])) {

					$data['maf_products'][$product['product_id']] = array(
						'product_id' => $product['product_id'],
						'name' => strip_tags(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')),
						'ostatus' => $product['status'],
						'status' => $product['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
						'mpadditional_fields' => array(),
					);
				}

				$data['maf_products'][$product['product_id']]['mpadditional_fields'][] = $value;
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'mpadditional_field/mpadditional_field_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'mpadditional_field/mpadditional_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 128)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if (isset($this->request->post['maf_product'])) {
			foreach ($this->request->post['maf_product'] as $product_id => $maf_product) {

				if (!empty($maf_product['mpadditional_fields'])) {
					foreach ($maf_product['mpadditional_fields'] as $key => $mpadditional_fields) {

						foreach ($mpadditional_fields['description'] as $language_id => $description) {

							if (empty($description['value'])) {
								$this->error['maf_product'][$product_id]['mpadditional_fields'][$key]['description'][$language_id]['value'] = $this->language->get('error_value');
							}

						}

					}
				}

			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'mpadditional_field/mpadditional_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');

		foreach ($this->request->post['selected'] as $mpadditional_field_id) {
			$total = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getTotalMpAdditionalFieldsByProductId($mpadditional_field_id);

			if ($total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $total);
			}
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->language($this->extension_path . 'mpadditional_field/mpadditional_field');

			$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');

			$this->load->model('tool/image');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$mpadditional_fields = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFields($filter_data);

			foreach ($mpadditional_fields as $mpadditional_field) {
				$json[] = array(
					'mpadditional_field_id' => $mpadditional_field['mpadditional_field_id'],
					'name' => strip_tags(html_entity_decode($mpadditional_field['name'], ENT_QUOTES, 'UTF-8')),
					'ostatus' => $mpadditional_field['status'],
					'status' => $mpadditional_field['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocompleteProductMpAdditionalField() {
		$json = array();

		if (isset($this->request->get['filter_name']) && isset($this->request->get['mpadditional_field_id'])) {
			$this->load->language($this->extension_path . 'mpadditional_field/mpadditional_field');

			$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');

			$this->load->model('catalog/product');

			$this->load->model('tool/image');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 10
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {

				$maf_products = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFieldsInProduct((int)$this->request->get['mpadditional_field_id'], array($result['product_id']));

				foreach ($maf_products as $key => $value) {

					$maf_products[$key]['ostatus'] = $value['ostatus'] = $value['status'];

					$maf_products[$key]['status'] = $value['status'] = $value['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');

					$thumb = 'no_image.png';
					if (!empty($value['image']) && is_file(DIR_IMAGE . $value['image'])) {
						$thumb = $value['image'];
					}

					$maf_products[$key]['thumb'] = $value['thumb'] = $this->model_tool_image->resize($thumb, 100, 100);
				}

				if (!isset($json[$result['product_id']])) {

					$json[$result['product_id']] = array(
						'product_id' => $result['product_id'],
						'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
						'ostatus' => $result['status'],
						'status' => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
						'mpadditional_fields' => array(),
					);
				}

				$json[$result['product_id']]['mpadditional_fields'] = $maf_products;
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Event functions starts

	// 'trigger' => 'admin/model/catalog/product/copyProduct/before',
	public function copyProductBefore(&$route, &$args) {
		$this->session->data['mpadditional_field_copy_product_id'] = (int)$args[0];
	}

	// 'trigger' => 'admin/model/catalog/product/addProduct/before',
	public function addProductBefore(&$route, &$args) {
		// Copy product case.
		// Current logic fail
		// if $data['product_id'] exists for addProduct
		// and customer wanted to remove all additional fields.
		// we need copyProduct before event to ensure
		// if a product is being copied or not.
		$data = &$args[0];

		if (isset($this->session->data['mpadditional_field_copy_product_id'])) {

			// $product_id = $data['product_id'];
			$product_id = $this->session->data['mpadditional_field_copy_product_id'];

			$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');
			$data['product_mpadditional_field'] = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFieldsByProductId($product_id);

			foreach ($data['product_mpadditional_field'] as $key => $value) {
				$data['product_mpadditional_field'][$key]['product_mpadditional_field_id'] = $value['product_mpadditional_field_id'] = 0;
			}

			unset($this->session->data['mpadditional_field_copy_product_id']);
		}

	}

	// 'trigger' => 'admin/model/catalog/product/addProduct/after',
	public function addProduct(&$route, &$args, &$output) {
		$data = &$args[0];
		$product_id = $output;

		if (isset($data['product_mpadditional_field'])) {
			foreach ($data['product_mpadditional_field'] as $product_mpadditional_field) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field SET product_mpadditional_field_id = '" . (int)$product_mpadditional_field['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$product_mpadditional_field['mpadditional_field_id'] . "', product_id = '" . (int)$product_id . "', status = '" . (int)$product_mpadditional_field['status'] . "', sort_order = '" . (int)$product_mpadditional_field['sort_order'] . "', image = '" . $this->db->escape($product_mpadditional_field['image']) . "', width = '" . $this->db->escape($product_mpadditional_field['width']) . "', height = '" . $this->db->escape($product_mpadditional_field['height']) . "'");

				$product_mpadditional_field['product_mpadditional_field_id'] = $this->db->getLastId();

				foreach ($product_mpadditional_field['description'] as $language_id => $description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field_value SET product_mpadditional_field_id = '" . (int)$product_mpadditional_field['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$product_mpadditional_field['mpadditional_field_id'] . "', product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape($description['value']) . "'");
				}

			}
		}
	}

	// 'trigger' => 'admin/model/catalog/product/editProduct/after',
	public function editProduct(&$route, &$args, &$output) {
		$data = &$args[1];
		$product_id = $args[0];

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field_value WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_mpadditional_field'])) {

			foreach ($data['product_mpadditional_field'] as $product_mpadditional_field) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field SET product_mpadditional_field_id = '" . (int)$product_mpadditional_field['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$product_mpadditional_field['mpadditional_field_id'] . "', product_id = '" . (int)$product_id . "', status = '" . (int)$product_mpadditional_field['status'] . "', sort_order = '" . (int)$product_mpadditional_field['sort_order'] . "', image = '" . $this->db->escape($product_mpadditional_field['image']) . "', width = '" . $this->db->escape($product_mpadditional_field['width']) . "', height = '" . $this->db->escape($product_mpadditional_field['height']) . "'");

				$product_mpadditional_field['product_mpadditional_field_id'] = $this->db->getLastId();

				foreach ($product_mpadditional_field['description'] as $language_id => $description) {

					$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field_value SET product_mpadditional_field_id = '" . (int)$product_mpadditional_field['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$product_mpadditional_field['mpadditional_field_id'] . "', product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape($description['value']) . "'");
				}
			}
		}
	}


	// 'trigger' => 'admin/model/catalog/product/deleteProduct/after',
	public function deleteProduct(&$route, &$args, &$output) {
		$product_id = $args[0];

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field_value WHERE product_id = '" . (int)$product_id . "'");
	}

	// 'trigger' => 'admin/view/catalog/product/after',
	public function productForm(&$route, &$data, &$output) {

		$this->load->model('tool/image');
		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');
		$this->load->language($this->extension_path . 'mpadditional_field/product_mpadditional_field');

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;

		// additional field status start
		$this->load->language($this->extension_path . 'mpadditional_field/menu');
		$data['mpadditional_field_status'] = $this->config->get('module_mpadditional_field_status');
		$data['text_additional_field_disable'] = sprintf($this->language->get('text_additional_field_disable'), $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true));
		// additional field status end

		// additional field menu end
		$this->load->language($this->extension_path . 'mpadditional_field/menu');
		$data['text_additional_field'] = $this->language->get('text_additional_field');
		$data['mpadditional_field'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true);
		// additional field menu end

		// additional field settings menu start
		$this->load->language($this->extension_path . 'mpadditional_field/menu');
		$data['mpadditional_field_config'] = $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true);

		if (!$this->load->controller($this->extension_path . 'module/mpadditional_field/isInstall')) {
			$data['mpadditional_field_config'] = $this->url->link('marketplace/extension', $this->token . '=' . $this->session->data[$this->token], true);
		}
		$data['text_additional_field_config'] = $this->language->get('text_additional_field_config');

		// additional field settings menu end

		if (isset($data['languages'])) {
			$this->parseLanguages($data['languages']);
		} else {
			$data['languages'] = $this->getLanguages();
		}

		$data['entry_additional_field'] = $this->language->get('entry_additional_field');
		$data['entry_text1'] = $this->language->get('entry_text1');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_maf_description'] = $this->language->get('entry_maf_description');
		$data['entry_image_size'] = $this->language->get('entry_image_size');
		$data['entry_awidth'] = $this->language->get('entry_awidth');
		$data['entry_aheight'] = $this->language->get('entry_aheight');

		$data['help_image_size'] = $this->language->get('help_image_size');

		$data['tab_mpadditional_field'] = $this->language->get('tab_mpadditional_field');

		if (isset($this->request->post['product_mpadditional_field'])) {
			$product_mpadditional_fields = $this->request->post['product_mpadditional_field'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_mpadditional_fields = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getMpAdditionalFieldsByProductId($this->request->get['product_id']);
		} else {
			$product_mpadditional_fields = array();
		}

		$data['product_mpadditional_fields'] = array();

		foreach ($product_mpadditional_fields as $product_mpadditional_field) {
			$image = '';
			$thumb = 'no_image.png';
			if (is_file(DIR_IMAGE . $product_mpadditional_field['image'])) {
				$image = $product_mpadditional_field['image'];
				$thumb = $product_mpadditional_field['image'];
			}

			$data['product_mpadditional_fields'][] = array(
				'mpadditional_field_id' => $product_mpadditional_field['mpadditional_field_id'],
				'product_mpadditional_field_id' => $product_mpadditional_field['product_mpadditional_field_id'],
				'name' => $product_mpadditional_field['name'],
				'width' => $product_mpadditional_field['width'],
				'height' => $product_mpadditional_field['height'],
				'sort_order' => $product_mpadditional_field['sort_order'],
				'ostatus' => $product_mpadditional_field['status'],
				'status' => $product_mpadditional_field['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'ad_ostatus' => $product_mpadditional_field['ad_status'],
				'ad_status' => $product_mpadditional_field['ad_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'image' => $image,
				'thumb' => $this->model_tool_image->resize($thumb, 100, 100),
				'description' => $product_mpadditional_field['description']
			);
		}

		$replace = $this->viewLoad($this->extension_path . 'mpadditional_field/product_form', $data);

		$find = '<div class="tab-pane" id="tab-recurring">';
		$output = str_replace($find, $replace . "\n" . $find, $output);

		$find = '<li><a href="#tab-design" data-toggle="tab">' . $data['tab_design'] . '</a></li>';

		$replace = '<li><a href="#tab-mpadditional_field" data-toggle="tab"><i class="fa fa-plus-circle"></i>' . $data['tab_mpadditional_field'] . '</a></li>';
		$output = str_replace($find, $replace . "\n" . $find, $output);
	}

	// hook type functions, controller callable using ocmod, as event can not touch $this->error
	public function dataProductForm($args) {
		$error = &$args['error'];
		$data = &$args['data'];

		if (isset($error['additional_field'])) {
			$data['error_additional_field'] = $error['additional_field'];
		} else {
			$data['error_additional_field'] = array();
		}

	}
	public function validateProductForm($args) {
		$error = &$args['error'];

		if (isset($this->request->post['product_mpadditional_field'])) {

			$this->load->language($this->extension_path . 'mpadditional_field/product_mpadditional_field');

			foreach ($this->request->post['product_mpadditional_field'] as $row => $product_mpadditional_field) {
				if (isset($product_mpadditional_field['description'])) {
					foreach ($product_mpadditional_field['description'] as $language_id => $description) {
						if (empty($description['value'])) {
							$error['additional_field'][$row][$language_id] = $this->language->get('error_value');
						}
					}
				}
			}
		}
	}

	// Event functions ends
}
