<?php
class ModelCatalogInformationBlock extends Model {
	private $table_exists;

	public function tableExists() {
		if ($this->table_exists === null) {
			$query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . "information_block") . "'");
			$this->table_exists = (bool)$query->num_rows;
		}

		return $this->table_exists;
	}

	public function getBlocks($information_id) {
		$blocks = array();

		if (!$this->tableExists()) {
			return $blocks;
		}

		$block_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_block` WHERE information_id = '" . (int)$information_id . "' ORDER BY sort_order, information_block_id");

		foreach ($block_query->rows as $block) {
			$block_id = (int)$block['information_block_id'];
			$descriptions = array();
			$actions = array();

			$description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_block_description` WHERE information_block_id = '" . $block_id . "'");

			foreach ($description_query->rows as $description) {
				$descriptions[(int)$description['language_id']] = array(
					'title'       => $description['title'],
					'description' => $description['description'],
					'image_alt'   => $description['image_alt']
				);
			}

			$action_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_block_action` WHERE information_block_id = '" . $block_id . "' ORDER BY sort_order, information_block_action_id");

			foreach ($action_query->rows as $action) {
				$action_id = (int)$action['information_block_action_id'];
				$action_descriptions = array();
				$action_description_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_block_action_description` WHERE information_block_action_id = '" . $action_id . "'");

				foreach ($action_description_query->rows as $action_description) {
					$action_descriptions[(int)$action_description['language_id']] = array(
						'label' => $action_description['label']
					);
				}

				$actions[] = array(
					'information_block_action_id' => $action_id,
					'type'                        => $action['type'],
					'url'                         => $action['url'],
					'filename'                    => $action['filename'],
					'mask'                        => $action['mask'],
					'new_window'                  => (int)$action['new_window'],
					'sort_order'                  => (int)$action['sort_order'],
					'description'                 => $action_descriptions
				);
			}

			$blocks[] = array(
				'information_block_id' => $block_id,
				'admin_title'          => $block['admin_title'],
				'image'                => $block['image'],
				'layout'               => $block['layout'],
				'status'               => (int)$block['status'],
				'sort_order'           => (int)$block['sort_order'],
				'description'          => $descriptions,
				'actions'              => $actions
			);
		}

		return $blocks;
	}

	public function setBlocks($information_id, $blocks) {
		if (!$this->tableExists()) {
			return;
		}

		$this->deleteBlocks($information_id);

		if (empty($blocks) || !is_array($blocks)) {
			return;
		}

		$block_sort_order = 0;

		foreach ($blocks as $block) {
			if (!is_array($block)) {
				continue;
			}

			$layout = isset($block['layout']) ? (string)$block['layout'] : 'image_right';
			$allowed_layouts = array('image_right', 'image_left', 'full');

			if (!in_array($layout, $allowed_layouts, true)) {
				$layout = 'image_right';
			}

			$image = isset($block['image']) ? trim((string)$block['image']) : '';
			$admin_title = isset($block['admin_title']) ? trim((string)$block['admin_title']) : '';
			$status = isset($block['status']) ? (int)$block['status'] : 0;

			$this->db->query("INSERT INTO `" . DB_PREFIX . "information_block` SET information_id = '" . (int)$information_id . "', admin_title = '" . $this->db->escape($admin_title) . "', image = '" . $this->db->escape($image) . "', layout = '" . $this->db->escape($layout) . "', status = '" . ($status ? 1 : 0) . "', sort_order = '" . (int)$block_sort_order . "', date_added = NOW(), date_modified = NOW()");

			$block_id = $this->db->getLastId();

			if (!empty($block['description']) && is_array($block['description'])) {
				foreach ($block['description'] as $language_id => $description) {
					if (!is_array($description)) {
						continue;
					}

					$title = isset($description['title']) ? trim((string)$description['title']) : '';
					$content = isset($description['description']) ? (string)$description['description'] : '';
					$image_alt = isset($description['image_alt']) ? trim((string)$description['image_alt']) : '';

					$this->db->query("INSERT INTO `" . DB_PREFIX . "information_block_description` SET information_block_id = '" . (int)$block_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($title) . "', description = '" . $this->db->escape($content) . "', image_alt = '" . $this->db->escape($image_alt) . "'");
				}
			}

			$action_sort_order = 0;

			if (!empty($block['actions']) && is_array($block['actions'])) {
				foreach ($block['actions'] as $action) {
					if (!is_array($action)) {
						continue;
					}

					$type = isset($action['type']) && $action['type'] === 'file' ? 'file' : 'link';
					$url = isset($action['url']) ? trim((string)$action['url']) : '';
					$filename = isset($action['filename']) ? basename((string)$action['filename']) : '';
					$mask = isset($action['mask']) ? basename((string)$action['mask']) : '';
					$new_window = isset($action['new_window']) ? (int)$action['new_window'] : 0;

					if ($url === '' && $filename === '') {
						continue;
					}

					$this->db->query("INSERT INTO `" . DB_PREFIX . "information_block_action` SET information_block_id = '" . (int)$block_id . "', type = '" . $type . "', url = '" . $this->db->escape($url) . "', filename = '" . $this->db->escape($filename) . "', mask = '" . $this->db->escape($mask) . "', new_window = '" . ($new_window ? 1 : 0) . "', sort_order = '" . (int)$action_sort_order . "'");

					$action_id = $this->db->getLastId();

					if (!empty($action['description']) && is_array($action['description'])) {
						foreach ($action['description'] as $language_id => $action_description) {
							if (!is_array($action_description)) {
								continue;
							}

							$label = isset($action_description['label']) ? trim((string)$action_description['label']) : '';

							$this->db->query("INSERT INTO `" . DB_PREFIX . "information_block_action_description` SET information_block_action_id = '" . (int)$action_id . "', language_id = '" . (int)$language_id . "', label = '" . $this->db->escape($label) . "'");
						}
					}

					$action_sort_order += 10;
				}
			}

			$block_sort_order += 10;
		}
	}

	public function deleteBlocks($information_id) {
		if (!$this->tableExists()) {
			return;
		}

		$block_query = $this->db->query("SELECT information_block_id FROM `" . DB_PREFIX . "information_block` WHERE information_id = '" . (int)$information_id . "'");

		foreach ($block_query->rows as $block) {
			$block_id = (int)$block['information_block_id'];
			$action_query = $this->db->query("SELECT information_block_action_id FROM `" . DB_PREFIX . "information_block_action` WHERE information_block_id = '" . $block_id . "'");

			foreach ($action_query->rows as $action) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "information_block_action_description` WHERE information_block_action_id = '" . (int)$action['information_block_action_id'] . "'");
			}

			$this->db->query("DELETE FROM `" . DB_PREFIX . "information_block_action` WHERE information_block_id = '" . $block_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "information_block_description` WHERE information_block_id = '" . $block_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_block` WHERE information_id = '" . (int)$information_id . "'");
	}
}
