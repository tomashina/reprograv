<?php
class ModelExtensionMpadditionalfieldMpadditionalfield extends Model {
	public function alterTables() {

		/*--
		-- Table structure for table `oc_mpadditional_field`
		--*/

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpadditional_field` (
		  `mpadditional_field_id` int(11) NOT NULL AUTO_INCREMENT,
		  `sort_order` int(11) NOT NULL,
		  `status` int(1) NOT NULL,
		  PRIMARY KEY (`mpadditional_field_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		/*--
		-- Table structure for table `oc_mpadditional_field_description`
		--*/

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpadditional_field_description` (
		  `mpadditional_field_id` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL,
		  `name` varchar(128) NOT NULL,
		  PRIMARY KEY (`mpadditional_field_id`,`language_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");


		/*--
		-- Table structure for table `oc_product_mpadditional_field`
		--*/

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_mpadditional_field` (
		  `product_mpadditional_field_id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) NOT NULL,
		  `mpadditional_field_id` int(11) NOT NULL,
		  `image` varchar(255) NOT NULL,
		  `height` int(11) NOT NULL,
		  `width` int(11) NOT NULL,
		  `status` int(1) NOT NULL,
		  `sort_order` int(11) NOT NULL,
		  PRIMARY KEY (`product_mpadditional_field_id`),
		  KEY `product_id` (`product_id`),
		  KEY `mpadditional_field_id` (`mpadditional_field_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		/*--
		-- Table structure for table `oc_product_mpadditional_field_value`
		--*/

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_mpadditional_field_value` (
		  `product_mpadditional_field_id` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL,
		  `product_id` int(11) NOT NULL,
		  `mpadditional_field_id` int(11) NOT NULL,
		  `value` longtext NOT NULL,
		  PRIMARY KEY (`product_mpadditional_field_id`,`language_id`),
		  KEY `product_id` (`product_id`),
		  KEY `mpadditional_field_id` (`mpadditional_field_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

	}
	
	public function addMpAdditionalField($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "mpadditional_field` SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "'");

		$mpadditional_field_id = $this->db->getLastId();

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mpadditional_field_description SET mpadditional_field_id = '" . (int)$mpadditional_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['maf_product'])) {
			foreach ($data['maf_product'] as $maf_product) {

				foreach ($maf_product['mpadditional_fields'] as $mpadditional_fields) {

					$mpadditional_fields['mpadditional_field_id'] = $mpadditional_field_id;

					$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field SET product_mpadditional_field_id = '" . (int)$mpadditional_fields['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$mpadditional_fields['mpadditional_field_id'] . "', product_id = '" . (int)$maf_product['product_id'] . "', status = '" . (int)$mpadditional_fields['status'] . "', sort_order = '" . (int)$mpadditional_fields['sort_order'] . "', image = '" . $this->db->escape($mpadditional_fields['image']) . "', width = '" . $this->db->escape($mpadditional_fields['width']) . "', height = '" . $this->db->escape($mpadditional_fields['height']) . "'");

					$mpadditional_fields['product_mpadditional_field_id'] = $this->db->getLastId();

					foreach ($mpadditional_fields['description'] as $language_id => $description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field_value SET product_mpadditional_field_id = '" . (int)$mpadditional_fields['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$mpadditional_fields['mpadditional_field_id'] . "', product_id = '" . (int)$maf_product['product_id'] . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape($description['value']) . "'");
					}
				}
			}
		}
		
		return $mpadditional_field_id;
	}

	public function editMpAdditionalField($mpadditional_field_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "mpadditional_field` SET sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "mpadditional_field_description WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mpadditional_field_description SET mpadditional_field_id = '" . (int)$mpadditional_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		if (isset($data['maf_product'])) {
			foreach ($data['maf_product'] as $maf_product) {

				$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field WHERE product_id = '" . (int)$maf_product['product_id'] . "' AND mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field_value WHERE product_id = '" . (int)$maf_product['product_id'] . "' AND mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");

				foreach ($maf_product['mpadditional_fields'] as $mpadditional_fields) {

					$mpadditional_fields['mpadditional_field_id'] = $mpadditional_field_id;

					$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field SET product_mpadditional_field_id = '" . (int)$mpadditional_fields['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$mpadditional_fields['mpadditional_field_id'] . "', product_id = '" . (int)$maf_product['product_id'] . "', status = '" . (int)$mpadditional_fields['status'] . "', sort_order = '" . (int)$mpadditional_fields['sort_order'] . "', image = '" . $this->db->escape($mpadditional_fields['image']) . "', width = '" . $this->db->escape($mpadditional_fields['width']) . "', height = '" . $this->db->escape($mpadditional_fields['height']) . "'");

					$mpadditional_fields['product_mpadditional_field_id'] = $this->db->getLastId();

					foreach ($mpadditional_fields['description'] as $language_id => $description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_mpadditional_field_value SET product_mpadditional_field_id = '" . (int)$mpadditional_fields['product_mpadditional_field_id'] . "', mpadditional_field_id = '" . (int)$mpadditional_fields['mpadditional_field_id'] . "', product_id = '" . (int)$maf_product['product_id'] . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape($description['value']) . "'");
					}
				}
			}
		}
	}

	public function deleteMpAdditionalField($mpadditional_field_id) {

		$this->db->query("DELETE FROM `" . DB_PREFIX . "mpadditional_field` WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mpadditional_field_description WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_mpadditional_field_value WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");
	}

	public function getMpAdditionalField($mpadditional_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mpadditional_field` af LEFT JOIN " . DB_PREFIX . "mpadditional_field_description afd ON (af.mpadditional_field_id = afd.mpadditional_field_id) WHERE af.mpadditional_field_id = '" . (int)$mpadditional_field_id . "' AND afd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getMpAdditionalFields($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "mpadditional_field` af LEFT JOIN " . DB_PREFIX . "mpadditional_field_description afd ON (af.mpadditional_field_id = afd.mpadditional_field_id) WHERE afd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND afd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'afd.name',
			'af.status',
			'af.sort_order',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY afd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getMpAdditionalFieldDescriptions($mpadditional_field_id) {
		$mpadditional_field_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpadditional_field_description WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");

		foreach ($query->rows as $result) {
			$mpadditional_field_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $mpadditional_field_data;
	}

	public function getTotalMpAdditionalFields($data = array()) {
		$sql = "SELECT  COUNT(*) AS total FROM `" . DB_PREFIX . "mpadditional_field` af LEFT JOIN " . DB_PREFIX . "mpadditional_field_description afd ON (af.mpadditional_field_id = afd.mpadditional_field_id) WHERE afd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND afd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}


		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	
	// Product Additional Work Starts
	public function getMpAdditionalFieldsInProduct($mpadditional_field_id, $product_ids = array()) {

		$where = '';
		if ($product_ids) {
			$where .= " AND po.product_id IN ('" . implode("','", $product_ids) . "')";
		}

		$query = $this->db->query("SELECT po.* FROM `" . DB_PREFIX . "product_mpadditional_field` po LEFT JOIN `" . DB_PREFIX . "mpadditional_field` ad ON (po.mpadditional_field_id = ad.mpadditional_field_id) LEFT JOIN `" . DB_PREFIX . "mpadditional_field_description` adf ON (ad.mpadditional_field_id = adf.mpadditional_field_id) WHERE po.mpadditional_field_id = '" . (int)$mpadditional_field_id . "' AND adf.language_id = '" . (int)$this->config->get('config_language_id') . "' {$where}");

		foreach ($query->rows as $key => $row) {
			$query->rows[$key]['description'] = $row['description'] = array();

			$query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_mpadditional_field_value WHERE product_mpadditional_field_id = '" . (int)$row['product_mpadditional_field_id'] . "'");

			foreach ($query1->rows as $row1) {
				$query->rows[$key]['description'][$row1['language_id']] = $row['description'][$row1['language_id']] = $row1;
			}
		}
	
		return $query->rows;
	}

	public function getMpAdditionalFieldsByProductId($product_id) {

		$query = $this->db->query("SELECT po.*, adf.name, ad.status as ad_status FROM `" . DB_PREFIX . "product_mpadditional_field` po LEFT JOIN `" . DB_PREFIX . "mpadditional_field` ad ON (po.mpadditional_field_id = ad.mpadditional_field_id) LEFT JOIN `" . DB_PREFIX . "mpadditional_field_description` adf ON (ad.mpadditional_field_id = adf.mpadditional_field_id) WHERE po.product_id = '" . (int)$product_id . "' AND adf.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $key => $row) {
			$query->rows[$key]['description'] = $row['description'] = array();

			$query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_mpadditional_field_value WHERE product_mpadditional_field_id = '" . (int)$row['product_mpadditional_field_id'] . "'");
			//  product_id = '" . (int)$product_id . "' AND

			foreach ($query1->rows as $row1) {
				$query->rows[$key]['description'][$row1['language_id']] = $row['description'][$row1['language_id']] = $row1;
			}

		}

		return $query->rows;
	}

	public function getTotalMpAdditionalFieldsByProductId($mpadditional_field_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_mpadditional_field WHERE mpadditional_field_id = '" . (int)$mpadditional_field_id . "'");

		return $query->row['total'];
	}

	// Product Additional Work Ends
}
