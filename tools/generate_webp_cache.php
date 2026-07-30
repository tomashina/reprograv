<?php
if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "This tool can only be run from the command line.\n");
	exit(1);
}

ini_set('display_errors', '0');

$project_root = dirname(__DIR__);

require $project_root . '/upload/config.php';
require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'engine/model.php';
require DIR_SYSTEM . 'library/image.php';
require $project_root . '/upload/admin/model/extension/module/webp_generator.php';

class WebpGeneratorCliDb {
	private $connection;

	public function __construct() {
		$this->connection = new mysqli(
			DB_HOSTNAME,
			DB_USERNAME,
			DB_PASSWORD,
			DB_DATABASE,
			(int)DB_PORT
		);

		if ($this->connection->connect_errno) {
			throw new RuntimeException('Database connection failed.');
		}
	}

	public function query($sql) {
		$query = $this->connection->query($sql);

		if ($query === false) {
			throw new RuntimeException($this->connection->error);
		}

		$result = new stdClass();
		$result->row = array();
		$result->rows = array();

		if ($query instanceof mysqli_result) {
			while ($row = $query->fetch_assoc()) {
				$result->rows[] = $row;
			}

			if ($result->rows) {
				$result->row = $result->rows[0];
			}
		}

		return $result;
	}
}

class WebpGeneratorCliLog {
	public function write($message) {
		fwrite(STDERR, $message . "\n");
	}
}

function webpGeneratorCliOption(array $options, $name, $default) {
	return isset($options[$name]) && $options[$name] !== false ? $options[$name] : $default;
}

$options = getopt('', array('start::', 'limit::', 'batch::', 'force', 'quiet'));
$start = max(0, (int)webpGeneratorCliOption($options, 'start', 0));
$requested_limit = max(0, (int)webpGeneratorCliOption($options, 'limit', 0));
$batch_size = max(1, min(20, (int)webpGeneratorCliOption($options, 'batch', 10)));
$force = isset($options['force']);
$quiet = isset($options['quiet']);

$registry = new Registry();
$registry->set('db', new WebpGeneratorCliDb());
$registry->set('log', new WebpGeneratorCliLog());

$model = new ModelExtensionModuleWebpGenerator($registry);
$sizes = array(
	array('width' => 520, 'height' => 520, 'mode' => 'contain'),
	array('width' => 500, 'height' => 500, 'mode' => 'contain'),
	array('width' => 1000, 'height' => 1000, 'mode' => 'contain'),
	array('width' => 120, 'height' => 120, 'mode' => 'contain'),
	array('width' => 160, 'height' => 160, 'mode' => 'contain'),
	array('width' => 55, 'height' => 55, 'mode' => 'contain')
);

$total = $model->getTotalSourceImages();
$end = $requested_limit > 0 ? min($total, $start + $requested_limit) : $total;
$summary = array(
	'processed_sources' => 0,
	'generated' => 0,
	'skipped' => 0,
	'failed' => 0
);
$errors = array();
$started = microtime(true);

if (!$quiet) {
	echo 'WebP range ' . $start . '-' . max($start, $end - 1) . ' of ' . $total . " source images\n";
}

for ($offset = $start; $offset < $end; $offset += $batch_size) {
	$current_limit = min($batch_size, $end - $offset);
	$result = $model->generateBatch($offset, $current_limit, $sizes, $force);

	foreach ($summary as $key => $value) {
		if (isset($result[$key])) {
			$summary[$key] += (int)$result[$key];
		}
	}

	foreach ($result['errors'] as $error) {
		if (count($errors) < 100) {
			$errors[] = $error;
		}
	}

	if (!$quiet) {
		$processed_to = min($end, $offset + $result['processed_sources']);
		echo 'Progress ' . $processed_to . '/' . $end .
			' generated=' . $summary['generated'] .
			' skipped=' . $summary['skipped'] .
			' failed=' . $summary['failed'] . "\n";
	}

	if ($result['processed_sources'] === 0) {
		break;
	}
}

$output = array(
	'range_start' => $start,
	'range_end' => $end,
	'total_sources' => $total,
	'seconds' => round(microtime(true) - $started, 1),
	'summary' => $summary,
	'errors' => $errors
);

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

exit($summary['failed'] > 0 ? 2 : 0);
