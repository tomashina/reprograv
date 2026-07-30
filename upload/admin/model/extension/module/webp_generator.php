<?php
class ModelExtensionModuleWebpGenerator extends Model {
	const QUALITY = 82;

	public function getTotalSourceImages() {
		return count($this->getAllSourceImages());
	}

	public function getTotalProductImages() {
		$query = $this->db->query(
			"SELECT COUNT(*) AS total FROM (" .
			"SELECT image FROM `" . DB_PREFIX . "product` WHERE status = '1' AND image <> '' " .
			"UNION " .
			"SELECT pi.image FROM `" . DB_PREFIX . "product_image` pi " .
			"INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pi.product_id) " .
			"WHERE p.status = '1' AND pi.image <> ''" .
			") product_images"
		);

		return (int)$query->row['total'];
	}

	public function getTargetCount(array $sizes) {
		return $this->getTotalSourceImages() + ($this->getTotalProductImages() * count($sizes));
	}

	public function getSourceImages($start = 0, $limit = 5) {
		$start = max(0, (int)$start);
		$limit = max(1, min(20, (int)$limit));

		return array_slice($this->getAllSourceImages(), $start, $limit);
	}

	public function getWebpCacheStats() {
		$stats = array(
			'files' => 0,
			'bytes' => 0
		);
		$cache_directory = DIR_IMAGE . 'cache';

		if (!is_dir($cache_directory)) {
			return $stats;
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($cache_directory, FilesystemIterator::SKIP_DOTS)
			);

			foreach ($iterator as $file) {
				if ($file->isFile() && strtolower($file->getExtension()) === 'webp') {
					$stats['files']++;
					$stats['bytes'] += $file->getSize();
				}
			}
		} catch (UnexpectedValueException $exception) {
			$this->log->write('WebP Generator: Cache statistics could not be read: ' . $exception->getMessage());
		}

		return $stats;
	}

	public function generateBatch($start, $limit, array $sizes, $force = false) {
		$images = $this->getSourceImages($start, $limit);
		$product_images = $this->getProductImageLookup();
		$result = array(
			'processed_sources' => count($images),
			'generated' => 0,
			'skipped' => 0,
			'failed' => 0,
			'errors' => array()
		);

		foreach ($images as $row) {
			$filename = $this->normaliseFilename($row['image']);
			$source = $this->validateSource($filename);

			if (!$source['valid']) {
				$result['failed']++;
				$this->addError($result, $filename, $source['code']);
				continue;
			}

			$this->recordResult(
				$result,
				$filename,
				$this->generateNativeImage($filename, $source, (bool)$force),
				$source['width'] . '×' . $source['height']
			);

			if (!isset($product_images[$filename])) {
				continue;
			}

			foreach ($sizes as $size) {
				$width = isset($size['width']) ? (int)$size['width'] : 0;
				$height = isset($size['height']) ? (int)$size['height'] : 0;
				$mode = isset($size['mode']) ? $size['mode'] : 'contain';

				$this->recordResult(
					$result,
					$filename,
					$this->generateResizedImage($filename, $source, $width, $height, $mode, (bool)$force),
					$width . '×' . $height
				);
			}
		}

		return $result;
	}

	private function getAllSourceImages() {
		$images = array();
		$image_root = realpath(DIR_IMAGE);

		if ($image_root === false || !is_dir($image_root)) {
			return $images;
		}

		$image_root = rtrim(str_replace('\\', '/', $image_root), '/') . '/';

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($image_root, FilesystemIterator::SKIP_DOTS)
			);

			foreach ($iterator as $file) {
				if (!$file->isFile() || $file->isLink()) {
					continue;
				}

				$path = str_replace('\\', '/', $file->getPathname());

				if (strpos($path, $image_root . 'cache/') === 0) {
					continue;
				}

				$extension = strtolower($file->getExtension());

				if (!in_array($extension, array('jpg', 'jpeg', 'png', 'webp'), true)) {
					continue;
				}

				$images[] = array('image' => substr($path, strlen($image_root)));
			}
		} catch (UnexpectedValueException $exception) {
			$this->log->write('WebP Generator: Image directory could not be read: ' . $exception->getMessage());
		}

		usort($images, function($a, $b) {
			return strcmp($a['image'], $b['image']);
		});

		return $images;
	}

	private function getProductImageLookup() {
		$lookup = array();
		$query = $this->db->query(
			"SELECT image FROM (" .
			"SELECT image FROM `" . DB_PREFIX . "product` WHERE status = '1' AND image <> '' " .
			"UNION " .
			"SELECT pi.image FROM `" . DB_PREFIX . "product_image` pi " .
			"INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pi.product_id) " .
			"WHERE p.status = '1' AND pi.image <> ''" .
			") product_images"
		);

		foreach ($query->rows as $row) {
			$filename = $this->normaliseFilename($row['image']);

			if ($filename !== '') {
				$lookup[$filename] = true;
			}
		}

		return $lookup;
	}

	private function normaliseFilename($filename) {
		return ltrim(str_replace('\\', '/', trim((string)$filename)), '/');
	}

	private function validateSource($filename) {
		if ($filename === '' || preg_match('#(^|/)\.\.(/|$)#', $filename)) {
			return array('valid' => false, 'code' => 'invalid_path');
		}

		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if (!in_array($extension, array('jpg', 'jpeg', 'png', 'webp'), true)) {
			return array('valid' => false, 'code' => 'unsupported_type');
		}

		$image_root = realpath(DIR_IMAGE);
		$source_path = realpath(DIR_IMAGE . $filename);

		if ($image_root === false || $source_path === false || !is_file($source_path)) {
			return array('valid' => false, 'code' => 'missing_source');
		}

		$image_root = rtrim(str_replace('\\', '/', $image_root), '/') . '/';
		$normalised_source = str_replace('\\', '/', $source_path);

		if (strpos($normalised_source, $image_root) !== 0) {
			return array('valid' => false, 'code' => 'invalid_path');
		}

		$info = @getimagesize($source_path);
		$valid_types = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);

		if (defined('IMAGETYPE_WEBP')) {
			$valid_types[] = IMAGETYPE_WEBP;
		}

		if (!$info || !in_array($info[2], $valid_types, true)) {
			return array('valid' => false, 'code' => 'invalid_image');
		}

		return array(
			'valid' => true,
			'path' => $source_path,
			'extension' => $extension,
			'width' => (int)$info[0],
			'height' => (int)$info[1]
		);
	}

	private function generateNativeImage($filename, array $source, $force) {
		$filename_without_extension = substr($filename, 0, strrpos($filename, '.'));
		$target_path = DIR_IMAGE . 'cache/' . $filename_without_extension . '.webp';

		return $this->writeWebp($filename, $source, $target_path, $source['width'], $source['height'], 'contain', $force);
	}

	private function generateResizedImage($filename, array $source, $width, $height, $mode, $force) {
		if ($width < 1 || $height < 1 || $width > 4096 || $height > 4096) {
			return array('status' => 'failed', 'code' => 'invalid_size');
		}

		$mode = $mode === 'cover' ? 'cover' : 'contain';
		$filename_without_extension = substr($filename, 0, strrpos($filename, '.'));
		$target_path = DIR_IMAGE . 'cache/' . $filename_without_extension . '-' . $width . 'x' . $height;

		if ($mode === 'cover') {
			$target_path .= '-cover';
		}

		$target_path .= '-q' . self::QUALITY . '.webp';

		return $this->writeWebp($filename, $source, $target_path, $width, $height, $mode, $force);
	}

	private function writeWebp($filename, array $source, $target_path, $width, $height, $mode, $force) {
		if (
			!$force &&
			is_file($target_path) &&
			filesize($target_path) > 0 &&
			filemtime($target_path) >= filemtime($source['path'])
		) {
			return array('status' => 'skipped');
		}

		$target_directory = dirname($target_path);

		if (!is_dir($target_directory) && !@mkdir($target_directory, 0777, true) && !is_dir($target_directory)) {
			return array('status' => 'failed', 'code' => 'directory_not_writable');
		}

		if (!is_writable($target_directory)) {
			return array('status' => 'failed', 'code' => 'directory_not_writable');
		}

		try {
			if (
				$source['width'] === $width &&
				$source['height'] === $height &&
				$source['extension'] === 'webp'
			) {
				$success = @copy($source['path'], $target_path);
			} else {
				$image = new Image($source['path']);

				if ($source['width'] !== $width || $source['height'] !== $height) {
					if ($mode === 'cover') {
						$source_ratio = $source['width'] / $source['height'];
						$target_ratio = $width / $height;
						$image->resize($width, $height, $source_ratio > $target_ratio ? 'h' : 'w');
					} else {
						$image->resize($width, $height);
					}
				}

				$image->save($target_path, self::QUALITY);
				$success = is_file($target_path) && filesize($target_path) > 0;
				unset($image);
			}
		} catch (Throwable $exception) {
			$this->log->write('WebP Generator: ' . $filename . ': ' . $exception->getMessage());
			return array('status' => 'failed', 'code' => 'conversion_failed');
		}

		if (!$success) {
			return array('status' => 'failed', 'code' => 'conversion_failed');
		}

		return array('status' => 'generated');
	}

	private function recordResult(array &$result, $filename, array $generated, $size) {
		if ($generated['status'] === 'generated') {
			$result['generated']++;
		} elseif ($generated['status'] === 'skipped') {
			$result['skipped']++;
		} else {
			$result['failed']++;
			$this->addError($result, $filename, $generated['code'], $size);
		}
	}

	private function addError(array &$result, $filename, $code, $size = '') {
		if (count($result['errors']) >= 20) {
			return;
		}

		$error = array(
			'file' => $filename,
			'code' => $code
		);

		if ($size !== '') {
			$error['size'] = $size;
		}

		$result['errors'][] = $error;
	}
}
