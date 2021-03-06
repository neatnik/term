<?php

/* Term: a tiny, lightweight CMS purpose-built for Terminal Land

Copyright © 2020 Neatnik

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE. */

// Configuration
define('TEMPLATE', '/var/www/html/tools/term/term.html');

// Load the template
if(file_exists(TEMPLATE)) $template = file_get_contents(TEMPLATE);
else die('No template file was found.');

// Prepare base path
$request_uri = explode('/', $_SERVER['REQUEST_URI']);
unset($request_uri[0]);


$request_uri = array_values($request_uri);
$tmp_request_uri = '/';
$possible_request_uris = array();
foreach($request_uri as $segment) {
	$tmp_request_uri = $tmp_request_uri . $segment.'/';
	$possible_request_uris[] = $tmp_request_uri;
}

$possible_request_uris = array_reverse($possible_request_uris);

foreach($possible_request_uris as $request_uri) {
	if(file_exists($_SERVER['DOCUMENT_ROOT'].$request_uri.'metadata.json')) {
		$metadata = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].$request_uri.'metadata.json'));
		goto skip;
	}
}

die('No metadata file was found.');

skip:

// Load collection metadata
if(file_exists($_SERVER['DOCUMENT_ROOT'].$request_uri.'../metadata.json')) $collection_metadata = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].$request_uri.'../metadata.json'));
else $collection_metadata = null;

// Include index content
ob_start();
if(isset($content)) {
	preg_match('/%{(.*)}%/s', $content, $local_metadata);
	if(isset($local_metadata[1])) {
		$remove = $local_metadata[0];
		$local_metadata = json_decode('{'.$local_metadata[1].'}');
		$content = str_replace($remove, null, $content);
	}
	
	echo $content;
}
else {
	include_once($_SERVER['DOCUMENT_ROOT'].$request_uri.$metadata->index);
}
$content = ob_get_contents();
ob_end_clean();

// Process Markdown
if(file_exists('/var/www/html/tools/term/Parsedown.php')) {
	include_once('/var/www/html/tools/term/Parsedown.php');
	$Parsedown = new Parsedown();
	$content = $Parsedown->text($content);
}

// Process template replacements
function process($item, $template) {
	global $local_metadata;
	global $metadata;
	global $collection_metadata;
	$set = false;
	
	if(isset($local_metadata->$item)) {
		$template = str_replace('{{'.$item.'}}', $local_metadata->$item, $template);
		$set = true;
	}
	
	if(isset($metadata->$item)) {
		$template = str_replace('{{'.$item.'}}', $metadata->$item, $template);
		$set = true;
	}
	
	if(isset($collection_metadata->$item)) {
		$template = str_replace('{{'.$item.'}}', $collection_metadata->$item, $template);
		$set = true;
	}
	
	if(!$set) $template = str_replace('{{'.$item.'}}', null, $template);
	
	return $template;
}

// Execute replacements
$template_items = array('stylesheet', 'collection_url', 'url', 'icon', 'collection_title', 'title', 'description');
foreach($template_items as $item) {
	$template = process($item, $template);
}

// Handle {{head}} if set, either in content or in metadata
if(isset($term_head)) {
	$template = str_replace('{{head}}', $term_head, $template);
}
else {
	if(isset($collection_metadata->head)) {
		$template = str_replace('{{head}}', file_get_contents($_SERVER['DOCUMENT_ROOT'].$request_uri.$collection_metadata->head), $template);
	}
	else if(isset($metadata->head)) {
		$template = str_replace('{{head}}', file_get_contents($_SERVER['DOCUMENT_ROOT'].$request_uri.$metadata->head), $template);
	}
	else {
		$template = str_replace('{{head}}', null, $template);
	}
}

// Default for {{url}}, just in case
$template = str_replace('{{url}}', 'https://'.$_SERVER['HTTP_HOST'].$request_uri, $template);

// Add the content
$template = str_replace('{{content}}', $content, $template);

echo $template;