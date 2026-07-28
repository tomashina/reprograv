<?php
class ControllerExtensionModuleCategorySpecialBulk extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/category_special_bulk');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/category_special_bulk');
        $this->load->model('catalog/category');
        $this->load->model('customer/customer_group');

        $data['report'] = array();
        $data['report_totals'] = array('product'=>0,'ro'=>0,'products'=>0);

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $data_post = $this->request->post;

            $category_ids = isset($data_post['categories']) ? array_map('intval', $data_post['categories']) : array();
            $include_sub = !empty($data_post['include_sub']);
            $product_ids = $this->model_extension_module_category_special_bulk->getProductIdsByCategories($category_ids, $include_sub);

            $customer_group_id = isset($data_post['customer_group_id']) ? (int)$data_post['customer_group_id'] : 1;
            $priority = isset($data_post['priority']) ? (int)$data_post['priority'] : 0;
            $type = isset($data_post['discount_type']) ? $data_post['discount_type'] : 'percent';
            $amount = isset($data_post['amount']) ? (float)$data_post['amount'] : 0.0;
            $date_start = isset($data_post['date_start']) ? $data_post['date_start'] : '';
            $date_end = isset($data_post['date_end']) ? $data_post['date_end'] : '';
            $overwrite = !empty($data_post['overwrite']);
            $round_rule = isset($data_post['round_rule']) ? $data_post['round_rule'] : 'none';
            $dry_run = !empty($data_post['dry_run']);

            $apply_product = !empty($data_post['apply_product_specials']);
            $apply_ro = !empty($data_post['apply_ro_specials']);

            // Inline deletes
            if (isset($data_post['action']) && $data_post['action'] == 'delete_ps' && !empty($data_post['ps_id'])) {
                $deleted = $this->model_extension_module_category_special_bulk->deleteProductSpecialById((int)$data_post['ps_id']);
                $this->session->data['success'] = sprintf($this->language->get('text_deleted_one_product_special'), (int)$deleted);
                // fallthrough to refresh report if categories selected
                if ($category_ids) {
                    $data['report'] = $this->model_extension_module_category_special_bulk->buildSpecialsReport($category_ids, $include_sub, $customer_group_id);
                    $data['report_totals'] = $this->model_extension_module_category_special_bulk->reportTotals($data['report']);
                }
            } elseif (isset($data_post['action']) && $data_post['action'] == 'delete_ros' && !empty($data_post['ro_relatedoptions_id'])) {
                $rid = (int)$data_post['ro_relatedoptions_id'];
                $deleted = $this->model_extension_module_category_special_bulk->deleteROSpecialByRelatedOptionsId($rid, $customer_group_id);
                $this->session->data['success'] = sprintf($this->language->get('text_deleted_one_ro_special'), (int)$deleted);
                if ($category_ids) {
                    $data['report'] = $this->model_extension_module_category_special_bulk->buildSpecialsReport($category_ids, $include_sub, $customer_group_id);
                    $data['report_totals'] = $this->model_extension_module_category_special_bulk->reportTotals($data['report']);
                }
            } elseif (isset($data_post['action']) && $data_post['action'] == 'report') {
                $data['report'] = $this->model_extension_module_category_special_bulk->buildSpecialsReport($category_ids, $include_sub, $customer_group_id);
                $data['report_totals'] = $this->model_extension_module_category_special_bulk->reportTotals($data['report']);
            } elseif (isset($data_post['action']) && $data_post['action'] == 'remove') {
                $msgs = array();
                if ($dry_run) {
                    $cnt = $this->model_extension_module_category_special_bulk->countProductSpecials($product_ids, $customer_group_id);
                    $cnt2 = $this->model_extension_module_category_special_bulk->countROSpecials($product_ids, $customer_group_id);
                    $this->session->data['success'] = sprintf($this->language->get('text_remove_preview'), (int)$cnt, (int)$cnt2);
                } else {
                    if ($apply_product) {
                        $removed = $this->model_extension_module_category_special_bulk->removeProductSpecials($product_ids, $customer_group_id);
                        $msgs[] = sprintf($this->language->get('text_removed_product_specials'), (int)$removed);
                    }
                    if ($apply_ro) {
                        $removed2 = $this->model_extension_module_category_special_bulk->removeROSpecials($product_ids, $customer_group_id);
                        $msgs[] = sprintf($this->language->get('text_removed_ro_specials'), (int)$removed2);
                    }
                    $this->session->data['success'] = implode(' | ', $msgs);
                    $this->response->redirect($this->url->link('extension/module/category_special_bulk', 'user_token=' . $this->session->data['user_token'], true));
                    return;
                }
            } elseif (isset($data_post['action']) && $data_post['action'] == 'apply') {
                $msgs = array();
                if ($dry_run) {
                    $this->session->data['success'] = sprintf($this->language->get('text_preview_products'), count($product_ids));
                } else {
                    if ($apply_product) {
                        $res = $this->model_extension_module_category_special_bulk->applyProductSpecials($product_ids, $customer_group_id, $priority, $type, $amount, $date_start, $date_end, $overwrite, $round_rule);
                        $msgs[] = sprintf($this->language->get('text_applied_product_specials'), (int)$res['affected']);
                    }
                    if ($apply_ro) {
                        $res2 = $this->model_extension_module_category_special_bulk->applyROSpecials($product_ids, $customer_group_id, $priority, $type, $amount, $date_start, $date_end, $overwrite, $round_rule);
                        $msgs[] = sprintf($this->language->get('text_applied_ro_specials'), (int)$res2['affected']);
                    }
                    $this->session->data['success'] = implode(' | ', $msgs);
                    $this->response->redirect($this->url->link('extension/module/category_special_bulk', 'user_token=' . $this->session->data['user_token'], true));
                    return;
                }
            }
        }

        // Texts
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_categories'] = $this->language->get('entry_categories');
        $data['entry_include_sub'] = $this->language->get('entry_include_sub');
        $data['entry_discount_type'] = $this->language->get('entry_discount_type');
        $data['entry_amount'] = $this->language->get('entry_amount');
        $data['entry_customer_group'] = $this->language->get('entry_customer_group');
        $data['entry_priority'] = $this->language->get('entry_priority');
        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['entry_date_end'] = $this->language->get('entry_date_end');
        $data['entry_apply_to'] = $this->language->get('entry_apply_to');
        $data['entry_apply_product'] = $this->language->get('entry_apply_product');
        $data['entry_apply_ro'] = $this->language->get('entry_apply_ro');
        $data['entry_overwrite'] = $this->language->get('entry_overwrite');
        $data['entry_round_rule'] = $this->language->get('entry_round_rule');
        $data['entry_dry_run'] = $this->language->get('entry_dry_run');
        $data['entry_report'] = $this->language->get('entry_report');

        $data['help_round_rule'] = $this->language->get('help_round_rule');
        $data['help_dry_run'] = $this->language->get('help_dry_run');

        $data['button_apply'] = $this->language->get('button_apply');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_report'] = $this->language->get('button_report');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/category_special_bulk', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/category_special_bulk', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        // Categories
        $data['categories'] = $this->getCategoryTreeAlpha(0);

        // Customer groups
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/category_special_bulk', $data));
    }

    private function getCategoryTreeAlpha($root_parent_id = 0) {
        $this->load->model('catalog/category');
        $sep = html_entity_decode('&nbsp;&nbsp;&gt;&nbsp;&nbsp;', ENT_QUOTES, 'UTF-8');

        $out = array();
        $stack = array(array('id' => (int)$root_parent_id, 'prefix' => ''));
        $seen = array();

        while (!empty($stack)) {
            $node = array_pop($stack);
            $parent_id = (int)$node['id'];
            $prefix = $node['prefix'];

            $children = $this->model_catalog_category->getCategories($parent_id);
            usort($children, function($a, $b){
                return strnatcasecmp($a['name'], $b['name']);
            });

            for ($i = count($children) - 1; $i >= 0; $i--) {
                $child = $children[$i];
                $cid = (int)$child['category_id'];
                if (isset($seen[$cid])) continue;
                $seen[$cid] = true;

                $out[] = array(
                    'category_id' => $cid,
                    'name'        => $prefix . $child['name']
                );
                $stack[] = array('id' => $cid, 'prefix' => $prefix . $child['name'] . $sep);
            }
        }
        return $out;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/category_special_bulk')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
