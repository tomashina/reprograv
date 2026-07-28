<?php
class ControllerInformationDownload extends Controller {
    public function index() {
        if (empty($this->request->get['f'])) {
            return new Action('error/not_found');
        }

        $filename = basename($this->request->get['f']); // sanitize
        $file = DIR_STORAGE . 'download/' . $filename;

        if (!is_file($file)) {
            return new Action('error/not_found');
        }

        header('Content-Type: application/pdf');
        // inline (u browseru). Ako želiš download, stavi attachment i smislen mask:
        // header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}
