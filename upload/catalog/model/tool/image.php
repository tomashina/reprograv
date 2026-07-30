<?php
class ModelToolImage extends Model {
	public function webp($filename) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$source_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if (!function_exists('imagewebp') || !in_array($source_extension, array('jpg', 'jpeg', 'png', 'webp'), true)) {
			return $this->imageUrl($filename);
		}

		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '.webp';

		if (!is_file(DIR_IMAGE . $image_new) || filemtime(DIR_IMAGE . $filename) > filemtime(DIR_IMAGE . $image_new)) {
			$directories = explode('/', dirname($image_new));
			$path = '';

			foreach ($directories as $directory) {
				$path .= '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($source_extension === 'webp') {
				copy(DIR_IMAGE . $filename, DIR_IMAGE . $image_new);
			} else {
				$image = new Image(DIR_IMAGE . $filename);
				$image->save(DIR_IMAGE . $image_new, 82);
			}
		}

		return $this->imageUrl($image_new);
	}

	public function resize($filename, $width, $height, $mode = 'contain') {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$width = max(1, (int)$width);
		$height = max(1, (int)$height);
		$source_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$webp = function_exists('imagewebp') && in_array($source_extension, array('jpg', 'jpeg', 'png', 'webp'), true);
		$extension = $webp ? 'webp' : $source_extension;
		$quality = 82;

		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . ($mode === 'cover' ? '-cover' : '') . ($webp ? '-q' . $quality : '') . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			$valid_types = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
			if (defined('IMAGETYPE_WEBP')) {
				$valid_types[] = IMAGETYPE_WEBP;
			}

			if (!in_array($image_type, $valid_types)) {
				return DIR_IMAGE . $image_old;
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($image_type == IMAGETYPE_GIF) {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			} else {
				$image = new Image(DIR_IMAGE . $image_old);
				if ($width_orig != $width || $height_orig != $height) {
					if ($mode === 'cover') {
						$source_ratio = $width_orig / $height_orig;
						$target_ratio = $width / $height;
						$image->resize($width, $height, $source_ratio > $target_ratio ? 'h' : 'w');
					} else {
						$image->resize($width, $height);
					}
				}
				$image->save(DIR_IMAGE . $image_new, $quality);
			}
		}
		
		return $this->imageUrl($image_new);
	}

	private function imageUrl($filename) {
		$filename = str_replace(' ', '%20', $filename);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +

		if (!empty($this->request->server['HTTPS'])) {
			return $this->config->get('config_ssl') . 'image/' . $filename;
		} else {
			return $this->config->get('config_url') . 'image/' . $filename;
		}
	}
}
