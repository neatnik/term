<?php

/* Term: a tiny, lightweight CMS purpose-built for Terminal Land

Copyright (c) 2020 Neatnik

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

// Load the template
if(file_exists('term.html')) $template = file_get_contents('term.html');
else die('No template file was found.');

// Load page metadata
if(file_exists($_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'].'metadata.json')) $metadata = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'].'metadata.json'));
else die('No metadata file was found.');

// Load collection metadata
if(file_exists($_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'].'../metadata.json')) $collection_metadata = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'].'../metadata.json'));
else $collection_metadata = null;

// Include index content
ob_start();
include_once($_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'].$metadata->index);
$content = ob_get_contents();
ob_end_clean();

// Process Markdown
if(file_exists('Parsedown.php')) {
	include_once('Parsedown.php');
	$Parsedown = new Parsedown();
	$content = $Parsedown->text($content);
}

// Process template replacements
function process($item, $template) {
	global $metadata;
	global $collection_metadata;
	if(isset($collection_metadata->$item)) $template = str_replace('{{'.$item.'}}', $collection_metadata->$item, $template);
	else if(isset($metadata->$item)) $template = str_replace('{{'.$item.'}}', $metadata->$item, $template);
	else $template = str_replace('{{'.$item.'}}', null, $template);
	return $template;
}

// Execute replacements
$template_items = array('head', 'stylesheet', 'url', 'icon', 'title', 'description');
foreach($template_items as $item) {
	$template = process($item, $template);
}

// Default for {{url}}, just in case
$template = str_replace('{{url}}', 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $template);

// Add the content
$template = str_replace('{{content}}', $content, $template);

echo $template;
