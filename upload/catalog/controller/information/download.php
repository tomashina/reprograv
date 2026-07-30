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

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $inline_extensions = array('pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp');
        $mime_types = array(
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip'  => 'application/zip',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp'
        );
        $download_name = !empty($this->request->get['m']) ? basename(html_entity_decode($this->request->get['m'], ENT_QUOTES, 'UTF-8')) : $filename;
        $download_name = str_replace(array("\r", "\n", '"'), '', $download_name);
        $disposition = in_array($extension, $inline_extensions, true) ? 'inline' : 'attachment';

        header('Content-Type: ' . (isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream'));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: ' . $disposition . '; filename="' . $download_name . '"; filename*=UTF-8\'\'' . rawurlencode($download_name));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}
