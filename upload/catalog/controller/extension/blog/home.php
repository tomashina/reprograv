<?php 
class ControllerExtensionBlogHome extends Controller {
	private $error = array();
	
	public function index() { 
	 	
    	$this->language->load('blog/blog');
		
		$this->load->model('extension/blog/blog');	
		
		$this->load->model('tool/image');

		$limit = $this->config->get('blogsetting_blogs_per_page');
		$img_width = $this->config->get('blogsetting_thumbs_w');
		$img_height = $this->config->get('blogsetting_thumbs_h');
		$data['date_added_status'] = $this->config->get('blogsetting_date_added');
		$data['comments_count_status'] = $this->config->get('blogsetting_comments_count');
		$data['page_view_status'] = $this->config->get('blogsetting_page_view');
		$data['author_status'] = $this->config->get('blogsetting_author');
		$data['list_columns'] = $this->config->get('blogsetting_layout');
		
		$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home')
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_blog'),
			'href'      => $this->url->link('extension/blog/home')
      	);	
		
		if (isset($this->request->get['page'])) {
			$page = max(1, (int)$this->request->get['page']);
		} else {
			$page = 1;
		}
		
		$pagefix = ($page - 1) * $limit;
			
		if ($pagefix < 1) { $pagefix = 0;}
		
		if( isset($this->request->get['tag']) ){
				$filter_tag = $this->request->get['tag'];
		}else {
				$filter_tag = '';
		}
		
		$data['blogs'] = array();

		$filter_data = array(
			'start' => ($page - 1) * $limit,
			'limit' => $limit,
			'filter_tag'  => $filter_tag,
		);
		
		
		$blog_total = $this->model_extension_blog_blog->getTotalBlogs($filter_data);
		
		$results = $this->model_extension_blog_blog->getBlogs($filter_data, $pagefix, $limit);
		
 
    	foreach ($results as $result) {
		
			if ($result['tags']) {
				$tags = explode(',', $result['tags']);
			} else {
				$tags = false;
			}
			
			$m = date("m",strtotime($result['date_added']));
			$months = array (
					1 => $this->language->get('text_month_jan'),
					2 => $this->language->get('text_month_feb'),
					3 => $this->language->get('text_month_mar'),
					4 => $this->language->get('text_month_apr'),
					5 => $this->language->get('text_month_may'),
					6 => $this->language->get('text_month_jun'),
					7 => $this->language->get('text_month_jul'),
					8 => $this->language->get('text_month_aug'),
					9 => $this->language->get('text_month_sep'),
					10 => $this->language->get('text_month_oct'),
					11 => $this->language->get('text_month_nov'),
					12 => $this->language->get('text_month_dec')
					);
			$date_added_month = $months[(int)$m];
						
			$data['blogs'][] = array(
			'count_read' 		=> $result['count_read'],
			'comment_total' 	=> $this->model_extension_blog_blog->getTotalCommentsByBlogId($result['blog_id']),
			'blog_id' 			=> $result['blog_id'],
			'tags' 				=> $tags,
			'title'     		=> $result['title'],
			'short_description' => $this->cleanSeoText($result['short_description'], 280),
				'date_added_day' 	=> date("d",strtotime($result['date_added'])),
				'date_added_month' 	=> $date_added_month,
				'date_added_year' 	=> date("Y", strtotime($result['date_added'])),
				'date_added_iso' 	=> date(DATE_ATOM, strtotime($result['date_added'])),
				'author' 			=> $result['author'],
			'image'   			=> $this->model_tool_image->resize($result['image'], $img_width, $img_height),
			'href' 				=> $this->url->link('extension/blog/blog', 'blog_id=' . $result['blog_id'])
			);
		}
		
		// Home page title
		$blog_page_title = $this->config->get('blogsetting_home_page_title');
		if (!empty($blog_page_title[$this->config->get('config_language_id')])) {
		$seo_title = $this->cleanSeoText($blog_page_title[$this->config->get('config_language_id')], 65);
		} else {
		$seo_title = $this->cleanSeoText($this->language->get('text_blog') . ' | ' . $this->config->get('config_name'), 65);
		}
		$this->document->setTitle($seo_title);
		
		// Home title
		$blog_title = $this->config->get('blogsetting_home_title');
		if (!empty($blog_title[$this->config->get('config_language_id')])) {
		$data['heading_title'] = $blog_title[$this->config->get('config_language_id')];
		} else {
		$data['heading_title'] = $this->language->get('text_blog');
		}
        
		// Home description
		$blog_description = $this->config->get('blogsetting_home_description');
		if (empty($blog_description[$this->config->get('config_language_id')]) || ($blog_description[$this->config->get('config_language_id')] == '&lt;p&gt;&lt;br&gt;&lt;/p&gt;')) {
		$data['description'] = false;
		} else {
		$data['description'] = html_entity_decode(($blog_description[$this->config->get('config_language_id')]), ENT_QUOTES, 'UTF-8');
		}
		
		// If searched on a blog tag
		if($filter_tag){
		$data['heading_title'] = $this->language->get('text_filter_by') . $filter_tag;
		$this->document->setTitle($this->language->get('text_filter_by') . $filter_tag);
		$data['description'] = false;
		$this->document->setRobots('noindex,follow');
		} elseif ($page > 1) {
		$this->document->setRobots('noindex,follow');
		} else {
		$this->document->setRobots('index,follow');
		}
					
		$blog_page_meta_description = $this->config->get('blogsetting_home_meta_description');
		if (!empty($blog_page_meta_description[$this->config->get('config_language_id')])) {
		$meta_description = $this->cleanSeoText($blog_page_meta_description[$this->config->get('config_language_id')], 160);
		} else {
		$meta_description = 'Savjeti, novosti i vodiči za profesionalne pečatare i gravere iz Repro-Grav ponude.';
		}
		$this->document->setDescription($meta_description);
		
		$blog_page_meta_keyword = $this->config->get('blogsetting_home_meta_keyword');
		if (!empty($blog_page_meta_keyword[$this->config->get('config_language_id')])) {
		$this->document->setKeywords($blog_page_meta_keyword[$this->config->get('config_language_id')]);
		}

		$data['text_posted_on'] = $this->language->get('text_posted_on');
		$data['text_read'] = $this->language->get('text_read');
		$data['text_posted_by'] = $this->language->get('text_posted_by');
		$data['text_comments'] = $this->language->get('text_comments');
		$data['text_no_blog_posts'] = $this->language->get('text_no_blog_posts');
		$data['text_read_more'] = $this->language->get('text_read_more');
		
		$url = '';
		
		/*if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}*/
		
		if( isset($this->request->get['tag']) ){
			$url .= '&tag=' . $filter_tag;
		}
		
		$pagination = new Pagination();
		$pagination->total = $blog_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('blogsetting_blogs_per_page');
		if (empty($pagination->limit)) {$pagination->limit = 5;}
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/blog/home', $url . '&page={page}');
		
		$data['pagination'] = $pagination->render();
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($blog_total) ? ($pagefix) + 1 : 0, ((($page - 1) * $limit) > ($blog_total - $limit)) ? $blog_total : (($pagefix) + $limit), $blog_total, ceil($blog_total / $limit));

		$canonical = $this->url->link('extension/blog/home', $page > 1 ? 'page=' . $page : '');
		$this->document->addLink($canonical, 'canonical');

		if (
			$page === 1 &&
			!$filter_tag &&
			!isset($this->request->get['_route_']) &&
			isset($this->request->server['REQUEST_URI']) &&
			strpos($this->request->server['REQUEST_URI'], 'route=extension/blog/home') !== false
		) {
			$this->response->redirect($canonical, 301);
			return;
		}

		if (method_exists($this->document, 'setOpengraph')) {
			$this->document->setOpengraph('og:title', $seo_title);
			$this->document->setOpengraph('og:type', 'website');
			$this->document->setOpengraph('og:site_name', $this->config->get('config_name'));
			$this->document->setOpengraph('og:url', html_entity_decode($canonical, ENT_QUOTES, 'UTF-8'));
			$this->document->setOpengraph('og:description', $meta_description);
		}

		if (method_exists($this->document, 'setTwittercard')) {
			$this->document->setTwittercard('twitter:card', 'summary');
			$this->document->setTwittercard('twitter:title', $seo_title);
			$this->document->setTwittercard('twitter:description', $meta_description);
		}

		if (method_exists($this->document, 'setStructureddata')) {
			$blog_posts = array();
			foreach ($data['blogs'] as $blog) {
				$blog_posts[] = array(
					'@type' => 'BlogPosting',
					'@id'   => html_entity_decode($blog['href'], ENT_QUOTES, 'UTF-8'),
					'url'   => html_entity_decode($blog['href'], ENT_QUOTES, 'UTF-8'),
					'name'  => $this->cleanSeoText($blog['title'], 110)
				);
			}

			$blog_schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Blog',
				'@id'      => html_entity_decode($this->url->link('extension/blog/home'), ENT_QUOTES, 'UTF-8') . '#blog',
				'url'      => html_entity_decode($this->url->link('extension/blog/home'), ENT_QUOTES, 'UTF-8'),
				'name'     => $data['heading_title'],
				'description' => $meta_description,
				'publisher' => array('@id' => rtrim($this->config->get('config_ssl') ?: $this->config->get('config_url'), '/') . '/#organization')
			);
			if ($blog_posts) {
				$blog_schema['blogPost'] = $blog_posts;
			}

			$this->document->setStructureddata(
				'<script type="application/ld+json">' .
				json_encode($blog_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
				'</script>'
			);
		}
			
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
			$this->response->setOutput($this->load->view('blog/blog_home', $data));
		}

	private function cleanSeoText($value, $max_length) {
		$value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$value = trim(preg_replace('/\s+/u', ' ', $value));

		if (utf8_strlen($value) > $max_length) {
			$value = rtrim(utf8_substr($value, 0, $max_length - 1), " \t\n\r\0\x0B,.;:-") . '…';
		}

		return $value;
	}
}
