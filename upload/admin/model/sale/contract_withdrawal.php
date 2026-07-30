<?php
class ModelSaleContractWithdrawal extends Model {
	public function getWithdrawal($withdrawal_id) {
		$query = $this->db->query("SELECT cw.*, CONCAT(u.firstname, ' ', u.lastname) AS handled_by_name FROM `" . DB_PREFIX . "contract_withdrawal` cw LEFT JOIN `" . DB_PREFIX . "user` u ON (u.user_id = cw.handled_by) WHERE cw.contract_withdrawal_id = '" . (int)$withdrawal_id . "'");

		return $query->row;
	}

	public function getWithdrawals($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "contract_withdrawal`";
		$sql .= $this->filterSql($data);
		$sql .= " ORDER BY submitted_at DESC, contract_withdrawal_id DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? max(0, (int)$data['start']) : 0;
			$limit = isset($data['limit']) ? max(1, (int)$data['limit']) : 20;
			$sql .= " LIMIT " . $start . "," . $limit;
		}

		return $this->db->query($sql)->rows;
	}

	public function getTotalWithdrawals($data = array()) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "contract_withdrawal`" . $this->filterSql($data));

		return (int)$query->row['total'];
	}

	public function editWithdrawal($withdrawal_id, $data) {
		$completed_sql = $data['status'] === 'completed' ? 'completed_at = NOW(),' : 'completed_at = NULL,';
		$this->db->query("UPDATE `" . DB_PREFIX . "contract_withdrawal` SET
			status = '" . $this->db->escape($data['status']) . "',
			internal_note = '" . $this->db->escape($data['internal_note']) . "',
			handled_by = '" . (int)$data['handled_by'] . "',
			handled_at = NOW(),
			" . $completed_sql . "
			date_modified = NOW()
			WHERE contract_withdrawal_id = '" . (int)$withdrawal_id . "'");
	}

	private function filterSql($data) {
		$where = array();

		if (!empty($data['filter_reference'])) {
			$where[] = "reference LIKE '" . $this->db->escape($data['filter_reference']) . "%'";
		}
		if (!empty($data['filter_order_number'])) {
			$where[] = "order_number LIKE '" . $this->db->escape($data['filter_order_number']) . "%'";
		}
		if (!empty($data['filter_email'])) {
			$where[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		if (!empty($data['filter_status'])) {
			$where[] = "status = '" . $this->db->escape($data['filter_status']) . "'";
		}
		if (!empty($data['filter_date_added'])) {
			$where[] = "DATE(submitted_at) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		return $where ? ' WHERE ' . implode(' AND ', $where) : '';
	}
}
