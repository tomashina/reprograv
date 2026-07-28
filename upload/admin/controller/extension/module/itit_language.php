<?php
class ControllerExtensionModuleItitLanguage extends Controller {
	private $error = array();
	private $ocversion = '3.0.3.8';
	private $ext_version = '0.0.75';
	private $ext_type = 'module';
	private $ext_iso_code = 'it-it';
	private $ext_code = 'itit_language';
	private $ext_url_path = '';
	private $ext_obj = '';
	private $ext_setting_name = '';

	public function __construct($registry)
	{
		// __construct of the parent class opencart
		parent::__construct($registry);
		// setting the extension url path - ex. extension/itit_language/module/itit_language
		$this->ext_url_path = 'extension/' . $this->ext_type . '/' . $this->ext_code;
		// setting the object of the extension's model - ex. model_extension_itit_language_module_itit_language
		$this->ext_obj = 'model_extension_' . $this->ext_type . '_' . $this->ext_code;
		// setting the name for the extension's settings - ex. module_itit_language
		$this->ext_setting_name = $this->ext_type . '_' . $this->ext_code;
	}

	public function index() {		

		$this->load->language($this->ext_url_path);
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model($this->ext_url_path);		
		
		if (!$this->user->hasPermission('access', $this->ext_url_path)) {
			$this->error['permission'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->ext_type, true));
		}
		
		$data = [];

		// extension type and code used in the view
		$data['ext_type_code'] = $this->ext_type . '_' . $this->ext_code;
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->ext_type, true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->ext_url_path, 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['lnkcancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=' . $this->ext_type, true);
		
		//installation links
		$data['lnkinstallfull'] = $this->url->link($this->ext_url_path, 'user_token=' . $this->session->data['user_token'] . '&lnginstall=0', true);
		$data['lnkinstalleng'] = $this->url->link($this->ext_url_path, 'user_token=' . $this->session->data['user_token'] . '&lnginstall=1', true);
		$data['lnkinstallextraonly'] = $this->url->link($this->ext_url_path, 'user_token=' . $this->session->data['user_token'] . '&lnginstall=2', true);
		$data['lnklanguninstall'] = $this->url->link($this->ext_url_path, 'user_token=' . $this->session->data['user_token'] . '&lnginstall=3', true);
		
		/*
		int value for the Language installation
		lang_installation_status
		0 - Not Installed
		1 - Secondary language version (only main)
		2 - Full language Version installed
		3 - the language is already installed in opencart but the installation status is 0
		*/
		$data['lang_installation_status'] = empty($this->config->get($this->ext_setting_name . '_statuslang')) ? 0 : intval($this->config->get($this->ext_setting_name . '_statuslang'));

		//getting the version from the settings
		$data['lang_extension_version'] = $this->config->get($this->ext_setting_name . '_version');
		
		//checking if the language (from code) is already installed
		$islanguageinstalledbycode = $this->{$this->ext_obj}->islanguageinstalledbycode($this->ext_iso_code) > 0  ? true : false;
		/*
		if the language is already installed and the installation status is still 0 or empty
		3 - Language detected but not installed/configured with the extension
		*/
		if( $islanguageinstalledbycode && $data['lang_installation_status'] == 0) {
			$data['lang_installation_status'] = 3;
		}
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {


			//installation requests
			if( isset($this->request->post['lnginstall']) ) {

				
				switch( $this->request->post['lnginstall'] ) {
				//Install FULL main+extra
				case 0:
					//install only if the installation status is 0
					if($data['lang_installation_status'] == 0) { 
						//installing full main+extra
						$this->{$this->ext_obj}->sqlinstall(0);
						$this->{$this->ext_obj}->sqlinstall(1);

						$this->{$this->ext_obj}->editSetting($this->ext_setting_name, [$this->ext_setting_name . '_statuslang'=>2]);
						//changing $data['lang_installation_status'] since we changed it
						$data['lang_installation_status'] = '2';
						
						$data['success'] = $this->language->get('text_success');
					} else {
						$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' 0';
					}
				break;
				// Install main language only
				case 1:
					//install only if the installation status is 0
					if($data['lang_installation_status'] == 0) { 
						//installing main only
						$this->{$this->ext_obj}->sqlinstall(0);

						$this->{$this->ext_obj}->editSetting($this->ext_setting_name, [$this->ext_setting_name . '_statuslang'=>1]);
						//changing $data['lang_installation_status'] since we changed it
						$data['lang_installation_status'] = '1';

						$data['success'] = $this->language->get('text_success');
					} else {
						$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' 1';
					}
				break;
				case 2:
					//adding extra data if the installation status is 1 (main only)
					if($data['lang_installation_status'] == 1) { 
						//installing main only - eng shops
						$this->{$this->ext_obj}->sqlinstall(1);
						
						$this->{$this->ext_obj}->editSetting($this->ext_setting_name, [$this->ext_setting_name . '_statuslang'=>2]);
						//changing $data['lang_installation_status'] since we changed it
						$data['lang_installation_status'] = '2';
						
						$data['success'] = $this->language->get('text_success');
					} else {
						$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' 2';
					}
				break;
				case 3:
					// delete the installed language
					if($data['lang_installation_status'] !== 0) { 

						// reverting to the first available language (usually en-gb - english) and removing our language from the database
						$this->{$this->ext_obj}->sqlinstall(2);
						$this->{$this->ext_obj}->editSetting($this->ext_setting_name, [$this->ext_setting_name . '_statuslang'=>0]);
						//changing $data['lang_installation_status'] since we changed it
						$data['lang_installation_status'] = '0';
						
						$data['success'] = $this->language->get('text_success');
					} else {
						$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' 3';
					}
				break;
				case 4:
					//tries to restore a misconfigured installation
					if($data['lang_installation_status'] == 3) { 

						$detection = $this->{$this->ext_obj}->detectlanguageinstallation();
						
						if( empty($detection) ) {
							$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' 4';
						} else {
							$this->{$this->ext_obj}->editSetting($this->ext_setting_name, [$this->ext_setting_name . '_statuslang'=> $detection ]);
							//changing $data['lang_installation_status'] since we changed it
							$data['lang_installation_status'] = $detection;
							$data['success'] = $this->language->get('text_success');
						}
						
					} else {
						$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' 4';
					}
				break;
				default:
					$this->error['langinstallation'] = $this->language->get('error_langinstallation') . ' -1';
				break;
				}
				
			}
		}

		// checking the compatibility with the current version of opencart | do not move this code before validate()
		if(VERSION !== $this->ocversion){ 
			$this->error['error_ocversion'] = $this->language->get('error_ocversion') . $this->ocversion;
		}

		$data['error'] = $this->error;	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view($this->ext_url_path, $data));
	}

	public function validate() {
		$this->load->language($this->ext_url_path);
		$this->load->model($this->ext_url_path);

		if (!$this->user->hasPermission('modify', $this->ext_url_path)) {
			$this->error['permission'] = $this->language->get('error_permission');
			return false;
		}

		$current_collation = $this->{$this->ext_obj}->connectionCollation();
		
		if(!$this->{$this->ext_obj}->checkCollation($current_collation)) {
			$this->error['dbcollation'] = sprintf($this->language->get('error_dbcollation'), $current_collation);
		}

		return empty($this->error);
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->load->model($this->ext_url_path);

		// status of the language installation
		$status_lang = 0;
		
		// direct installation of the BASE translations if detected the correct OC version
		if(VERSION == $this->ocversion && $this->{$this->ext_obj}->islanguageinstalledbycode($this->ext_iso_code) == 0) { 
			$this->load->model($this->ext_url_path);
			

			//installing main only
			$this->{$this->ext_obj}->sqlinstall(0);
			// main language installed
			$status_lang = 1;
		}

		//enabling the status and adding the version
		$this->model_setting_setting->editSetting($this->ext_setting_name, [$this->ext_setting_name . '_status' => '1', $this->ext_setting_name . '_statuslang' => $status_lang, $this->ext_setting_name . '_version' => $this->ext_version]);
	}
	
	public function uninstall() {
		$this->load->model('setting/setting');
		$this->load->model($this->ext_url_path);

		if ($this->{$this->ext_obj}->islanguageinstalledbycode($this->ext_iso_code) == 1) {
			// reverting to the first available language (usually en-gb - english) and removing our language from the database
			$this->{$this->ext_obj}->sqlinstall(2);
		}
		$this->model_setting_setting->deleteSetting($this->ext_setting_name);
	}
}
