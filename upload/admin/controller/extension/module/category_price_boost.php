<?php
class ControllerExtensionModuleCategoryPriceBoost extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/category_price_boost');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('catalog/category');
        $this->load->model('extension/module/category_price_boost');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $action = isset($this->request->post['form_action']) ? $this->request->post['form_action'] : 'save';

            if ($action === 'apply') {
                $category_ids = array_map('intval', $this->request->post['category_ids']);
                $percentage = (float)$this->request->post['percentage'];
                $include_subcategories = !empty($this->request->post['include_subcategories']);

                $affected = $this->model_extension_module_category_price_boost->increasePricesByCategories($category_ids, $percentage, $include_subcategories);

                $this->session->data['success'] = sprintf($this->language->get('text_success_apply'), $affected);
            } elseif ($action === 'apply_relatedoptions_only') {
                $category_ids = array_map('intval', $this->request->post['category_ids']);
                $percentage = (float)$this->request->post['percentage'];
                $include_subcategories = !empty($this->request->post['include_subcategories']);

                $affected = $this->model_extension_module_category_price_boost->increaseRelatedOptionsOnlyByCategories($category_ids, $percentage, $include_subcategories);

                $this->session->data['success'] = sprintf($this->language->get('text_success_apply_relatedoptions_only'), $affected);
            } elseif ($action === 'apply_relatedoptions') {
                $category_ids = array_map('intval', $this->request->post['category_ids']);
                $percentage = (float)$this->request->post['percentage'];
                $include_subcategories = !empty($this->request->post['include_subcategories']);

                $affected = $this->model_extension_module_category_price_boost->increasePricesAndRelatedOptionsByCategories($category_ids, $percentage, $include_subcategories);

                $this->session->data['success'] = sprintf($this->language->get('text_success_apply_relatedoptions'), $affected['products'], $affected['relatedoptions']);
            } elseif ($action === 'preview') {
                $category_ids = array_map('intval', $this->request->post['category_ids']);
                $percentage = (float)$this->request->post['percentage'];
                $include_subcategories = !empty($this->request->post['include_subcategories']);

                $affected = $this->model_extension_module_category_price_boost->getProductsCountByCategories($category_ids, $include_subcategories);

                $this->session->data['success'] = sprintf($this->language->get('text_success_preview'), $affected, $percentage);
            } else {
                $this->model_setting_setting->editSetting('module_category_price_boost', $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success_save');
            }

            $this->response->redirect($this->url->link('extension/module/category_price_boost', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_categories'] = $this->language->get('entry_categories');
        $data['entry_include_subcategories'] = $this->language->get('entry_include_subcategories');
        $data['entry_percentage'] = $this->language->get('entry_percentage');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_apply'] = $this->language->get('button_apply');
        $data['button_preview'] = $this->language->get('button_preview');
        $data['button_apply_relatedoptions'] = $this->language->get('button_apply_relatedoptions');
        $data['button_apply_relatedoptions_only'] = $this->language->get('button_apply_relatedoptions_only');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['category_ids'])) {
            $data['error_category_ids'] = $this->error['category_ids'];
        } else {
            $data['error_category_ids'] = '';
        }

        if (isset($this->error['percentage'])) {
            $data['error_percentage'] = $this->error['percentage'];
        } else {
            $data['error_percentage'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

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
            'href' => $this->url->link('extension/module/category_price_boost', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/category_price_boost', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_category_price_boost_status'])) {
            $data['module_category_price_boost_status'] = $this->request->post['module_category_price_boost_status'];
        } else {
            $data['module_category_price_boost_status'] = $this->config->get('module_category_price_boost_status');
        }

        if (isset($this->request->post['category_ids'])) {
            $data['category_ids'] = array_map('intval', $this->request->post['category_ids']);
        } else {
            $data['category_ids'] = array();
        }

        if (isset($this->request->post['include_subcategories'])) {
            $data['include_subcategories'] = 1;
        } else {
            $data['include_subcategories'] = 0;
        }

        if (isset($this->request->post['percentage'])) {
            $data['percentage'] = $this->request->post['percentage'];
        } else {
            $data['percentage'] = '';
        }

        $data['categories'] = $this->model_catalog_category->getCategories();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/category_price_boost', $data));
    }

    public function install() {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/category_price_boost');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/category_price_boost');
    }

    public function uninstall() {
        // No custom tables to remove.
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/category_price_boost')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $action = isset($this->request->post['form_action']) ? $this->request->post['form_action'] : 'save';

        if ($action === 'apply' || $action === 'preview' || $action === 'apply_relatedoptions' || $action === 'apply_relatedoptions_only') {
            if (empty($this->request->post['category_ids']) || !is_array($this->request->post['category_ids'])) {
                $this->error['category_ids'] = $this->language->get('error_categories');
            }

            if (!isset($this->request->post['percentage']) || !is_numeric($this->request->post['percentage'])) {
                $this->error['percentage'] = $this->language->get('error_percentage_numeric');
            } else {
                $percentage = (float)$this->request->post['percentage'];

                if ($percentage <= 0) {
                    $this->error['percentage'] = $this->language->get('error_percentage_positive');
                }
            }
        }

        return !$this->error;
    }

}
