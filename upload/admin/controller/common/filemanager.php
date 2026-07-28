<?php
class ControllerCommonFileManager extends Controller {
    public function index() {
        $this->load->language('common/filemanager');

        // Catalog URL
        if ($this->request->server['HTTPS']) {
            $server = HTTPS_CATALOG;
        } else {
            $server = HTTP_CATALOG;
        }

        // Admin URL za ikone
        if (!empty($this->request->server['HTTPS'])) {
            $admin_server = HTTPS_SERVER;
        } else {
            $admin_server = HTTP_SERVER;
        }

        // Filter name
        if (isset($this->request->get['filter_name'])) {
            $filter_name = rtrim(str_replace(array('*', '/', '\\'), '', $this->request->get['filter_name']), '/');
        } else {
            $filter_name = '';
        }

        // Directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . str_replace('*', '', $this->request->get['directory']), '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }

        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

        $data['images'] = array();

        $this->load->model('tool/image');

        if (substr(str_replace('\\', '/', realpath($directory) . '/' . $filter_name), 0, strlen(DIR_IMAGE . 'catalog')) == str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
            // Get directories
            $directories = glob($directory . '/' . $filter_name . '*', GLOB_ONLYDIR);
            if (!$directories) $directories = array();

            // Get files (dodali mp4, webm, avif itd.)
            $files = glob($directory . '/' . $filter_name . '*.{jpg,jpeg,png,gif,webp,avif,mp4,webm,ogg,JPG,JPEG,PNG,GIF,WEBP,AVIF,MP4,WEBM,OGG}', GLOB_BRACE);
            if (!$files) $files = array();
        }

        // Merge
        $images = array_merge($directories, $files);

        // Pagination
        $image_total = count($images);
        $images = array_splice($images, ($page - 1) * 16, 16);

        foreach ($images as $image) {
            $name = str_split(basename($image), 14);

            if (is_dir($image)) {
                $url = '';

                if (isset($this->request->get['target'])) {
                    $url .= '&target=' . $this->request->get['target'];
                }

                if (isset($this->request->get['thumb'])) {
                    $url .= '&thumb=' . $this->request->get['thumb'];
                }

                $data['images'][] = array(
                    'thumb' => '',
                    'name'  => implode(' ', $name),
                    'type'  => 'directory',
                    'path'  => utf8_substr($image, utf8_strlen(DIR_IMAGE)),
                    'href'  => $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . '&directory=' . urlencode(utf8_substr($image, utf8_strlen(DIR_IMAGE . 'catalog/'))) . $url, true)
                );
            } elseif (is_file($image)) {
                $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                $rel = utf8_substr($image, utf8_strlen(DIR_IMAGE));

                $is_image = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                $is_video = in_array($ext, ['mp4','webm','ogg']);

                if ($is_image) {
                    // Thumbnail iz same slike
                    $thumb = $this->model_tool_image->resize($rel, 100, 100);
                    $type  = 'image';
                } elseif ($ext === 'avif') {
                    $thumb = $admin_server . 'view/image/filemanager/avif.png';
                    $type  = 'image';
                } elseif ($is_video) {
                    $thumb = $admin_server . 'view/image/filemanager/video.png';
                    $type  = 'image'; // mora ostati image zbog <img> u twig-u
                } else {
                    $thumb = $admin_server . 'view/image/filemanager/file.png';
                    $type  = 'image';
                }

                $data['images'][] = [
                    'thumb' => $thumb,
                    'name'  => implode(' ', $name),
                    'type'  => $type,
                    'path'  => $rel,
                    'href'  => $server . 'image/' . $rel
                ];
            }
        }

        $data['user_token'] = $this->session->data['user_token'];
        $data['directory']  = isset($this->request->get['directory']) ? urlencode($this->request->get['directory']) : '';
        $data['filter_name'] = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
        $data['target'] = isset($this->request->get['target']) ? $this->request->get['target'] : '';
        $data['thumb']  = isset($this->request->get['thumb']) ? $this->request->get['thumb'] : '';

        // Parent
        $url = '';
        if (isset($this->request->get['directory'])) {
            $pos = strrpos($this->request->get['directory'], '/');
            if ($pos) {
                $url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
            }
        }
        if (isset($this->request->get['target'])) $url .= '&target=' . $this->request->get['target'];
        if (isset($this->request->get['thumb']))  $url .= '&thumb=' . $this->request->get['thumb'];
        $data['parent'] = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url, true);

        // Refresh
        $url = '';
        if (isset($this->request->get['directory'])) $url .= '&directory=' . urlencode($this->request->get['directory']);
        if (isset($this->request->get['target']))    $url .= '&target=' . $this->request->get['target'];
        if (isset($this->request->get['thumb']))     $url .= '&thumb=' . $this->request->get['thumb'];
        $data['refresh'] = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url, true);

        // Pagination
        $url = '';
        if (isset($this->request->get['directory'])) $url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
        if (isset($this->request->get['filter_name'])) $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        if (isset($this->request->get['target'])) $url .= '&target=' . $this->request->get['target'];
        if (isset($this->request->get['thumb']))  $url .= '&thumb=' . $this->request->get['thumb'];

        $pagination = new Pagination();
        $pagination->total = $image_total;
        $pagination->page = $page;
        $pagination->limit = 16;
        $pagination->url = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $this->response->setOutput($this->load->view('common/filemanager', $data));
    }

    public function upload() {
        $this->load->language('common/filemanager');
        $json = array();

        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        // Directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . $this->request->get['directory'], '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }

        if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
            $json['error'] = $this->language->get('error_directory');
        }

        if (!$json) {
            $files = array();

            if (!empty($this->request->files['file']['name']) && is_array($this->request->files['file']['name'])) {
                foreach (array_keys($this->request->files['file']['name']) as $key) {
                    $files[] = array(
                        'name'     => $this->request->files['file']['name'][$key],
                        'type'     => $this->request->files['file']['type'][$key],
                        'tmp_name' => $this->request->files['file']['tmp_name'][$key],
                        'error'    => $this->request->files['file']['error'][$key],
                        'size'     => $this->request->files['file']['size'][$key]
                    );
                }
            }

            foreach ($files as $file) {
                if (is_file($file['tmp_name'])) {
                    $filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

                    if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
                        $json['error'] = $this->language->get('error_filename');
                    }

                    // Ekstenzije
                    $allowed = array('jpg','jpeg','gif','avif','png','mp4');
                    if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed)) {
                        $json['error'] = $this->language->get('error_filetype');
                    }

                    // MIME tipovi
                    $allowed = array(
                        'image/jpeg','image/pjpeg','image/png','image/x-png',
                        'image/gif','image/avif','video/mp4'
                    );
                    if (!in_array($file['type'], $allowed)) {
                        $json['error'] = $this->language->get('error_filetype');
                    }

                    if ($file['size'] > $this->config->get('config_file_max_size')) {
                        $json['error'] = $this->language->get('error_filesize');
                    }

                    if ($file['error'] != UPLOAD_ERR_OK) {
                        $json['error'] = $this->language->get('error_upload_' . $file['error']);
                    }
                } else {
                    $json['error'] = $this->language->get('error_upload');
                }

                if (!$json) {
                    move_uploaded_file($file['tmp_name'], $directory . '/' . $filename);
                }
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_uploaded');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function folder() {
        $this->load->language('common/filemanager');
        $json = array();

        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . $this->request->get['directory'], '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }

        if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
            $json['error'] = $this->language->get('error_directory');
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $folder = basename(html_entity_decode($this->request->post['folder'], ENT_QUOTES, 'UTF-8'));

            if ((utf8_strlen($folder) < 3) || (utf8_strlen($folder) > 128)) {
                $json['error'] = $this->language->get('error_folder');
            }

            if (is_dir($directory . '/' . $folder)) {
                $json['error'] = $this->language->get('error_exists');
            }
        }

        if (!isset($json['error'])) {
            mkdir($directory . '/' . $folder, 0777);
            chmod($directory . '/' . $folder, 0777);
            @touch($directory . '/' . $folder . '/' . 'index.html');
            $json['success'] = $this->language->get('text_directory');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete() {
        $this->load->language('common/filemanager');
        $json = array();

        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        $paths = isset($this->request->post['path']) ? $this->request->post['path'] : array();

        foreach ($paths as $path) {
            if ($path == DIR_IMAGE . 'catalog' || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $path)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
                $json['error'] = $this->language->get('error_delete');
                break;
            }
        }

        if (!$json) {
            foreach ($paths as $path) {
                $path = rtrim(DIR_IMAGE . $path, '/');

                if (is_file($path)) {
                    unlink($path);
                } elseif (is_dir($path)) {
                    $files = array();
                    $path = array($path);

                    while (count($path) != 0) {
                        $next = array_shift($path);

                        foreach (glob($next) as $file) {
                            if (is_dir($file)) {
                                $path[] = $file . '/*';
                            }
                            $files[] = $file;
                        }
                    }

                    rsort($files);

                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        } elseif (is_dir($file)) {
                            rmdir($file);
                        }
                    }
                }
            }
            $json['success'] = $this->language->get('text_delete');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
