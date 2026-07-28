<?php
class ControllerExtensionModuleMpAdditionalField extends Controller {
	private $error = array();
	private $installed = array();
	private $files = array(
		'extension/mpadditional_field/mpadditional_field',
		'extension/module/mpadditional_field'
	);

	// 17-march-2023: improvements start
	private $events_code = 'module_mpadditional_field';
	private $events = array(
		array(
			'trigger' => 'admin/view/common/column_left/before',
			'action' => 'extension/module/mpadditional_field/getMenu'
		), array(
			'trigger' => 'admin/view/catalog/product_form/after',
			'action' => 'extension/mpadditional_field/mpadditional_field/productForm'
		), array(
			'trigger' => 'admin/model/catalog/product/copyProduct/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/copyProductBefore'
		), array(
			'trigger' => 'admin/model/catalog/product/addProduct/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/addProductBefore'
		), array(
			'trigger' => 'admin/model/catalog/product/addProduct/after',
			'action' => 'extension/mpadditional_field/mpadditional_field/addProduct'
		), array(
			'trigger' => 'admin/model/catalog/product/editProduct/after',
			'action' => 'extension/mpadditional_field/mpadditional_field/editProduct'
		), array(
			'trigger' => 'admin/model/catalog/product/deleteProduct/after',
			'action' => 'extension/mpadditional_field/mpadditional_field/deleteProduct'
		), array(
			'trigger' => 'catalog/view/product/product/after',
			'action' => 'extension/mpadditional_field/mpadditional_field/product'
		), array(
			'trigger' => 'catalog/controller/common/header/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/commonHeaderBefore'
		), array(
			'trigger' => 'catalog/view/extension/module/featured/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/extension/module/bestseller/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/extension/module/latest/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/extension/module/special/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/product/category/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/product/manufacturer_info/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/product/product/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/product/search/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		), array(
			'trigger' => 'catalog/view/product/special/before',
			'action' => 'extension/mpadditional_field/mpadditional_field/extModules'
		)
	);
	// 17-march-2023: improvements end

	use mpadditional_field\trait_mpadditional_field;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpAdditionalField($registry);

		// 17-march-2023: improvements start
		/* OC2.3x event: view/after fix starts */
		if (VERSION > '2.2.0.0' && VERSION <= '2.3.0.2') {

			foreach ($this->events as $key => $value) {

				// oc2.3x common/menu.php controller not exists.
				if (strpos($value['trigger'], 'admin/') !== false) {
					continue;
				}

				$trigger_parts = explode('/', $value['trigger']);
				$tigger_end = end($trigger_parts);

				$str_part = 'catalog/view/';
				if (strpos($value['trigger'], 'catalog/view') !== false &&  $tigger_end === 'after') {
					$this->events[$key]['trigger'] = $str_part . '*/' . substr($value['trigger'], strlen($str_part));
				}
			}
		}
		/* OC2.3x event: view/after fix ends */
		// 17-march-2023: improvements end

	}
	public function install() {
		$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');
		$this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->alterTables();

		// 17-march-2023: improvements start
		$this->createEvents($this->events, $this->events_code);
		// 17-march-2023: improvements end

		// Add permissions to extension files dynamically
		$this->addFilesInPermissions($this->files);
	}

	// 17-march-2023: improvements start
	public function uninstall() {
		$this->removeEventsByCode($this->events_code);
	}
	// 17-march-2023: improvements end

	private function addFilesInPermissions($files) {
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpadditional_field')) {
			$this->load->model('user/user_group');
			foreach ($files as $file) {
				// remove list of files from permissions array to avoid troubles
				$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', $file);
				$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', $file);

				$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $file);
				$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $file);
			}
		}
	}

	private function detectFilesForPermissions() {
		$this->load->model('user/user_group');
		$user_group = $this->model_user_user_group->getUserGroup($this->user->getGroupId());

		$files = [];

		foreach ($this->files as $file) {
			if (!in_array($file, $user_group['permission']['access']) || !in_array($file, $user_group['permission']['modify'])) {
				$files[] = $file;
			}
		}

		return $files;
	}

	public function isInstall() {
		return $this->moduleIsInstalled('mpadditional_field');
	}

	public function moduleIsInstalled($module, $type = 'module') {
		if (empty($this->installed[$type])) {
			$this->load->model($this->model_file['extension/extension']['path']);

			$this->installed[$type] = $this->{$this->model_file['extension/extension']['obj']}->getInstalled($type);
		}

		return in_array($module, $this->installed[$type]);
	}

	// 17-march-2023: improvements start
	// ajax callable
	public function activateEvents() {
		$json = [];

		if (($this->request->server['REQUEST_METHOD'] == 'GET') && $this->accessValidate() && isset($this->request->get['ae']) && $this->request->get['ae'] == '1') {

			$this->load->language($this->extension_path . 'module/mpadditional_field');
			$this->load->model($this->model_file['extension/event']['path']);

			$disable_events = $this->areEventsDisable($this->events_code);

			if ($disable_events) {
				foreach ($disable_events as $event_id) {
					$this->{$this->model_file['extension/event']['obj']}->enableEvent($event_id);
				}

				$json['success'] = $this->language->get('text_success_activate_events');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// ajax callable for insert dump.

	public function updatePermissions() {
		$json = [];
		$this->load->language($this->extension_path . 'module/mpadditional_field');

		$this->addFilesInPermissions($this->detectFilesForPermissions());
		$this->session->data['success'] = $this->language->get('text_success_files_permission');
		$json['redirect'] = str_replace("&amp;", "&", $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token.'=' . $this->session->data[$this->token], true));

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// 'trigger' => 'admin/view/common/column_left/before',
	public function getMenu(&$route, &$data, &$code) {

		$this->load->language($this->extension_path . 'mpadditional_field/menu');

		$menu = array();
		$children = array();

		if ($this->user->hasPermission('access', $this->extension_path . 'module/mpadditional_field')) {
			$children[] = array(
				'id'       => 'mpadditional_field-setting',
				'name'	   => $this->labelEnableDisable((int)$this->config->get('module_mpadditional_field_status')) . ' ' . $this->language->get('text_setting'),
				'href'     => $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token.'=' . $this->session->data[$this->token], true),
				'children' => array()
			);
		}


		if ($this->user->hasPermission('access', $this->extension_path . 'mpadditional_field/mpadditional_field')) {
			$children[] = array(
				'name'	   => $this->language->get('text_additional_field'),
				'href'     => $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true),
				'children' => array()
			);
		}


		if ($children && $this->moduleIsInstalled('mpadditional_field')) {

			$menu = array(
				'id'       => 'menu-mpadditional_field',
				'icon'	   => '',
				'name'     => $this->language->get('text_mpadditional_field'),
				'href'     => '',
				'children' => $children
			);

			foreach ($data['menus'] as $key => $value) {
				if ($value['id'] == 'menu-catalog') {

					$value['children'][] = $data['menus'][$key]['children'][] = $menu;

				}
			}


		}

	}

	public function index() {
		$this->load->language($this->extension_path . 'module/mpadditional_field');

		$this->load->language($this->extension_path . 'mpadditional_field/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		// show a alert message for files that are not in premissions list
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpadditional_field')) {
			$data['files'] = $this->detectFilesForPermissions();
		} else {
			$data['files'] = [];
		}

		// 17-march-2023: improvements start
		// explicit code for 2x, and 2.3x versions only.
		if (VERSION < '3.0.0.0') {
			$this->getAllLanguageMpadditionalfield($data);
		}
		// 17-march-2023: improvements end

		// 17-march-2023: improvements start
		$data['text_disable_events'] = '';
		$data['disable_events'] = false;
		if ($this->user->hasPermission('modify', $this->extension_path . 'module/mpadditional_field')) {
			$this->createEvents($this->events, $this->events_code);
			$disable_events = $this->areEventsDisable($this->events_code);
			if ($disable_events) {
				$data['disable_events'] = true;
				$data['text_disable_events'] = $this->language->get('text_disable_events');
			}
		}
		// 17-march-2023: improvements end

		//menu
		$data['text_additional_field'] = $this->language->get('text_additional_field');
		$data['mpadditional_field'] = $this->url->link($this->extension_path . 'mpadditional_field/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true);

		$this->document->addStyle('view/stylesheet/mpadditional_field/stylesheet.css');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_mpadditional_field', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['stay_here']) && $this->request->post['stay_here'] == 1) {
				$this->response->redirect($this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true));
			}

			$this->response->redirect($this->url->link('marketplace/extension', $this->token . '=' . $this->session->data[$this->token] . '&type=module', true));
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->token . '=' . $this->session->data[$this->token], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', $this->token . '=' . $this->session->data[$this->token] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true)
		);

		$data['action'] = $this->url->link($this->extension_path . 'module/mpadditional_field', $this->token . '=' . $this->session->data[$this->token], true);

		$data['cancel'] = $this->url->link('marketplace/extension', $this->token . '=' . $this->session->data[$this->token] . '&type=module', true);

		$data['get_token'] = $this->token;
		$data['token'] = $this->session->data[$this->token];
		$data['extension_path'] = $this->extension_path;

		if (isset($this->request->post['module_mpadditional_field_status'])) {
			$data['module_mpadditional_field_status'] = $this->request->post['module_mpadditional_field_status'];
		} else {
			$data['module_mpadditional_field_status'] = $this->config->get('module_mpadditional_field_status');
		}
		if (isset($this->request->post['module_mpadditional_field_imgage_width'])) {
			$data['module_mpadditional_field_imgage_width'] = $this->request->post['module_mpadditional_field_imgage_width'];
		} else {
			$data['module_mpadditional_field_imgage_width'] = $this->config->get('module_mpadditional_field_imgage_width');
		}
		if (isset($this->request->post['module_mpadditional_field_imgage_height'])) {
			$data['module_mpadditional_field_imgage_height'] = $this->request->post['module_mpadditional_field_imgage_height'];
		} else {
			$data['module_mpadditional_field_imgage_height'] = $this->config->get('module_mpadditional_field_imgage_height');
		}
		if (isset($this->request->post['module_mpadditional_field_product_listing'])) {
			$data['module_mpadditional_field_product_listing'] = $this->request->post['module_mpadditional_field_product_listing'];
		} else {
			$data['module_mpadditional_field_product_listing'] = $this->config->get('module_mpadditional_field_product_listing');
		}

		$data['header'] = $this->language->get('common/header');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->viewLoad($this->extension_path . 'module/mpadditional_field', $data));
	}

	protected function hasModifyPermission() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'module/mpadditional_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->extension_path . 'module/mpadditional_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	// 17-march-2023: improvements start
	protected function accessValidate() {
		if (!$this->user->hasPermission('access', $this->extension_path . 'module/mpadditional_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	// event functions starts

	// model like functions starts
	public function areEventsDisable($code) {
		$disable_events = [];

		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return $disable_events;
		}

		// get events from db
		$query = $this->db->query("SELECT DISTINCT `event_id` FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "' AND `status`=0");

		foreach ($query->rows as $key => $value) {
			$disable_events[] = $value['event_id'];
		}

		return $disable_events;
	}

	public function removeEventsByCode($code) {
		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "'");
	}

	public function removeEvent($event_id) {
		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `event_id`='" . (int)$event_id . "'");
	}

	public function createEvents($events, $code) {

		// no events for oc version 2.2.0.0 or below. Only OCMOD.
		if (VERSION <= '2.2.0.0') {
			return;
		}

		$this->load->model($this->model_file['extension/event']['path']);
		$defaults = [
			'status' => 1,
			'sort_order' => 0,
			'description' => '',
		];

		// get events from db
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `code`='" . $this->db->escape($code) . "'");

		$db_events = [];
		foreach ($query->rows as $key => $value) {
			$triact = "{$value['trigger']}==={$value['action']}";
			$db_events[] = $triact;
		}

		$removed_events_in_db = [];
		$trion = [];
		foreach ($events as $key => $event) {
			$triact = "{$event['trigger']}==={$event['action']}";
			$trion[] = $triact;
			if (!in_array($triact, $db_events)) {
				$removed_events_in_db[] = $event;
			}
		}

		// non required events present in database.
		$non_required_events = [];
		foreach ($query->rows as $key => $value) {
			$triact = "{$value['trigger']}==={$value['action']}";
			if (!in_array($triact, $trion)) {
				$non_required_events[] = $value;
			}
		}

		// delete non required events from database
		foreach ($non_required_events as $key => $value) {
			$this->removeEvent($value['event_id']);
		}

		foreach ($removed_events_in_db as $event) {

			// add default keys in array
			foreach ($defaults as $key => $value) {
				if (!isset($event[$key])) {
					$event[$key] = $value;
				}
			}

			$this->{$this->model_file['extension/event']['obj']}->addEvent($code, $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
		}
	}
	// model like functions ends
}

