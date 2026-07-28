<?php
class ControllerExtensionMpAdditionalFieldMpAdditionalField extends Controller {

	use mpadditional_field\trait_mpadditional_field_catalog;

	public function __construct($registry) {
		parent :: __construct($registry);
		$this->igniteTraitMpAdditionalFieldCatalog($registry);

	}

	protected function getTheme() {
		// If the default theme is selected we need to know which directory its pointing to
		if ($this->config->get('config_theme') == 'default') {
			$directory = $this->config->get('theme_default_directory');
		} else {
			$directory = $this->config->get('config_theme');
		}
		return $directory;
	}

	// 'trigger' => 'catalog/controller/common/header/before',
	public function commonHeaderBefore(&$route, &$data) {
		if ($this->config->get('module_mpadditional_field_status')) {

			if ($this->config->get('module_mpadditional_field_product_listing') || (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product') ) {

				//$this->document->addStyle('catalog/view/theme/default/stylesheet/mpadditional_field.css');

			}
		}
	}

	// 'trigger' => 'catalog/view/product/product/after',
	public function product(&$route, &$data, &$output) {

		if ($this->config->get('module_mpadditional_field_status')) {

			$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');
			$data['mpadditional_fields'] = $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getProductAdditionalFields($this->request->get['product_id']);

			if ($data['mpadditional_fields']) {

				$data['mpadditional_fields_current_theme'] = $current_theme = $this->getTheme();

				$html = $this->load->view($this->extension_path . 'mpadditional_field/product', $data);

				// $find = '<div id="product">';
				$find = '<!--tio-->';
				if ($current_theme == 'journal3') {
					$find = '<div class="product-stats">';
				}

				$output = str_replace($find, $find . " \n " . $html, $output);
			}
		}
	}

	// 'trigger' => 'catalog/view/product/special/before',
	// 'trigger' => 'catalog/view/product/search/before',
	// 'trigger' => 'catalog/view/product/product/before',
	// 'trigger' => 'catalog/view/product/manufacturer_info/before',
	// 'trigger' => 'catalog/view/product/category/before',
	// 'trigger' => 'catalog/view/extension/module/special/before',
	// 'trigger' => 'catalog/view/extension/module/latest/before',
	// 'trigger' => 'catalog/view/extension/module/bestseller/before',
	// 'trigger' => 'catalog/view/extension/module/featured/before',
	public function extModules(&$route, &$data, &$code) {

		$data['mpadditional_field_status'] = $this->config->get('module_mpadditional_field_status');
		$data['mpadditional_field_product_listing'] = $this->config->get('module_mpadditional_field_product_listing');

		if ($this->config->get('module_mpadditional_field_status') && $this->config->get('module_mpadditional_field_product_listing')) {

			$this->load->model($this->extension_path . 'mpadditional_field/mpadditional_field');


			$product_data['mpadditional_field_status'] =  $this->config->get('module_mpadditional_field_status');
			$product_data['mpadditional_field_product_listing'] = $this->config->get('module_mpadditional_field_product_listing');


			foreach ($data['products'] as $key => $value) {

				$value['mpadditional_fields'] = $data['products'][ $key ]['mpadditional_fields'] = $this->config->get('module_mpadditional_field_status') && $this->config->get('module_mpadditional_field_product_listing') ? $this->{'model_' . $this->extension_model . 'mpadditional_field_mpadditional_field'}->getProductAdditionalFields($value['product_id']) : array();

				$product_data['product'] = $value;
				$value['mpadditional_fields_thumb'] = $data['products'][ $key ]['mpadditional_fields_thumb'] = $this->load->view($this->extension_path . 'mpadditional_field/product_thumb', $product_data);
			}
		}

		// make sure the keys exists in products array
		foreach ($data['products'] as $key => $value) {

			if (!isset($value['mpadditional_fields'])) {
				$data['products'][ $key ]['mpadditional_fields'] = array();
			}
			if (!isset($value['mpadditional_fields_thumb'])) {
				$data['products'][ $key ]['mpadditional_fields_thumb'] = '';
			}
		}

	}
}