<?php
class ModelExtensionModuleCategoryPriceBoost extends Model {
    public function increasePricesByCategories($category_ids, $percentage, $include_subcategories = false) {
        $product_ids = $this->getProductIdsByCategories($category_ids, $include_subcategories);

        if (!$product_ids) {
            return 0;
        }

        $product_ids_sql = implode(',', $product_ids);
        $multiplier = 1 + ((float)$percentage / 100);

        $this->db->query("UPDATE " . DB_PREFIX . "product SET price = ROUND(price * " . (float)$multiplier . ", 4), date_modified = NOW() WHERE product_id IN (" . $product_ids_sql . ")");

        return count($product_ids);
    }

    public function increasePricesAndRelatedOptionsByCategories($category_ids, $percentage, $include_subcategories = false) {
        $product_ids = $this->getProductIdsByCategories($category_ids, $include_subcategories);

        if (!$product_ids) {
            return array(
                'products' => 0,
                'relatedoptions' => 0
            );
        }

        $product_ids_sql = implode(',', $product_ids);
        $multiplier = 1 + ((float)$percentage / 100);

        $this->db->query("UPDATE " . DB_PREFIX . "product SET price = ROUND(price * " . (float)$multiplier . ", 4), date_modified = NOW() WHERE product_id IN (" . $product_ids_sql . ")");

        $related_count = 0;

        if ($this->hasRelatedOptionsTable()) {
            $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "relatedoptions WHERE product_id IN (" . $product_ids_sql . ")");
            $related_count = (int)$query->row['total'];

            if ($related_count > 0) {
                $this->db->query("UPDATE " . DB_PREFIX . "relatedoptions SET price = ROUND(price * " . (float)$multiplier . ", 4) WHERE product_id IN (" . $product_ids_sql . ")");
            }
        }

        return array(
            'products' => count($product_ids),
            'relatedoptions' => $related_count
        );
    }

    public function increaseRelatedOptionsOnlyByCategories($category_ids, $percentage, $include_subcategories = false) {
        $product_ids = $this->getProductIdsByCategories($category_ids, $include_subcategories);

        if (!$product_ids || !$this->hasRelatedOptionsTable()) {
            return 0;
        }

        $product_ids_sql = implode(',', $product_ids);
        $multiplier = 1 + ((float)$percentage / 100);

        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "relatedoptions WHERE product_id IN (" . $product_ids_sql . ")");
        $related_count = (int)$query->row['total'];

        if ($related_count > 0) {
            $this->db->query("UPDATE " . DB_PREFIX . "relatedoptions SET price = ROUND(price * " . (float)$multiplier . ", 4) WHERE product_id IN (" . $product_ids_sql . ")");
        }

        return $related_count;
    }

    public function getProductsCountByCategories($category_ids, $include_subcategories = false) {
        $product_ids = $this->getProductIdsByCategories($category_ids, $include_subcategories);

        return count($product_ids);
    }

    protected function getProductIdsByCategories($category_ids, $include_subcategories = false) {
        $category_ids = array_values(array_unique(array_map('intval', $category_ids)));

        if (!$category_ids) {
            return array();
        }

        if ($include_subcategories) {
            $category_ids = $this->expandCategoryIds($category_ids);
        }

        if (!$category_ids) {
            return array();
        }

        $category_ids_sql = implode(',', $category_ids);
        $query = $this->db->query("SELECT DISTINCT product_id FROM " . DB_PREFIX . "product_to_category WHERE category_id IN (" . $category_ids_sql . ")");

        if (!$query->num_rows) {
            return array();
        }

        $product_ids = array();

        foreach ($query->rows as $row) {
            $product_ids[] = (int)$row['product_id'];
        }

        return $product_ids;
    }

    protected function expandCategoryIds($category_ids) {
        $category_ids = array_values(array_unique(array_map('intval', $category_ids)));

        if (!$category_ids) {
            return array();
        }

        $category_ids_sql = implode(',', $category_ids);

        $query = $this->db->query("SELECT DISTINCT category_id FROM " . DB_PREFIX . "category_path WHERE path_id IN (" . $category_ids_sql . ")");

        if (!$query->num_rows) {
            return $category_ids;
        }

        $expanded = $category_ids;

        foreach ($query->rows as $row) {
            $expanded[] = (int)$row['category_id'];
        }

        return array_values(array_unique($expanded));
    }

    protected function hasRelatedOptionsTable() {
        $table = DB_PREFIX . 'relatedoptions';
        $query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape($table) . "'");

        return (bool)$query->num_rows;
    }
}
