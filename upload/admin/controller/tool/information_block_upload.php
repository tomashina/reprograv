<?php
class ControllerToolInformationBlockUpload extends Controller {
	public function index() {
		$json = array();

		if (!$this->user->hasPermission('modify', 'catalog/information')) {
			$json['error'] = 'Nemate ovlasti za upload datoteka.';
		} elseif (empty($this->request->files['file'])) {
			$json['error'] = 'Datoteka nije odabrana.';
		} else {
			$file = $this->request->files['file'];
			$original_name = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));
			$extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
			$allowed_extensions = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'jpg', 'jpeg', 'png', 'webp');
			$maximum_size = 25 * 1024 * 1024;

			if (!empty($file['error'])) {
				$json['error'] = 'Upload nije uspio (kod ' . (int)$file['error'] . ').';
			} elseif (!in_array($extension, $allowed_extensions, true)) {
				$json['error'] = 'Dozvoljeni formati: PDF, Word, Excel, PowerPoint, ZIP, JPG, PNG i WEBP.';
			} elseif ((int)$file['size'] > $maximum_size) {
				$json['error'] = 'Datoteka smije imati najviše 25 MB.';
			} elseif (!is_uploaded_file($file['tmp_name'])) {
				$json['error'] = 'Upload nije valjan.';
			} else {
				$filename = bin2hex(random_bytes(16)) . '.' . $extension;
				$directory = rtrim(DIR_DOWNLOAD, '/\\') . DIRECTORY_SEPARATOR;

				if (!is_dir($directory)) {
					@mkdir($directory, 0775, true);
				}

				if (!move_uploaded_file($file['tmp_name'], $directory . $filename)) {
					$json['error'] = 'Datoteku nije moguće spremiti.';
				} else {
					$json['filename'] = $filename;
					$json['mask'] = $original_name;
					$json['success'] = 'Datoteka je učitana.';
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
