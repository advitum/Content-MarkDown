/**
 * Author: Lars Ebert
 * Date: 2014/11/10
 * Title: Core Plugins
 * NavTitle: Core Plugins
 */
Core Plugins
============

There are a number of core plugins already inside Content MarkDown. Right now that number is 2. But there will be more.


Gallery
-------

The gallery plugin provides a simple way to display multiple images in a lightbox gallery. Simply put the images into one folder inside content/img and place the plugin whereever you want:

    &#123;Gallery|folder:img/folder&#125;

The images are automatically cropped and resized by Content MarkDown. Go ahead and test it!


Contact
-------

The contact plugin lets you place a contact form in your pages.

    &#123;Contact|from:noreply@example.com;to:me@example.com&#125;

This will place a contact form in the page letting the user send emails to me@example.com.
