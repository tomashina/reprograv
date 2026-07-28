<?php
class ControllerInformationPdfthumb extends Controller {
    public function index() {
        if (empty($this->request->get['f'])) {
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
            return;
        }

        $filename = basename($this->request->get['f']); // sanitiziraj
        $file = DIR_STORAGE . 'download/' . $filename;

        if (!is_file($file)) {
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
            return;
        }

        $w = isset($this->request->get['w']) ? (int)$this->request->get['w'] : 200;
        $cache_name = 'pdf_thumbs/' . md5($filename . '_' . $w) . '.jpg';
        $cache_path = DIR_IMAGE . $cache_name;

        if (!is_file($cache_path)) {
            if (!class_exists('Imagick')) {
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
                $this->response->setOutput('Imagick missing');
                return;
            }

            if (!is_dir(dirname($cache_path))) {
                mkdir(dirname($cache_path), 0777, true);
            }

            try {
                $im = new Imagick();
                $im->setResolution(150, 150);
                $im->readImage($file . '[0]');
                $im->setImageFormat('jpg');
                $im->thumbnailImage($w, 0);
                $im->writeImage($cache_path);
                $im->clear();
                $im->destroy();
            } catch (Exception $e) {
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
                $this->response->setOutput('Conversion failed: ' . $e->getMessage());
                return;
            }
        }

        $this->response->addHeader('Content-Type: image/jpeg');
        $this->response->setOutput(file_get_contents($cache_path));
    }
}
