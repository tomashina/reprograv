<?php
class ControllerExtensionModuleInformationParent extends Controller {
	public function index() {
		$this->load->language('extension/module/information_parent');

		$informationdescription = $this->config->get('module_information_parent_description');
		if(!empty($informationdescription[$this->config->get('config_language_id')]['title'])){
			$data['heading_title'] = $informationdescription[$this->config->get('config_language_id')]['title'];
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}
		
		// Color Setting
		$data['headingbgcolor'] =$this->config->get('module_information_parent_headingbgcolor');
		$data['headingtextcolor'] =$this->config->get('module_information_parent_headingtextcolor');
		$data['listbordercolor'] =$this->config->get('module_information_parent_listbordercolor');
		$data['infolistbgcolor'] =$this->config->get('module_information_parent_infolistbgcolor');
		$data['infolisttextcolor'] =$this->config->get('module_information_parent_infolisttextcolor');
		$data['infolistbghovercolor'] =$this->config->get('module_information_parent_infolistbghovercolor');
		$data['infolistbgtexthovercolor'] =$this->config->get('module_information_parent_infolistbgtexthovercolor');
		
		$data['text_contact'] = $this->language->get('text_contact');
		$data['text_sitemap'] = $this->language->get('text_sitemap');

		$this->load->model('extension/information_parent');

		$data['informations'] = array();

		foreach ($this->model_extension_information_parent->getInformations() as $result) {
			$data['informations_children'] = array();
			foreach ($this->model_extension_information_parent->getInformations($result['information_id']) as $child_result) {
				$data['informations_children'][] = array(
					'title' => $child_result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $child_result['information_id'])
				);
			}
			
			$data['informations'][] = array(
				'title' => $result['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id']),
				'informations_children'  => $data['informations_children'],
			);
		}
		
		$data['contact'] = $this->url->link('information/contact');
		$data['sitemap'] = $this->url->link('information/sitemap');

		return $this->load->view('extension/module/information_parent', $data);
	}
}