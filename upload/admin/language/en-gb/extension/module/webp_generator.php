<?php
$_['heading_title'] = 'Catalog WebP Generator';

$_['text_home'] = 'Home';
$_['text_extension'] = 'Extensions';
$_['text_tool'] = 'Bulk WebP generation';
$_['text_intro'] = 'Create a WebP copy of every source image and pre-generate all standard sizes for active product images. Original images and database paths remain unchanged.';
$_['text_on_demand'] = 'The storefront creates WebP cache files on first use. This tool warms the catalog so customers and crawlers do not wait for the first conversion.';
$_['text_source_images'] = 'All source images';
$_['text_product_images'] = 'Active product images';
$_['text_cached_webp'] = 'Cached WebP files';
$_['text_cache_size'] = 'WebP cache size';
$_['text_target_count'] = 'Possible variants';
$_['text_dimensions'] = 'Product dimensions to generate';
$_['text_dimension_name'] = 'Purpose';
$_['text_dimension_size'] = 'Dimensions';
$_['text_quality'] = 'WebP quality';
$_['text_batch_size'] = 'Images per step';
$_['text_batch_help'] = 'Smaller batches are safer on a slower server.';
$_['text_force'] = 'Regenerate existing WebP files';
$_['text_force_help'] = 'When disabled, existing up-to-date files are skipped quickly.';
$_['text_ready'] = 'Ready to start.';
$_['text_starting'] = 'Starting generator…';
$_['text_processing'] = 'Processed %1$d of %2$d source images.';
$_['text_stopping'] = 'Stopping after the current batch…';
$_['text_stopped'] = 'Generation stopped. You can start it again; existing files will be skipped.';
$_['text_complete'] = 'Generation completed.';
$_['text_generated'] = 'Generated';
$_['text_skipped'] = 'Skipped';
$_['text_failed'] = 'Errors';
$_['text_errors'] = 'Latest errors';
$_['text_confirm_force'] = 'Regeneration can take a while and will replace the existing WebP cache. Continue?';

$_['size_product'] = 'Product listings';
$_['size_thumb'] = 'Main product image';
$_['size_popup'] = 'Large image';
$_['size_additional'] = 'Additional images';
$_['size_related'] = 'Related products';
$_['size_compare'] = 'Comparison';
$_['size_wishlist'] = 'Wish list';
$_['size_cart'] = 'Cart';

$_['button_generate'] = 'Generate all WebP images';
$_['button_stop'] = 'Stop';
$_['button_cancel'] = 'Back';

$_['error_permission'] = 'You do not have permission to use the WebP generator.';
$_['error_method'] = 'Invalid generator request method.';
$_['error_webp_support'] = 'PHP GD does not have WebP support. The generator cannot run.';
$_['error_request'] = 'The server request failed. Check the error log and try again.';
$_['error_generation'] = 'Generation failed. Details were written to the OpenCart error log.';
$_['error_invalid_path'] = 'The image path is invalid or unsafe.';
$_['error_unsupported_type'] = 'The image is not a JPG, PNG, or WebP file.';
$_['error_missing_source'] = 'The source image does not exist.';
$_['error_invalid_image'] = 'The file is not a valid image.';
$_['error_invalid_size'] = 'The image dimensions are invalid.';
$_['error_directory_not_writable'] = 'The cache directory is not writable.';
$_['error_conversion_failed'] = 'WebP conversion failed.';
