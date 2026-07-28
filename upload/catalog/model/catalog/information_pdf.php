<?php
class ModelCatalogInformationPdf extends Model {
    public function getPdfs($information_id) {
        $sql = "SELECT title, filename, mask,thumb,category
                FROM " . DB_PREFIX . "information_pdf
                WHERE information_id = '" . (int)$information_id . "'
                ORDER BY sort_order, information_pdf_id";
        return $this->db->query($sql)->rows;
    }
}
