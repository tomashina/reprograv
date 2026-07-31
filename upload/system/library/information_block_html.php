<?php
final class InformationBlockHtml {
	public static function decode($description) {
		$decoded = html_entity_decode((string)$description, ENT_QUOTES, 'UTF-8');

		// The old block editor escaped already escaped HTML and Summernote then
		// wrapped it in a paragraph. Unwrap only that recognisable legacy shape
		// so intentionally escaped examples such as <code>&lt;tag&gt;</code>
		// remain text.
		if (preg_match('/^\s*<p(?:\s[^>]*)?>\s*(.*?)\s*<\/p>\s*$/is', $decoded, $matches)
			&& preg_match('/^&lt;\/?(?:p|div|img|h[1-6]|ul|ol|li|blockquote|table|thead|tbody|tr|th|td|figure|figcaption|a|strong|em)(?:\s|&gt;)/i', ltrim($matches[1]))) {
			return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
		}

		return $decoded;
	}
}
