<?php
class ModelExtensionMpAdditionalFieldMpAdditionalField extends Model {

	// Additional Field Work Start
	public function getProductAdditionalFields($product_id) {
		$data = array();
		
		$this->load->model('tool/image');

		
		$query = $this->db->query("SELECT pad.*, afd.name, padv.value FROM `" . DB_PREFIX . "product_mpadditional_field` pad LEFT JOIN `" . DB_PREFIX . "mpadditional_field` af ON (pad.mpadditional_field_id = af.mpadditional_field_id) LEFT JOIN `" . DB_PREFIX . "mpadditional_field_description` afd ON (af.mpadditional_field_id = afd.mpadditional_field_id) LEFT JOIN `" . DB_PREFIX . "product_mpadditional_field_value` padv ON (padv.product_mpadditional_field_id = pad.product_mpadditional_field_id) WHERE pad.product_id = '" . (int)$product_id . "' AND afd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND padv.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pad.status=1 AND af.status=1  ORDER BY pad.sort_order ASC, af.sort_order ASC;");/*, af.mpadditional_field_id ASC*/

		foreach ($query->rows as $row) {

			$width = 74;
			if ((int)$row['width']) {
				$width = (int)$row['width'];
			} elseif ((int)$this->config->get('module_mpadditional_field_imgage_width')) {
				$width = (int)$this->config->get('module_mpadditional_field_imgage_width');
			}


			$height = 74;
			if ((int)$row['height']) {
				$height = (int)$row['height'];
			} elseif ((int)$this->config->get('module_mpadditional_field_imgage_height')) {
				$height = (int)$this->config->get('module_mpadditional_field_imgage_height');
			}

			$image = '';
			if (!empty($row['image']) && file_exists(DIR_IMAGE . $row['image'])) {
				$image = $this->model_tool_image->resize($row['image'], $width, $height);
			}

			$data[] = array(
				'name' => $row['name'],
				'value' => $row['value'],
				'image' => $image,
				'width' => $width,
				'height' => $height,
			);

		}

		return $data;
	}
	// Additional Field Work Ends
}