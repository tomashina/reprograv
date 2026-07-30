<?php
class ControllerCommonHome extends Controller {
	public function index() {
		if (
			isset($this->request->get['route']) &&
			$this->request->get['route'] === 'common/home' &&
			!isset($this->request->get['_route_']) &&
			isset($this->request->server['REQUEST_URI']) &&
			strpos($this->request->server['REQUEST_URI'], 'route=common/home') !== false
		) {
			$this->response->redirect($this->config->get('config_ssl') ?: $this->config->get('config_url'), 301);
			return;
		}

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		$this->document->addLink($this->config->get('config_ssl') ?: $this->config->get('config_url'), 'canonical');

		

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
