<?php
class ModelExtensionModuleCategorySpecialBulk extends Model {

    public function getProductIdsByCategories($category_ids = array(), $include_sub = true) {
        if (!$category_ids) return array();
        $category_ids = array_map('intval', $category_ids);
        $catList = implode(',', $category_ids);

        if ($include_sub) {
            $sql = "SELECT DISTINCT p.product_id
                    FROM " . DB_PREFIX . "product p
                    JOIN " . DB_PREFIX . "product_to_category pc ON p.product_id = pc.product_id
                    JOIN " . DB_PREFIX . "category_path cp ON pc.category_id = cp.category_id
                    WHERE cp.path_id IN ($catList)";
        } else {
            $sql = "SELECT DISTINCT p.product_id
                    FROM " . DB_PREFIX . "product p
                    JOIN " . DB_PREFIX . "product_to_category pc ON p.product_id = pc.product_id
                    WHERE pc.category_id IN ($catList)";
        }
        $res = $this->db->query($sql);
        $ids = array();
        foreach ($res->rows as $row) { $ids[] = (int)$row['product_id']; }
        return $ids;
    }

    public function applyProductSpecials($product_ids, $customer_group_id, $priority, $type, $amount, $date_start, $date_end, $overwrite=false, $round_rule='none') {
        if (!$product_ids) return array('affected'=>0);
        $in = implode(',', array_map('intval', $product_ids));

        if ($overwrite) {
            $this->db->query("DELETE ps FROM " . DB_PREFIX . "product_special ps WHERE ps.customer_group_id = " . (int)$customer_group_id . " AND ps.product_id IN ($in)");
        }

        $products = $this->db->query("SELECT product_id, price FROM " . DB_PREFIX . "product WHERE product_id IN ($in)")->rows;
        $affected = 0;
        foreach ($products as $p) {
            $base = (float)$p['price'];
            if ($base <= 0) continue;

            $new = ($type == 'percent') ? $base * (1 - ($amount/100.0)) : max(0, $base - $amount);
            if ($round_rule == '99')      $new = floor($new) + 0.99;
            elseif ($round_rule == '95')  $new = floor($new) + 0.95;

            $this->db->query("INSERT INTO " . DB_PREFIX . "product_special 
                SET product_id=".(int)$p['product_id'].",
                    customer_group_id=".(int)$customer_group_id.",
                    priority=".(int)$priority.",
                    price='".(float)$new."',
                    date_start='".$this->db->escape($date_start)."',
                    date_end='".$this->db->escape($date_end)."'");
            $affected++;
        }
        return array('affected'=>$affected);
    }

    public function applyROSpecials($product_ids, $customer_group_id, $priority, $type, $amount, $date_start, $date_end, $overwrite=false, $round_rule='none') {
        if (!$product_ids) return array('affected'=>0);
        $in = implode(',', array_map('intval', $product_ids));

        $ro_exists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "relatedoptions'");
        if (!$ro_exists->num_rows) {
            return array('affected'=>0);
        }

        $ros = $this->db->query("SELECT ro.relatedoptions_id, ro.product_id, ro.price
                                 FROM " . DB_PREFIX . "relatedoptions ro
                                 WHERE ro.product_id IN ($in)")->rows;

        if ($overwrite && $ros) {
            $ids = implode(',', array_map('intval', array_column($ros,'relatedoptions_id')));
            if ($ids) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "relatedoptions_special 
                                  WHERE relatedoptions_id IN ($ids) AND customer_group_id=".(int)$customer_group_id);
            }
        }

        $has_dates = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "relatedoptions_special LIKE 'date_start'")->num_rows > 0;

        $affected = 0;
        foreach ($ros as $r) {
            $base = (float)$r['price'];
            if ($base <= 0) {
                $baseRow = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id=".(int)$r['product_id']." LIMIT 1")->row;
                $base = isset($baseRow['price']) ? (float)$baseRow['price'] : 0.0;
            }
            if ($base <= 0) continue;

            $new = ($type == 'percent') ? $base * (1 - ($amount/100.0)) : max(0, $base - $amount);
            if ($round_rule == '99')      $new = floor($new) + 0.99;
            elseif ($round_rule == '95')  $new = floor($new) + 0.95;

            $sql = "INSERT INTO " . DB_PREFIX . "relatedoptions_special 
                SET relatedoptions_id=".(int)$r['relatedoptions_id'].",
                    customer_group_id=".(int)$customer_group_id.",
                    priority=".(int)$priority.",
                    price='".(float)$new."'";
            if ($has_dates) {
                $sql .= ", date_start='".$this->db->escape($date_start)."', date_end='".$this->db->escape($date_end)."'";
            }
            $this->db->query($sql);
            $affected++;
        }
        return array('affected'=>$affected);
    }

    // Count / Remove
    public function countProductSpecials($product_ids, $customer_group_id) {
        if (!$product_ids) return 0;
        $in = implode(',', array_map('intval', $product_ids));
        $q = $this->db->query("SELECT COUNT(*) AS c FROM " . DB_PREFIX . "product_special WHERE customer_group_id=".(int)$customer_group_id." AND product_id IN ($in)");
        return (int)$q->row['c'];
    }
    public function removeProductSpecials($product_ids, $customer_group_id) {
        if (!$product_ids) return 0;
        $in = implode(',', array_map('intval', $product_ids));
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE customer_group_id=".(int)$customer_group_id." AND product_id IN ($in)");
        return $this->db->countAffected();
    }
    public function countROSpecials($product_ids, $customer_group_id) {
        $ro_exists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "relatedoptions'");
        if (!$ro_exists->num_rows) return 0;
        if (!$product_ids) return 0;
        $in = implode(',', array_map('intval', $product_ids));
        $q = $this->db->query("SELECT COUNT(*) AS c FROM " . DB_PREFIX . "relatedoptions_special ros
                               JOIN " . DB_PREFIX . "relatedoptions ro ON ro.relatedoptions_id = ros.relatedoptions_id
                               WHERE ros.customer_group_id=".(int)$customer_group_id." AND ro.product_id IN ($in)");
        return (int)$q->row['c'];
    }
    public function removeROSpecials($product_ids, $customer_group_id) {
        $ro_exists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "relatedoptions'");
        if (!$ro_exists->num_rows) return 0;
        if (!$product_ids) return 0;
        $in = implode(',', array_map('intval', $product_ids));
        $ids = $this->db->query("SELECT ros.relatedoptions_id FROM " . DB_PREFIX . "relatedoptions_special ros
                                 JOIN " . DB_PREFIX . "relatedoptions ro ON ro.relatedoptions_id = ros.relatedoptions_id
                                 WHERE ros.customer_group_id=".(int)$customer_group_id." AND ro.product_id IN ($in)");
        if (!$ids->num_rows) return 0;
        $idlist = implode(',', array_map('intval', array_column($ids->rows,'relatedoptions_id')));
        $this->db->query("DELETE FROM " . DB_PREFIX . "relatedoptions_special WHERE relatedoptions_id IN ($idlist) AND customer_group_id=".(int)$customer_group_id);
        return $this->db->countAffected();
    }

    // REPORT
    public function buildSpecialsReport($category_ids = array(), $include_sub = true, $customer_group_id = 1) {
        $report = array();
        if (!$category_ids) return $report;

        $cats = $this->db->query("SELECT category_id, name FROM " . DB_PREFIX . "category_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'")->rows;
        $cat_names = array();
        foreach ($cats as $c) { $cat_names[(int)$c['category_id']] = $c['name']; }

        $pids = $this->getProductIdsByCategories($category_ids, $include_sub);
        if (!$pids) return $report;
        $in = implode(',', array_map('intval', $pids));

        $prows = $this->db->query("SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p
                                   JOIN " . DB_PREFIX . "product_description pd ON pd.product_id=p.product_id AND pd.language_id=".(int)$this->config->get('config_language_id')."
                                   WHERE p.product_id IN ($in)")->rows;
        $pname = array();
        foreach ($prows as $pr) { $pname[(int)$pr['product_id']] = $pr['name']; }

        $pc = $this->db->query("SELECT product_id, category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id IN ($in)")->rows;
        $prod_cats = array();
        foreach ($pc as $row) {
            $pid = (int)$row['product_id']; $cid = (int)$row['category_id'];
            if (!isset($prod_cats[$pid])) $prod_cats[$pid] = array();
            $prod_cats[$pid][] = $cid;
        }

        // Product specials with IDs
        $ps = $this->db->query("SELECT product_special_id, product_id, price, date_start, date_end FROM " . DB_PREFIX . "product_special WHERE customer_group_id=".(int)$customer_group_id." AND product_id IN ($in)")->rows;

        // RO specials with relatedoptions_id
        $ros = array();
        $ro_exists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "relatedoptions_special'");
        if ($ro_exists->num_rows) {
            $ros = $this->db->query("SELECT ro.product_id, ros.relatedoptions_id, ros.price, 
                                            IFNULL(ros.date_start,'') as date_start, IFNULL(ros.date_end,'') as date_end
                                     FROM " . DB_PREFIX . "relatedoptions_special ros
                                     JOIN " . DB_PREFIX . "relatedoptions ro ON ro.relatedoptions_id = ros.relatedoptions_id
                                     WHERE ros.customer_group_id=".(int)$customer_group_id." AND ro.product_id IN ($in)")->rows;
        }

        foreach ($pids as $pid) {
            $cats_for_p = isset($prod_cats[$pid]) ? $prod_cats[$pid] : array();
            foreach ($cats_for_p as $cid) {
                if (!isset($report[$cid])) {
                    $report[$cid] = array('name'=> isset($cat_names[$cid])?$cat_names[$cid]:'#'.$cid, 'products'=>array(), 'product_specials'=>0, 'ro_specials'=>0);
                }
                if (!isset($report[$cid]['products'][$pid])) {
                    $report[$cid]['products'][$pid] = array(
                        'name' => isset($pname[$pid])?$pname[$pid]:'#'.$pid,
                        'product_specials' => array(),
                        'ro_specials' => array()
                    );
                }
            }
        }

        foreach ($ps as $row) {
            $pid = (int)$row['product_id'];
            $cats_for_p = isset($prod_cats[$pid]) ? $prod_cats[$pid] : array();
            foreach ($cats_for_p as $cid) {
                if (!isset($report[$cid])) continue;
                $report[$cid]['products'][$pid]['product_specials'][] = array(
                    'product_special_id' => (int)$row['product_special_id'],
                    'price' => $row['price'],
                    'date_start' => $row['date_start'],
                    'date_end' => $row['date_end']
                );
                $report[$cid]['product_specials']++;
            }
        }

        foreach ($ros as $row) {
            $pid = (int)$row['product_id'];
            $cats_for_p = isset($prod_cats[$pid]) ? $prod_cats[$pid] : array();
            foreach ($cats_for_p as $cid) {
                if (!isset($report[$cid])) continue;
                $report[$cid]['products'][$pid]['ro_specials'][] = array(
                    'relatedoptions_id' => (int)$row['relatedoptions_id'],
                    'price' => $row['price'],
                    'date_start' => $row['date_start'],
                    'date_end' => $row['date_end']
                );
                $report[$cid]['ro_specials']++;
            }
        }

        uasort($report, function($a,$b){ return strnatcasecmp($a['name'],$b['name']); });
        foreach ($report as &$cat) {
            uasort($cat['products'], function($a,$b){ return strnatcasecmp($a['name'], $b['name']); });
        }
        return $report;
    }

    public function reportTotals($report) {
        $tot = array('product'=>0,'ro'=>0,'products'=>0);
        foreach ($report as $cid => $c) {
            $tot['product'] += (int)$c['product_specials'];
            $tot['ro'] += (int)$c['ro_specials'];
            $tot['products'] += count($c['products']);
        }
        return $tot;
    }

    // Inline delete helpers
    public function deleteProductSpecialById($product_special_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_special_id=".(int)$product_special_id." LIMIT 1");
        return $this->db->countAffected();
    }
    public function deleteROSpecialByRelatedOptionsId($relatedoptions_id, $customer_group_id) {
        // Delete all ROS rows for this relatedoptions_id and CG
        $this->db->query("DELETE FROM " . DB_PREFIX . "relatedoptions_special WHERE relatedoptions_id=".(int)$relatedoptions_id." AND customer_group_id=".(int)$customer_group_id);
        return $this->db->countAffected();
    }
}
