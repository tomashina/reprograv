<?php
class ControllerExtensionBaselDefaultMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $this->getCategoryChildren(
						$category['category_id'],
						(string)$category['category_id']
					),
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		return $this->load->view('common/menu', $data);
	}

	private function getCategoryChildren($parent_id, $path, $depth = 1) {
		$children_data = array();

		// Prevent malformed category data from creating an endless tree.
		if ($depth > 20) {
			return $children_data;
		}

		$children = $this->model_catalog_category->getCategories($parent_id);

		foreach ($children as $child) {
			$child_path = $path . '_' . $child['category_id'];

			if ($this->config->get('config_product_count')) {
				$total = ' (' . $this->model_catalog_product->getTotalProducts(array(
					'filter_category_id'  => $child['category_id'],
					'filter_sub_category' => true
				)) . ')';
			} else {
				$total = '';
			}

			$children_data[] = array(
				'name'     => $child['name'] . $total,
				'href'     => $this->url->link('product/category', 'path=' . $child_path),
				'children' => $this->getCategoryChildren(
					$child['category_id'],
					$child_path,
					$depth + 1
				)
			);
		}

		return $children_data;
	}
}
