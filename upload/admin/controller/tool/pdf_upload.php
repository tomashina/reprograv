<?php
class ControllerToolPdfUpload extends Controller {
    public function index() {
        $this->load->language('tool/upload');
        $json = [];

        // === PDF.co API KEY ===
        $PDFCO_API_KEY = 'tomislav@agmedia.hr_LA9sY4tih75Z9mGQZJaecInnCsfh9q6hKvluw80Pak3jpvdLTM2a4aErPFpIIYq6';

        // === LOG helper ===
        $log_file = rtrim(DIR_STORAGE, '/\\') . '/logs/pdfco.log';
        $log = function($msg) use ($log_file) {
            @file_put_contents($log_file, '['.date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
        };
        $log('--- START upload ---');

        // 1) Permission + file presence
        if (!$this->user->hasPermission('modify', 'tool/pdf_upload')) {
            $json['error'] = $this->language->get('error_permission');
            $log('ERROR: no permission');
            return $this->respond($json);
        }
        if (empty($this->request->files['file'])) {
            $json['error'] = 'No file uploaded';
            $log('ERROR: no file in request');
            return $this->respond($json);
        }

        $file = $this->request->files['file'];
        $orig_name = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));
        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $allowed = ['pdf','jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
            $json['error'] = 'Allowed: PDF, JPG, PNG, GIF, WEBP';
            $log('ERROR: ext not allowed: '.$ext);
            return $this->respond($json);
        }

        // 2) Save original to storage
        if (!function_exists('token')) {
            function token($length = 32) { return bin2hex(random_bytes($length/2)); }
        }
        $filename = token(32) . '.' . $ext;

        $target_dir = rtrim(DIR_STORAGE, '/\\') . '/download/';
        if (!is_dir($target_dir)) { @mkdir($target_dir, 0777, true); }
        $target = $target_dir . $filename;

        if (!is_uploaded_file($file['tmp_name']) || !@move_uploaded_file($file['tmp_name'], $target)) {
            $json['error'] = 'Upload failed';
            $log('ERROR: move_uploaded_file failed to ' . $target);
            return $this->respond($json);
        }
        $log('Saved original: ' . $target);

        // 3) Prepare thumbs dir
        $thumb_dir_rel = 'pdf_thumbs/';
        $thumb_dir_abs = rtrim(DIR_IMAGE, '/\\') . '/' . $thumb_dir_rel;
        if (!is_dir($thumb_dir_abs)) { @mkdir($thumb_dir_abs, 0777, true); }
        $thumb_name = md5($filename) . '.png';
        $thumb_abs  = $thumb_dir_abs . $thumb_name;
        $thumb_rel  = $thumb_dir_rel . $thumb_name;

        // 4) Make thumbnail
        if ($ext === 'pdf') {
            // 4a) Upload PDF to PDF.co file storage
            $file_content = @file_get_contents($target);
            if ($file_content === false) {
                $log('ERROR: cannot read saved PDF for upload');
                $pdfco_url_uploaded = null;
            } else {
                $log('Uploading to PDF.co file storage...');
                $upload_resp = $this->pdfcoUpload($PDFCO_API_KEY, $file_content, $orig_name, $log);
                $pdfco_url_uploaded = $upload_resp['url'] ?? null;
                if (!$pdfco_url_uploaded) {
                    $log('ERROR: PDF.co upload failed: ' . ($upload_resp['error'] ?? 'unknown'));
                } else {
                    $log('Uploaded to PDF.co: ' . $pdfco_url_uploaded);
                }
            }

            // 4b) Convert first page to PNG via PDF.co (using the URL from upload)
            if ($pdfco_url_uploaded) {
                $log('Converting via PDF.co...');
                $conv = $this->pdfcoConvertFirstPageToPngUrl($PDFCO_API_KEY, $pdfco_url_uploaded, $log);
                $thumb_remote = $conv['thumb_url'] ?? null;
                if (!$thumb_remote) {
                    $log('ERROR: convert returned no thumb_url. Raw: ' . json_encode($conv));
                } else {
                    $log('Thumb URL: ' . $thumb_remote);

                    // 4c) Download the PNG to local /image/pdf_thumbs/
                    $ok = $this->downloadTo($thumb_remote, $thumb_abs, $log);
                    if ($ok && is_file($thumb_abs) && filesize($thumb_abs) > 0) {
                        $json['thumb'] = $thumb_rel;
                        $log('Thumb saved: ' . $thumb_abs);
                    } else {
                        @unlink($thumb_abs);
                        $log('ERROR: downloading thumb failed');
                    }
                }
            }
        } else {
            // Images: simple copy as "thumb" (or you can wire model_tool_image for resize)
            @copy($target, $thumb_abs);
            if (is_file($thumb_abs) && filesize($thumb_abs) > 0) {
                $json['thumb'] = $thumb_rel;
                $log('Image thumb saved (copy): ' . $thumb_abs);
            } else {
                $log('WARN: image copy to thumb failed');
            }
        }

        // 5) Compose response
        $json['filename'] = $filename; // physical in storage/download
        $json['mask']     = $orig_name;
        $json['success']  = 'Uploaded';
        if (empty($json['thumb'])) {
            // fallback icon (place one at image/pdf-icon.png)
            $json['thumb'] = 'pdf-icon.png';
            $log('Fallback thumb used: pdf-icon.png');
        }

        $this->respond($json);
    }

    // === helpers ===

    private function respond($json) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        return;
    }

    private function pdfcoUpload($apiKey, $binary, $orig_name, $log) {
        // POST https://api.pdf.co/v1/file/upload
        // Sends multipart/form-data with 'file' field
        $url = 'https://api.pdf.co/v1/file/upload';
        $boundary = '----pdfco' . bin2hex(random_bytes(8));

        $body  = "--$boundary\r\n";
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . addslashes($orig_name) . "\"\r\n";
        $body .= "Content-Type: application/octet-stream\r\n\r\n";
        $body .= $binary . "\r\n";
        $body .= "--$boundary--\r\n";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $apiKey,
                'Content-Type: multipart/form-data; boundary=' . $boundary
            ],
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            $log('ERROR pdfcoUpload cURL: ' . $err);
            return ['error' => $err];
        }

        $data = json_decode($resp, true);
        if (!empty($data['error'])) {
            $log('ERROR pdfcoUpload API: ' . ($data['message'] ?? 'unknown'));
        }
        return $data ?: ['error' => 'invalid json'];
    }

    private function pdfcoConvertFirstPageToPngUrl($apiKey, $file_url, $log) {
        // POST JSON: https://api.pdf.co/v1/pdf/convert/to/png
        // { url: "<file_url>", pages: "1", async: false }
        $payload = json_encode([
            'url'          => $file_url,
            'pages'        => '0',
            'outputFormat' => 'png',
            'async'        => false
        ]);

        $ch = curl_init('https://api.pdf.co/v1/pdf/convert/to/png');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            $log('ERROR convert cURL: ' . $err);
            return ['error' => $err];
        }
        $data = json_decode($resp, true);
        if (!empty($data['error'])) {
            $log('ERROR convert API: ' . ($data['message'] ?? 'unknown'));
            return $data;
        }
        $thumb_url = $data['url'] ?? ($data['urls'][0] ?? null);
        return ['thumb_url' => $thumb_url] + $data;
    }

    private function downloadTo($url, $dest, $log) {
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $fh = @fopen($dest, 'w');
        if (!$fh) {
            $log('ERROR: cannot open dest for write: ' . $dest);
            return false;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE           => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fh);

        if (!$ok) {
            $log('ERROR: downloadTo cURL: ' . $err);
            return false;
        }
        if (!is_file($dest) || filesize($dest) === 0) {
            $log('ERROR: downloaded file empty: ' . $dest);
            return false;
        }
        return true;
    }
}
