<?php
class ModelInformationContractWithdrawal extends Model {
	public function addWithdrawal($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "contract_withdrawal` SET
			reference = '" . $this->db->escape($data['reference']) . "',
			submission_key = '" . $this->db->escape($data['submission_key']) . "',
			customer_id = '" . (int)$data['customer_id'] . "',
			order_id = '" . (int)$data['order_id'] . "',
			order_number = '" . $this->db->escape($data['order_number']) . "',
			full_name = '" . $this->db->escape($data['full_name']) . "',
			email = '" . $this->db->escape($data['email']) . "',
			phone = '" . $this->db->escape($data['phone']) . "',
			address_line = '" . $this->db->escape($data['address_line']) . "',
			postal_code = '" . $this->db->escape($data['postal_code']) . "',
			city = '" . $this->db->escape($data['city']) . "',
			country_code = '" . $this->db->escape($data['country_code']) . "',
			contract_date = " . ($data['contract_date'] ? "'" . $this->db->escape($data['contract_date']) . "'" : "NULL") . ",
			received_date = " . ($data['received_date'] ? "'" . $this->db->escape($data['received_date']) . "'" : "NULL") . ",
			items = '" . $this->db->escape($data['items']) . "',
			note = '" . $this->db->escape($data['note']) . "',
			declaration = '" . $this->db->escape($data['declaration']) . "',
			request_snapshot = '" . $this->db->escape($data['request_snapshot']) . "',
			snapshot_hash = '" . $this->db->escape($data['snapshot_hash']) . "',
			status = 'received',
			language_id = '" . (int)$data['language_id'] . "',
			submitted_at = '" . $this->db->escape($data['submitted_at']) . "',
			ip_address = '" . $this->db->escape($data['ip_address']) . "',
			user_agent = '" . $this->db->escape($data['user_agent']) . "',
			date_added = NOW(),
			date_modified = NOW()");

		return $this->db->getLastId();
	}

	public function getWithdrawal($withdrawal_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "contract_withdrawal` WHERE contract_withdrawal_id = '" . (int)$withdrawal_id . "'");

		return $query->row;
	}

	public function getBySubmissionKey($submission_key) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "contract_withdrawal` WHERE submission_key = '" . $this->db->escape($submission_key) . "' LIMIT 1");

		return $query->row;
	}

	public function referenceExists($reference) {
		$query = $this->db->query("SELECT contract_withdrawal_id FROM `" . DB_PREFIX . "contract_withdrawal` WHERE reference = '" . $this->db->escape($reference) . "' LIMIT 1");

		return (bool)$query->num_rows;
	}

	public function resolveOrderId($order_number, $email, $customer_id) {
		$order_number = trim((string)$order_number);
		$conditions = array();

		if (ctype_digit($order_number)) {
			$conditions[] = "order_id = '" . (int)$order_number . "'";
		}

		$conditions[] = "invoice_no = '" . $this->db->escape($order_number) . "'";

		$sql = "SELECT order_id FROM `" . DB_PREFIX . "order` WHERE (" . implode(' OR ', $conditions) . ") AND (LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'";
		if ($customer_id) {
			$sql .= " OR customer_id = '" . (int)$customer_id . "'";
		}
		$sql .= ") ORDER BY order_id DESC LIMIT 1";

		$query = $this->db->query($sql);

		return $query->num_rows ? (int)$query->row['order_id'] : 0;
	}

	public function markConsumerNotified($withdrawal_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "contract_withdrawal` SET consumer_notified_at = NOW(), date_modified = NOW() WHERE contract_withdrawal_id = '" . (int)$withdrawal_id . "'");
	}

	public function markAdminNotified($withdrawal_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "contract_withdrawal` SET admin_notified_at = NOW(), date_modified = NOW() WHERE contract_withdrawal_id = '" . (int)$withdrawal_id . "'");
	}

	public function setNotificationError($withdrawal_id, $error) {
		$this->db->query("UPDATE `" . DB_PREFIX . "contract_withdrawal` SET notification_error = '" . $this->db->escape(utf8_substr($error, 0, 5000)) . "', date_modified = NOW() WHERE contract_withdrawal_id = '" . (int)$withdrawal_id . "'");
	}
}
