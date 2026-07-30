<?php
class ModelExtensionModuleHbSeoSnippets extends Model {	
	public function get_stock_status_id($product_id) {
		$query = $this->db->query("SELECT stock_status_id FROM ".DB_PREFIX."product WHERE product_id = '".(int)$product_id."'");
		if ($query->row) {
			return $query->row['stock_status_id'];
		}else {
			return '0';
		}
	}
	
	public function product_sd($product_info, $data) {
		$this->load->model('catalog/product');

		$ldjson = '';
		$product_id = (int)$product_info['product_id'];
		$name = $this->cleanText($product_info['name']);
		$model = $this->cleanText($product_info['model']);
		$brand_name = $this->cleanText($product_info['manufacturer']);
		$url = html_entity_decode($this->url->link('product/product', 'product_id=' . $product_id), ENT_QUOTES, 'UTF-8');

		$description_source = $product_info['meta_description'];
		if ($this->config->get('hb_snippets_description') == 'description' || !$this->cleanText($description_source)) {
			$description_source = isset($data['description']) ? $data['description'] : $product_info['description'];
		}
		$description = $this->cleanText($description_source);

		if ($this->config->get('hb_snippets_prod_enable')) {
			$product_images = array();

			if (!empty($product_info['image'])) {
				$product_images[] = $this->absoluteUrl('image/' . ltrim($product_info['image'], '/'));
			}

			foreach ($this->model_catalog_product->getProductImages($product_id) as $image) {
				if (!empty($image['image'])) {
					$product_images[] = $this->absoluteUrl('image/' . ltrim($image['image'], '/'));
				}
			}

			$product_images = array_values(array_unique(array_filter($product_images)));

			$product_snippet = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Product',
				'@id'         => $url . '#product',
				'url'         => $url,
				'name'        => $name,
				'description' => $description
			);

			if ($product_images) {
				$product_snippet['image'] = $product_images;
			}

			$sku = $this->cleanText($product_info['sku']);
			if ($sku) {
				$product_snippet['sku'] = $sku;
			}

			$mpn = $this->cleanText($product_info['mpn']);
			if ($mpn) {
				$product_snippet['mpn'] = $mpn;
			}

			if ($brand_name) {
				$product_snippet['brand'] = array(
					'@type' => 'Brand',
					'name'  => $brand_name
				);
			}

			$category = $this->getProductCategoryName($product_id);
			if ($category) {
				$product_snippet['category'] = $category;
			}

			$additional_properties = $this->getProductProperties($product_id);
			if ($additional_properties) {
				$product_snippet['additionalProperty'] = $additional_properties;
			}

			$review_data = array();
			$review_query = $this->db->query("SELECT author, text, rating, date_added FROM `" . DB_PREFIX . "review` WHERE product_id = '" . $product_id . "' AND status = 1 ORDER BY date_added DESC");

			foreach ($review_query->rows as $review) {
				$review_body = $this->cleanText($review['text']);
				$author = $this->cleanText($review['author']);

				if (!$review_body || !$author) {
					continue;
				}

				$headline = function_exists('utf8_substr') ? utf8_substr($review_body, 0, 65) : substr($review_body, 0, 65);
				$review_data[] = array(
					'@type'         => 'Review',
					'headline'      => $headline,
					'reviewBody'    => $review_body,
					'datePublished' => date('Y-m-d', strtotime($review['date_added'])),
					'author'        => array(
						'@type' => 'Person',
						'name'  => $author
					),
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => (int)$review['rating'],
						'bestRating'  => 5,
						'worstRating' => 1
					)
				);
			}

			if ($review_data) {
				$product_snippet['review'] = $review_data;
			}

			$review_count = (int)$product_info['reviews'];
			$rating = isset($data['rating']) ? (float)$data['rating'] : (float)$product_info['rating'];
			if ($review_count > 0 && $rating > 0) {
				$product_snippet['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => $rating,
					'reviewCount' => $review_count,
					'bestRating'  => 5,
					'worstRating' => 1
				);
			}

			// Prices and availability are intentionally absent. They are private
			// catalogue data and must never be exposed to anonymous crawlers.
			$ldjson = '<script type="application/ld+json">' .
				json_encode($product_snippet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
				'</script>';
		}

		if ($this->config->get('hb_snippets_og_enable')) {
			$og_title = $this->buildSocialTitle($this->config->get('hb_snippets_ogp'), $name, $model, $brand_name);

			if (strlen($this->config->get('hb_snippets_og_id')) > 5) {
				$this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
			}

			$this->document->setOpengraph('og:title', $og_title);
			$this->document->setOpengraph('og:type', 'product');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			$this->document->setOpengraph('og:url', $url);
			$this->document->setOpengraph('og:description', $description);

			$this->load->model('tool/image');
			if (!empty($product_info['image'])) {
				$snippet_thumb = $this->model_tool_image->resize(
					$product_info['image'],
					$this->config->get('hb_snippets_og_piw'),
					$this->config->get('hb_snippets_og_pih')
				);
				$this->document->setOpengraph('og:image', $this->absoluteUrl($snippet_thumb));
				$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_piw'));
				$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_pih'));
			}
		}

		if ($this->config->get('hb_snippets_tc_enable')) {
			$twitter_title = $this->buildSocialTitle($this->config->get('hb_snippets_tcp'), $name, $model, $brand_name);

			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $twitter_title);
			$this->document->setTwittercard('twitter:description', $description);

			if (!empty($product_info['image'])) {
				$this->document->setTwittercard('twitter:image', $this->absoluteUrl('image/' . ltrim($product_info['image'], '/')));
			}
		}

		$this->document->setStructureddata($ldjson);
	}

	private function cleanText($value) {
		$value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$value = preg_replace('/\s+/u', ' ', $value);

		return trim($value);
	}

	private function absoluteUrl($url) {
		$url = html_entity_decode((string)$url, ENT_QUOTES, 'UTF-8');

		if (preg_match('#^https?://#i', $url)) {
			return $url;
		}

		$base = $this->config->get('config_ssl') ?: $this->config->get('config_url');
		if (!$base && defined('HTTPS_SERVER')) {
			$base = HTTPS_SERVER;
		}

		return rtrim($base, '/') . '/' . ltrim($url, '/');
	}

	private function buildSocialTitle($template, $name, $model, $brand) {
		if (strlen((string)$template) <= 4) {
			return $name;
		}

		$title = str_replace(
			array('{name}', '{model}', '{brand}', '{price}'),
			array($name, $model, $brand, ''),
			$template
		);

		return $this->cleanText($title);
	}

	private function getProductCategoryName($product_id) {
		$query = $this->db->query(
			"SELECT cd.name FROM `" . DB_PREFIX . "product_to_category` p2c " .
			"INNER JOIN `" . DB_PREFIX . "category` c ON (c.category_id = p2c.category_id AND c.status = 1) " .
			"INNER JOIN `" . DB_PREFIX . "category_description` cd ON (cd.category_id = c.category_id) " .
			"WHERE p2c.product_id = '" . (int)$product_id . "' " .
			"AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' " .
			"ORDER BY (SELECT COUNT(*) FROM `" . DB_PREFIX . "category_path` cp WHERE cp.category_id = c.category_id) DESC, c.sort_order ASC LIMIT 1"
		);

		return $query->row ? $this->cleanText($query->row['name']) : '';
	}

	private function getProductProperties($product_id) {
		$properties = array();

		foreach ($this->model_catalog_product->getProductAttributes((int)$product_id) as $group) {
			if (empty($group['attribute'])) {
				continue;
			}

			foreach ($group['attribute'] as $attribute) {
				$name = isset($attribute['name']) ? $this->cleanText($attribute['name']) : '';
				$value = isset($attribute['text']) ? $this->cleanText($attribute['text']) : '';

				if ($name && $value) {
					$properties[] = array(
						'@type' => 'PropertyValue',
						'name'  => $name,
						'value' => $value
					);
				}
			}
		}

		return $properties;
	}
	
	public function category_social($category_info){
		$this->load->model('tool/image');
		if ($this->config->get('hb_snippets_og_enable')){
			$hb_snippets_ogc = $this->config->get('hb_snippets_ogc');
			if (strlen($hb_snippets_ogc) > 4){
				$ogc_name = $category_info['name'];
				$hb_snippets_ogc = str_replace('{name}',$ogc_name,$hb_snippets_ogc);
			}else{
				$hb_snippets_ogc = $category_info['name'];
			}
			
			if (strlen($this->config->get('hb_snippets_og_id')) > 5 ){
			    $this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
			}
			$this->document->setOpengraph('og:title', $hb_snippets_ogc);
            $this->document->setOpengraph('og:type', 'product.group');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			$this->document->setOpengraph('og:url', $this->url->link('product/category', 'path=' . $category_info['category_id']));
			if ($category_info['image']) {
				$image = $this->model_tool_image->resize($category_info['image'], $this->config->get('hb_snippets_og_ciw'), $this->config->get('hb_snippets_og_cih'));
				$this->document->setOpengraph('og:image', $image);
				$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_ciw'));
				$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_cih'));
			}
			$this->document->setOpengraph('og:description', $category_info['meta_description']);
		}
		
		//TWITTER CARDS
		if ($this->config->get('hb_snippets_tc_enable')){
			$hb_snippets_tcc = $this->config->get('hb_snippets_tcc');
			if (strlen($hb_snippets_tcc) > 4){
				$tcc_name = $category_info['name'];
				$hb_snippets_tcc = str_replace('{name}',$tcc_name,$hb_snippets_tcc);
			}else{
				$hb_snippets_tcc = $category_info['name'];
			}
			
			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $hb_snippets_tcc);
			$this->document->setTwittercard('twitter:description', $category_info['meta_description']);
			if ($category_info['image']) {
				$image = $this->model_tool_image->resize($category_info['image'], $this->config->get('hb_snippets_og_ciw'), $this->config->get('hb_snippets_og_cih'));
			    $this->document->setTwittercard('twitter:image', $image);
			}
		}
	}
	
	public function information_social($information_info) {
		if ($this->config->get('hb_snippets_og_enable')) {
			$config_url = $this->config->get('config_url');
			$og_img = $this->config->get('hb_snippets_og_img');
			$fb_app_id = $this->config->get('hb_snippets_og_id');
	
			// Open Graph
			if (!empty($fb_app_id) && strlen($fb_app_id) > 5) {
				$this->document->setOpengraph('fb:app_id', $fb_app_id);
			}
	
			$this->document->setOpengraph('og:title', $information_info['title']);
			$this->document->setOpengraph('og:type', 'website');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			$this->document->setOpengraph('og:url', $this->url->link('information/information', 'information_id=' . $information_info['information_id']));
			$this->document->setOpengraph('og:description', $information_info['meta_description']);
	
			if ($og_img) {
				$this->document->setOpengraph('og:image', $config_url . 'image/' . $og_img);
				$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_diw'));
				$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_dih'));
			}
		}
	
		// Twitter Cards
		if ($this->config->get('hb_snippets_tc_enable')) {
			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $information_info['title']);
			$this->document->setTwittercard('twitter:description', $information_info['meta_description']);
	
			if ($og_img) {
				$this->document->setTwittercard('twitter:image', $config_url . 'image/' . $og_img);
			}
		}
	}	
	
	public function home_social() {
		//$this->load->model('tool/image');

		// Open Graph
		if ($this->config->get('hb_snippets_og_enable')) {
			$config_url = $this->config->get('config_url');
			$og_img = $this->config->get('hb_snippets_og_img');

			if (strlen($this->config->get('hb_snippets_og_id')) > 5) {
				$this->document->setOpengraph('fb:app_id', $this->config->get('hb_snippets_og_id'));
			}

			$this->document->setOpengraph('og:title', $this->config->get('config_meta_title'));
			$this->document->setOpengraph('og:type', 'website');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			$this->document->setOpengraph('og:url', $config_url);
			$this->document->setOpengraph('og:description', $this->config->get('config_meta_description'));

			if ($og_img) {
				$this->document->setOpengraph('og:image', $config_url . 'image/' . $og_img);
				$this->document->setOpengraph('og:image:width', $this->config->get('hb_snippets_og_diw'));
				$this->document->setOpengraph('og:image:height', $this->config->get('hb_snippets_og_dih'));
			}
		}

		// Twitter Cards
		if ($this->config->get('hb_snippets_tc_enable')) {
			$this->document->setTwittercard('twitter:card', 'summary_large_image');
			$this->document->setTwittercard('twitter:site', $this->config->get('hb_snippets_tc_username'));
			$this->document->setTwittercard('twitter:title', $this->config->get('config_meta_title'));
			$this->document->setTwittercard('twitter:description', $this->config->get('config_meta_description'));

			if ($og_img) {
				$this->document->setTwittercard('twitter:image', $config_url . 'image/' . $og_img);
			}
		}
	}
	
	public function getProductCategory(int $product_id): array{
		$query = $this->db->query("SELECT c.category_id, c.parent_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category c ON (p2c.category_id = c.category_id) WHERE product_id = '" . (int)$product_id . "' ORDER BY parent_id DESC LIMIT 1");
		if ($query->row){
			return $query->row;
		}else{
			return [];
		}
	}

	public function getParentCategory(int $category_id): int{
		$query = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "' LIMIT 1");
		if ($query->row){
			return $query->row['parent_id'];
		}else{
			return '0';
		}
	}

	public function isCategoryActive(int $category_id): bool{
		$query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "' AND status = 1");
		if ($query->row['total'] > 0){
			return true;
		}else{
			return false;
		}
	 }

	public function breadcrumbs_sd($breadcrumbs, $options = []) {		
		if ($this->config->get('hb_snippets_bc_enable')) {
			$ldjson = '';
			$itemlist = [];
			$i = 1;

			if ($this->config->get('hb_snippets_bc_type') == 'smart' && !empty($options)) {
				$type   = $options['type'];
				$id 	= $options['id'];
				$title 	= $options['title'];

				$breadcrumbs = array(
					array(
						'text' => $this->language->get('text_home'),
						'href' => $this->url->link('common/home')
					)
				);

				if ($type == 'product' && $id > 0) {
					$category_id = $this->getPrimaryProductCategoryId($id);

					foreach ($this->getCategoryTrail($category_id) as $category) {
						$breadcrumbs[] = $category;
					}

					$breadcrumbs[] = array(
						'text' => $title,
						'href' => $this->url->link('product/product', 'product_id=' . $id)
					);
				}

				if ($type == 'category' && $id > 0) {
					foreach ($this->getCategoryTrail($id) as $category) {
						$breadcrumbs[] = $category;
					}
				}
			}			
			
			if (!empty($breadcrumbs)) {
				foreach ($breadcrumbs as $breadcrumb) {	
					$itemlist[] = array(
						'@type'			=> 	'ListItem',
						'position'		=>  $i,
						'name'			=>  $this->cleanText($breadcrumb['text']),
						'item'			=>  html_entity_decode($breadcrumb['href'], ENT_QUOTES, 'UTF-8')
					);

					$i++;
				}
			}					

			$breadcrumb_snippet = array(
				'@context' 			=> 	'https://schema.org/',
				'@type'				=> 	'BreadcrumbList',
				'itemListElement'   =>	$itemlist
			);
			
			$ldjson .= '<!--huntbee breadcrumb structured data--><script type="application/ld+json">';
			$ldjson .= json_encode($breadcrumb_snippet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$ldjson .= "</script>";

		} else {
			$ldjson = '';
		}
		
		$this->document->setStructureddata($ldjson);
	}

	private function getPrimaryProductCategoryId($product_id) {
		$query = $this->db->query(
			"SELECT p2c.category_id FROM `" . DB_PREFIX . "product_to_category` p2c " .
			"INNER JOIN `" . DB_PREFIX . "category` c ON (c.category_id = p2c.category_id AND c.status = 1) " .
			"WHERE p2c.product_id = '" . (int)$product_id . "' " .
			"ORDER BY (SELECT COUNT(*) FROM `" . DB_PREFIX . "category_path` cp WHERE cp.category_id = c.category_id) DESC, c.sort_order ASC LIMIT 1"
		);

		return $query->row ? (int)$query->row['category_id'] : 0;
	}

	private function getCategoryTrail($category_id) {
		if (!$category_id) {
			return array();
		}

		$query = $this->db->query(
			"SELECT cp.path_id, cd.name FROM `" . DB_PREFIX . "category_path` cp " .
			"INNER JOIN `" . DB_PREFIX . "category` c ON (c.category_id = cp.path_id AND c.status = 1) " .
			"INNER JOIN `" . DB_PREFIX . "category_description` cd ON (cd.category_id = cp.path_id) " .
			"WHERE cp.category_id = '" . (int)$category_id . "' " .
			"AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' " .
			"ORDER BY cp.level ASC"
		);

		$trail = array();
		$path = array();

		foreach ($query->rows as $category) {
			$path[] = (int)$category['path_id'];
			$trail[] = array(
				'text' => $category['name'],
				'href' => $this->url->link('product/category', 'path=' . implode('_', $path))
			);
		}

		return $trail;
	}
	
	public function local_business() {
		// Organization/Store data is emitted once by knowledge_graph().
		// Keeping the legacy free-form block disabled avoids duplicate entities
		// and invalid hard-coded URLs from the old module configuration.
		$this->document->setStructureddata('');
	}

	public function knowledge_graph() {
		if (!$this->config->get('hb_snippets_kg_enable')) {
			$this->document->setStructureddata('');
			return;
		}
	
		$store_url = $this->config->get('config_ssl') ?: $this->config->get('config_url');
		if (!$store_url && defined('HTTPS_SERVER')) {
			$store_url = HTTPS_SERVER;
		}
		$store_url = rtrim($store_url, '/') . '/';

		$home_snippet = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Store',
			'@id'         => $store_url . '#organization',
			'name'        => $this->cleanText($this->config->get('config_name')),
			'url'         => $store_url,
			'description' => $this->cleanText($this->config->get('config_meta_description'))
		);

		$logo = $this->config->get('config_logo');
		if ($logo) {
			$home_snippet['logo'] = $this->absoluteUrl('image/' . ltrim($logo, '/'));
		}

		$telephone = $this->cleanText($this->config->get('config_telephone'));
		if ($telephone) {
			$home_snippet['telephone'] = $telephone;
		}

		$email = $this->cleanText($this->config->get('config_email'));
		if ($email) {
			$home_snippet['email'] = $email;
		}

		$address = array_filter(array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $this->cleanText($this->config->get('hb_snippets_local_st')),
			'addressLocality' => $this->cleanText($this->config->get('hb_snippets_local_location')),
			'addressRegion'   => $this->cleanText($this->config->get('hb_snippets_local_state')),
			'postalCode'      => $this->cleanText($this->config->get('hb_snippets_local_postal')),
			'addressCountry'  => $this->cleanText($this->config->get('hb_snippets_local_country'))
		));
		if (count($address) > 1) {
			$home_snippet['address'] = $address;
		}

		$same_as = array_values(array_filter((array)$this->config->get('hb_snippets_socials'), function ($url) {
			return filter_var($url, FILTER_VALIDATE_URL);
		}));
		if ($same_as) {
			$home_snippet['sameAs'] = $same_as;
		}

		$vat_id = $this->cleanText($this->config->get('hb_snippets_vat'));
		if ($vat_id) {
			$home_snippet['vatID'] = $vat_id;
		}
	
		$ldjson = '<!-- Repro-Grav organization structured data --><script type="application/ld+json">' .
			json_encode($home_snippet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
			'</script>';
	
		$this->document->setStructureddata($ldjson);
	}
	
	public function site_search() {
		if (!$this->config->get('hb_snippets_search_enable')) {
			$this->document->setStructureddata('');
			return;
		}
	
		$store_url = $this->config->get('config_ssl') ?: $this->config->get('config_url');
		if (!$store_url && defined('HTTPS_SERVER')) {
			$store_url = HTTPS_SERVER;
		}
		$store_url = rtrim($store_url, '/') . '/';
		$search_link = html_entity_decode($this->url->link('product/search', 'search='), ENT_QUOTES, 'UTF-8');
	
		$snippet = [
			'@context'        => 'https://schema.org/',
			'@type'           => 'WebSite',
			'@id'             => $store_url . '#website',
			'url'             => $store_url,
			'name'            => $this->cleanText($this->config->get('config_name')),
			'publisher'       => array('@id' => $store_url . '#organization'),
			'potentialAction' => [
				'@type'       => 'SearchAction',
				'target'      => $search_link . '{search_term_string}',
				'query-input' => 'required name=search_term_string'
			]
		];
	
		$ldjson = '<!--huntbee sitelinks search box structured data--><script type="application/ld+json">';
		$ldjson .= json_encode($snippet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$ldjson .= '</script>';
	
		$this->document->setStructureddata($ldjson);
	}
	
	public function itemlist($products) {
		if (!$this->config->get('hb_snippets_list_enable') || empty($products)) {
			$this->document->setStructureddata('');
			return;
		}
	
		$itemlist = array_map(function ($product, $index) {
			return [
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $product['name'],
				'image'    => $product['thumb'],
				'url'      => $product['href']
			];
		}, $products, array_keys($products));
	
		$itemlist_snippet = [
			'@context'         => 'https://schema.org/',
			'@type'            => 'ItemList',
			'itemListElement'  => $itemlist
		];
	
		$ldjson = '<!--huntbee category structured data--><script type="application/ld+json">';
		$ldjson .= json_encode($itemlist_snippet);
		$ldjson .= '</script>';
	
		$this->document->setStructureddata($ldjson);
	}	
	
}
