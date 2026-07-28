<?php
class ModelCatalogInformationPdf extends Model {
    public function getPdfs($information_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "information_pdf
                WHERE information_id = '" . (int)$information_id . "'
                ORDER BY sort_order, information_pdf_id";
        return $this->db->query($sql)->rows;
    }

    public function setPdfs($information_id, $pdfs) {
    $this->db->query("DELETE FROM " . DB_PREFIX . "information_pdf WHERE information_id = '" . (int)$information_id . "'");

    if (!empty($pdfs)) {
        foreach ($pdfs as $pdf) {
            $title    = $this->db->escape((string)($pdf['title'] ?? ''));
            // NEW: category (trim + limit 191)
            $category = isset($pdf['category']) ? trim((string)$pdf['category']) : '';
            if (utf8_strlen($category) > 191) {
                $category = utf8_substr($category, 0, 191);
            }
            $category = $this->db->escape($category);

            $filename   = $this->db->escape((string)($pdf['filename'] ?? ''));
            $mask       = $this->db->escape((string)($pdf['mask'] ?? ''));
            $thumb      = $this->db->escape((string)($pdf['thumb'] ?? ''));
            $sort_order = (int)($pdf['sort_order'] ?? 0);

            $this->db->query("INSERT INTO " . DB_PREFIX . "information_pdf SET
                information_id = '" . (int)$information_id . "',
                title = '" . $title . "',
                category = '" . $category . "',
                filename = '" . $filename . "',
                mask = '" . $mask . "',
                thumb = '" . $thumb . "',
                sort_order = '" . $sort_order . "'");
        }
    }
}

}
