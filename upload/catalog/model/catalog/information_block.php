<?php
require_once(DIR_SYSTEM . 'library/information_block_html.php');

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

		$language_id = (int)$this->config->get('config_language_id');
		$block_query = $this->db->query("SELECT ib.*, ibd.title, ibd.description, ibd.image_alt FROM `" . DB_PREFIX . "information_block` ib LEFT JOIN `" . DB_PREFIX . "information_block_description` ibd ON (ib.information_block_id = ibd.information_block_id AND ibd.language_id = '" . $language_id . "') WHERE ib.information_id = '" . (int)$information_id . "' AND ib.status = '1' ORDER BY ib.sort_order, ib.information_block_id");

		foreach ($block_query->rows as $block) {
			$actions = array();
			$action_query = $this->db->query("SELECT iba.*, ibad.label FROM `" . DB_PREFIX . "information_block_action` iba LEFT JOIN `" . DB_PREFIX . "information_block_action_description` ibad ON (iba.information_block_action_id = ibad.information_block_action_id AND ibad.language_id = '" . $language_id . "') WHERE iba.information_block_id = '" . (int)$block['information_block_id'] . "' ORDER BY iba.sort_order, iba.information_block_action_id");

			foreach ($action_query->rows as $action) {
				$actions[] = array(
					'type'       => $action['type'],
					'url'        => $action['url'],
					'filename'   => $action['filename'],
					'mask'       => $action['mask'],
					'new_window' => (int)$action['new_window'],
					'label'      => $action['label']
				);
			}

			$blocks[] = array(
				'information_block_id' => (int)$block['information_block_id'],
				'image'                => $block['image'],
				'layout'               => $block['layout'],
				'title'                => $block['title'],
				'description'          => InformationBlockHtml::decode($block['description']),
				'image_alt'            => $block['image_alt'],
				'actions'              => $actions
			);
		}

		return $blocks;
	}
}
