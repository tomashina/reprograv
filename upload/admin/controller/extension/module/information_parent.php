<?php
//lib
require_once(DIR_SYSTEM.'library/tmd/system.php');
//lib
class ControllerExtensionModuleInformationParent extends Controller {
	private $error = array();
	public function install(){
		$this->load->model('extension/information_parent');
		$this->model_extension_information_parent->install();
	}	
	public function uninstall(){
		$this->load->model('extension/information_parent');
		$this->model_extension_information_parent->uninstall();
	}
	public function index() {
		
		$this->document->addStyle('view/stylesheet/jquery.minicolors.css');
		$this->document->addScript('view/javascript/colorbox/jquery.minicolors.js');

		$this->load->language('extension/module/information_parent');
		
		$this->registry->set('tmd', new TMD($this->registry));
		$keydata=array(
		'code'=>'tmdkey_information_parent',
		'eid'=>'MjcwOTI=',
		'route'=>'extension/module/information_parent',
		);
		$information_parent=$this->tmd->getkey($keydata['code']);
		$data['getkeyform']=$this->tmd->loadkeyform($keydata);

		$this->document->setTitle($this->language->get('heading_title1'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_information_parent', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();


		if (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];
		
			unset($this->session->data['warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}
		
		if (isset($this->error['module_information_parent_width'])) {
			$data['error_width'] = $this->error['module_information_parent_width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['module_information_parent_height'])) {
			$data['error_height'] = $this->error['module_information_parent_height'];
		} else {
			$data['error_height'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['action'] = $this->url->link('extension/module/information_parent', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_information_parent_status'])) {
			$data['module_information_parent_status'] = $this->request->post['module_information_parent_status'];
		} else {
			$data['module_information_parent_status'] = $this->config->get('module_information_parent_status');
		}

		if (isset($this->request->post['module_information_parent_description'])) {
			$data['module_information_parent_description'] = $this->request->post['module_information_parent_description'];
		} else {
			$data['module_information_parent_description'] = $this->config->get('module_information_parent_description');
		}
		
		if (isset($this->request->post['module_information_parent_width'])) {
			$data['module_information_parent_width'] = $this->request->post['module_information_parent_width'];
		} else {
			$data['module_information_parent_width'] = $this->config->get('module_information_parent_width');
		}
		
		if (isset($this->request->post['module_information_parent_height'])) {
			$data['module_information_parent_height'] = $this->request->post['module_information_parent_height'];
		} else {
			$data['module_information_parent_height'] = $this->config->get('module_information_parent_height');
		}
		
		// Colour Setting
		if (isset($this->request->post['module_information_parent_headingbgcolor'])) {
			$data['module_information_parent_headingbgcolor'] = $this->request->post['module_information_parent_headingbgcolor'];
		} else {
			$data['module_information_parent_headingbgcolor'] = $this->config->get('module_information_parent_headingbgcolor');
		}
		
		if (isset($this->request->post['module_information_parent_headingtextcolor'])) {
			$data['module_information_parent_headingtextcolor'] = $this->request->post['module_information_parent_headingtextcolor'];
		} else {
			$data['module_information_parent_headingtextcolor'] = $this->config->get('module_information_parent_headingtextcolor');
		}
		
		if (isset($this->request->post['module_information_parent_listbordercolor'])) {
			$data['module_information_parent_listbordercolor'] = $this->request->post['module_information_parent_listbordercolor'];
		} else {
			$data['module_information_parent_listbordercolor'] = $this->config->get('module_information_parent_listbordercolor');
		}
		
		if (isset($this->request->post['module_information_parent_infolistbgcolor'])) {
			$data['module_information_parent_infolistbgcolor'] = $this->request->post['module_information_parent_infolistbgcolor'];
		} else {
			$data['module_information_parent_infolistbgcolor'] = $this->config->get('module_information_parent_infolistbgcolor');
		}
		
		if (isset($this->request->post['module_information_parent_infolisttextcolor'])) {
			$data['module_information_parent_infolisttextcolor'] = $this->request->post['module_information_parent_infolisttextcolor'];
		} else {
			$data['module_information_parent_infolisttextcolor'] = $this->config->get('module_information_parent_infolisttextcolor');
		}
		
		if (isset($this->request->post['module_information_parent_infolistbghovercolor'])) {
			$data['module_information_parent_infolistbghovercolor'] = $this->request->post['module_information_parent_infolistbghovercolor'];
		} else {
			$data['module_information_parent_infolistbghovercolor'] = $this->config->get('module_information_parent_infolistbghovercolor');
		}
		
		if (isset($this->request->post['module_information_parent_infolistbgtexthovercolor'])) {
			$data['module_information_parent_infolistbgtexthovercolor'] = $this->request->post['module_information_parent_infolistbgtexthovercolor'];
		} else {
			$data['module_information_parent_infolistbgtexthovercolor'] = $this->config->get('module_information_parent_infolistbgtexthovercolor');
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/information_parent', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/information_parent')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}/*
		$information_parent=$this->config->get('tmdkey_information_parent');
		if (empty(trim($information_parent))) {			
		$this->session->data['warning'] ='Module will Work after add License key!';
		$this->response->redirect($this->url->link('extension/module/information_parent', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}*/
		
		foreach ($this->request->post['module_information_parent_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
		}

		if (!$this->request->post['module_information_parent_width']) {
			$this->error['module_information_parent_width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['module_information_parent_height']) {
			$this->error['module_information_parent_height'] = $this->language->get('error_height');
		}
		
		return !$this->error;
	}
	public function keysubmit() {
		$json = array(); 
		
      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$keydata=array(
			'code'=>'tmdkey_information_parent',
			'eid'=>'MjcwOTI=',
			'route'=>'extension/module/information_parent',
			'moduledata_key'=>$this->request->post['moduledata_key'],
			);
			$this->registry->set('tmd', new TMD($this->registry));
            $json=$this->tmd->matchkey($keydata);       
		} 
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}