<?php
class ControllerCommonRobots extends Controller {
	public function index() {
		$file = DIR_APPLICATION . '../robots.txt';

		if (!is_file($file)) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			return;
		}

		http_response_code(200);
		$this->response->addHeader('Content-Type: text/plain; charset=UTF-8');
		$this->response->setOutput(file_get_contents($file));
	}

	public function llms() {
		$file = DIR_APPLICATION . '../llms.txt';

		if (!is_file($file)) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			return;
		}

		http_response_code(200);
		$this->response->addHeader('Content-Type: text/plain; charset=UTF-8');
		$this->response->setOutput(file_get_contents($file));
	}
}
