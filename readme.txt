=== Paged Gallery ===

Contributors: andreyk
Donate link: http://andrey.eto-ya.com/wordpress/my-plugins/paged-gallery-plugin
Tags: image, images, gallery, galleries, shortcode, picture
Requires at least: 2.5
Tested up to: 3.2.1
Stable tag: 0.7

Divides your wordpress image gallery into several pages.

== Description ==

It may be useful when you need to attach many images to the same post. This plugin divides your wordpress gallery into several dynamic pages. It can works in two ways: modifying the `[gallery]` shortcode output or do not affect it when you use `[pgallery]` shortcode instead. The following settings of the plugin are available: number of images per page, hide or show edit attachment link, and the same as in standard wordpress gallery: number of columns, preview size, link from preview, tags of items, captions and previews, sort order, exclude some attachments.

Note that if you set the plugin to [pgallery] mode then you may use both [gallery] and [pgallery] shortcodes in your blog. For instanse, you may apply another plugin to replace standard [gallery] output. When activating first, the Paged Gallery plugin is set to [gallery] mode.

== Installation ==

1. Upload `paged-gallery` folder to the plugins directory (usually `/wp-content/plugins/`).
2. Activate the plugin through the 'Plugins' menu in WordPress, then 'Paged Gallery' settings link appears in the Mediafiles submenu.
3. Set desirable options of the plugin at the Paged Gallery settings page.

== Frequently Asked Questions ==

= How to paste images into a post/page? =

Just write `[gallery]`, or `[pgallery]` (without quotes) in your post, or specify individual settings:
[gallery perpage="12" columns="3" link="attachment" id="123"]
(where 123 is ID of another post with attachments), or
[gallery perpage="20" columns="4" itemtag="div" icontag="span" captiontag="p" link="file"]

= How to exclude some images from gallery? =

Sample: `[pgallery exclude="10,11,12"]` where 10, 11, 12 are the IDs of images.

== Changelog ==

= 0.7 =
* Exclude one or several attachments works now.
* Corrected a bug in dynamic pages links.

= 0.6 =
* Istallation from WordPress blog admin panel available now.

= 0.5 =
* First public version.

== Sample ==

Here is a sample of using this plugin [Chernihiv Photos](http://andrey.eto-ya.com/chernihiv-photo
"Chernihiv: city and historical places").
