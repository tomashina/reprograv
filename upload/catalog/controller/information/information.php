<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);
			$this->document->addLink(
				$this->url->link('information/information', 'information_id=' . $information_id, true),
				'canonical'
			);

			$this->load->model('catalog/information');
			$breadcrumbs_info = $this->model_catalog_information->getInformationBreadcrumbs($information_id);

			foreach ($breadcrumbs_info as $breadcrumb) {
			    $data['breadcrumbs'][] = [
			        'text' => $breadcrumb['title'],
			        'href' => $breadcrumb['href']
			    ];
			}

			$this->load->model('catalog/information_pdf');
           $data['information_pdfs'] = $this->model_catalog_information_pdf->getPdfs($information_id);


			$data['heading_title'] = $information_info['title'];

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$this->load->model('catalog/information_block');
			$data['information_blocks'] = $this->model_catalog_information_block->getBlocks($information_id);

			foreach ($data['information_blocks'] as &$information_block) {
				$image = trim((string)$information_block['image']);

				if (preg_match('#^https?://#i', $image)) {
					$information_block['image_src'] = $image;
				} elseif ($image !== '' && strpos($image, '..') === false) {
					$information_block['image_src'] = HTTPS_SERVER . 'image/' . ltrim($image, '/');
				} else {
					$information_block['image_src'] = '';
				}

				foreach ($information_block['actions'] as &$information_block_action) {
					if ($information_block_action['type'] === 'file' && $information_block_action['filename']) {
						$download_arguments = 'f=' . rawurlencode($information_block_action['filename']);

						if ($information_block_action['mask']) {
							$download_arguments .= '&m=' . rawurlencode($information_block_action['mask']);
						}

						$information_block_action['href'] = $this->url->link('information/download', $download_arguments, true);
					} else {
						$information_block_action['href'] = $this->normaliseActionUrl($information_block_action['url']);
					}

					if (!$information_block_action['label']) {
						$information_block_action['label'] = $information_block_action['type'] === 'file' ? 'Preuzmite datoteku' : 'Saznajte više';
					}
				}
				unset($information_block_action);
			}
			unset($information_block);

			$data['information_blocks_html'] = $data['information_blocks'] ? $this->load->view('information/information_blocks', $data) : '';

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->addHeader('X-Robots-Tag: noindex');

		$this->response->setOutput($output);
	}

	private function normaliseActionUrl($url) {
		$url = trim((string)$url);

		if ($url === '') {
			return '';
		}

		if (preg_match('#^(https?://|mailto:|tel:|/|\\#)#i', $url)) {
			return $url;
		}

		if (strpos($url, ':') === false) {
			return $url;
		}

		return '';
	}
}
