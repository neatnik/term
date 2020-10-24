# Term

Term is a tiny, lightweight CMS purpose-built for Terminal Land. But maybe you can use it for something, too?

## Features

* Simple templating engine
* Easy metadata management process
* Respects existing directory structure
* Supports Markdown
* Effortlessly extensible

## Get started

1. Put `term.php` wherever you’d like on your server. If you want Markdown support, put [`Parsedown.php`](https://github.com/erusev/parsedown) in the same location.
2. At the root level from which your content is served, add a `.htaccess` file that looks like this:
```
Options -Indexes
DirectoryIndex /path/to/term.php
```
3. In any directory from which you’re serving content, add a `metadata.json` file with these items at a minimum:
```
{
  "index": "your_index.file",
  "title": "Term",
}
```
4. That’s it. Your Term-powered site is now up and running.

## Make it your own

You can edit `term.html` to customize the template however you’d like.

Term supports “collections”, which lets you manage some metadata at one level above the current page. For example, if you have some related pages being served under a parent directory, that parent directory is a collection and you can include a `metadata.json` file there. That JSON string should include `url`, `title`, and `icon` items which will override those values from the local metadata file.

Here’s a list of available metadata items:

* **`index`**: the file that should be served when the directory is accessed
* **`stylesheet`**: the stylesheet path/filename to be used
* **`title`**: the page’s title
* **`description`**: the page’s description
* **`url`**: the page’s canonical URL
* **`icon`**: any HTML that you’d like to include for a page or collection specific icon (SVG works well here)
* **`head`**: any additional stuff you’d like to include in your page’s `<head>` section

That’s all. Hope you find it useful.