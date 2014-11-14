/**
 * Author: Lars Ebert
 * Date: 2014/11/10
 * Title: Core Plugins
 * NavTitle: Core Plugins
 */
Core Plugins
============

There are a number of core plugins already inside Content MarkDown. This is a complete list of these plugins.


Gallery
-------

The gallery plugin provides a simple way to display multiple images in a lightbox gallery. Simply put the images into one folder inside content/img and place the plugin whereever you want:

    <cmd:plugin plugin="Gallery" folder="img/folder" />

The images are automatically cropped and resized by Content MarkDown. Go ahead and test it!


Contact
-------

The contact plugin lets you place a contact form in your pages.

    <cmd:plugin plugin="Gallery" from="noreply@example.com" to="me@example.com" />

This will place a contact form in the page letting the user send emails to me@example.com.


Map
---

You can use the map plugin to display a google map on your website.

	<cmd:plugin plugin="Map" markers='[{
		"title": "Example",
		"position": [51.248855, 7.627476]
	}]' options='{
		"panControl": false,
		"styles": [{
			"stylers": [{ "saturation": -100 }]
		}]
	}' />

The markers attribute is mandatory, as it defines the position and markers of the map. The options attribute is optional but very useful.