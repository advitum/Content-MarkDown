/**
 * Author: Lars Ebert
 * Date: 2015/01/31
 */
Content MarkDown - free and simple markdown content management
==============================================================

This is the demo page for Content MarkDown. Content MarkDown (Cmd) is a simple content management system that lets you manage your website's content through markdown. It comes with an easy to use backend and can also be managed through the file system, for example via FTP.

Cmd has a powerful plugin interface and comes with some ready to use core plugins.

This demo website leads you through Cmd's functions. If you have any questions, send me an email to <info@advitum.de>.


Format your content with markdown
---------------------------------

Markdown is a very simple markup language. It is not a replacement for HTML, it is meant to be as easy to write and read as possible. The syntax of markdown is designed so that the markup actually looks like it's meaning.

Look at this example:

    This is a headline
    ==================
    
    This is some simple text. Just your basic paragraph.
    
    This is a sub headline
    ----------------------
    
     * Here comes a list
     * and it really looks like one, too!
    
     1. This is a numbered list
     2. See how simple it is?

See? Markdown itself already looks structured and is easy to read. Here you can find the [complete markdown syntax][1].


Build your website structure on the file system
-----------------------------------------------

In the default setup of Cmd, your content will be stored in the content folder.

 * **admin/**: In this folder, the backend styles are stores. Basically, you do not have to worry about it.
 * **css/** and **img/**: These folders are exactly what they sound like. You can store css and image files in there. Notice that you have to place an .htaccess file in every of these folders that you want to be publicly accessible. By the way, you can more folders, for example pdf.
 * **layouts/**: Here you can store multiple layouts that you can choose in your content files. Right now there is only the default.tpl, which is used by default.
 * **pages/**: In this folder, you build your website structure.
 * **scss/**: The scss folder is just there because I compiled the css of the default theme with [Compass][2]. Feel free to remove it.

The most important folder is the pages folder. Here you will store your content. Your website's structure will consist of folders and files, the files all ending on ".markdown".

Every folder (including the pages folder itself) has to contain a file named "index.markdown", which contains the content of that folders correspondig page. So if on your website, you access the page "/about/me", the content can be found in the file "content/pages/about/me.markdown", or "content/pages/about/me/index.markdown" if the page has sub pages.

If you want a page to have sub pages, make it a folder and move it's content into the "index.markdown" inside it. Then you can place the sub pages inside the folder.

To make a file or folder inaccessible, simply prepend it's name with an underscore. So a file named "content/pages/about/_me.markdown" will not be accessible through the frontend.


Which pages are going into the navigation?
------------------------------------------

By default, none of your files are included into the navigation except the home file. If you want a file or folder included in the navigation, simply prepend it's name with an integer. Be aware that the parent folders also have to be prepended with a number. So your file structure could look something like this:

 * 1about/
   * 1me.markdown
   * 2work.markdown
   * index.markdown
   * more.markdown
 * 2download.markdown
 * index.markdown
 * media.markdown

In this example, your navigation would look like this:

 * Home
 * About
   * Me
   * Work
 * Download

Notice that index files never appear in the navigation and must not be prepended with an integer.

To include the navigation in a layout or page, simply place &lt;cmd:navigation \&gt; inside it. This will render the navigation at that position. To remove the home link from the navigation, write &lt;cmd:navigation home="false" /&gt;.

At the beginning of each page file, you can add a comment with meta information about the page, for example:

    /**
     * Author: Lars Ebert
     * Date: 2014/11/09
     * Title: Title of the page
     * NavTitle: Page X
     */
    Real content...

Note that that all is optional, but if the title is provided, it is used for the pages title tag, the navtitle is used for the title of the page in the navigation. You can also provide the name of a layout (minus the .tpl extension) that will be used for the page instead of the default layout.

Using plugins
-------------

To place a plugin inside a layout or page, simply write the plugin name in curly braces.


Building a layout
-----------------

A layout is basically a html file. This is a very simple layout file:

    <!DOCTYPE html>
    <html>
    <head>
        <cmd:head />
    </head>
    <body>
        <cmd:navigation />
        
        <cmd:message />
        <cmd:content />
        
        <cmd:foot />
    </body>
    </html>

Notice the tags <cmd:head />, <cmd:foot /> and <cmd:content />! These are required so Cmd can insert the content, stylesheets, scripts and meta information.

Additionally, you may place the tags <cmd:message /> and <cmd:navigation /> and <cmd:plugin /> inside the layout.

But basically, you can put any HTML around those placeholders, whatever you need for your layout.

Stylesheets and scripts can be added in the configuration file of your website.


Configuring the website
-----------------------

The file content/config.php contains your website's configuration. First, you can define the constants LONG&#95;TITLE and SHORT&#95;TITLE. The long title will be used as the title if no title is provided by the page. The short title will be appended to the title otherwise.

You also have to configure the database connection. That is what the four DATABASE_* constants are for. Or you use the new setup tool added in version 0.0.6. If no config file is present, it will guide you through creating one.

Additionally, you can add stylesheets and scripts to the layout. For that, use the static methods addStylesheet and addScript of the Layout class. For example:

    Layout::addStylesheet('main', CSS_URL . '/main.css');
    Layout::addScript('jquery', JS_URL . '/jquery.min.js');
    Layout::addScript('main', JS_URL . '/main.js', array('jquery'));


Displaying images
-----------------

You can place images anywhere in your content through the markdown syntax.

    ![Alt text](/content/img/path/to/img.jpg "Optional title")

Additionally, ContentMarkDown comes with a simple way to scale, crop, grayscale and even tint your images without ever touching the original image on the server. Simply prepend the path to AutoImg and your configuration to the image url:

    ![Alt text](/content/autoimg/index.php?p=w500-h500/img/path/to/img.jpg "Optional title")

The configuration parameters in this case are w500 and h500, which means that AutoImg will resize your image to a maximum width and height of 500 pixels. The ratio of the image is not changed, you might end up with an image of 500x180px or 350x500px, depending on the original ratio. But neither width nor height will be bigger than 500 pixels.

To crop the image to the exact specified ratio, add the parameter c:

    ![Alt text](/content/autoimg/index.php?p=w500-h500-c/img/path/to/img.jpg "Optional title")

Your image will now be 500x500px big. Notice that cropping obviously only works when both width and height are specified.

To grayscale the image, add the g parameter, for example (this time without caring about the size):

    ![Alt text](/content/autoimg/index.php?p=g/img/path/to/img.jpg "Optional title")

To tint your image with a certain color, use the t parameter with a hex color, omitting the #:

    ![Alt text](/content/autoimg/index.php?p=tff0000/img/path/to/img.jpg "Optional title")

Notice that all these parameters are combinable.


Backend
-------

You can get to the backend by navigating to the page /admin. The default admin password is "admin". You should log in right now and change the password!

In the backend, you can edit, create and delete folders and pages and edit, create and delete users. Note that only the admin user has access to the user management.

Some plugins might present you with a backend page of their own. You can see these in the backend menu listed under "Plugins". If you can not find the plugins menu, there are simply no plugins defining any backend page.

That is about all there is to tell about managing content with Content MarkDown. Go ahead and edit this page to contain your own content!


License
-------

Content MarkDown is released under the [MIT License][3].



[1]: http://daringfireball.net/projects/markdown/syntax "Markdown Syntax"
[2]: http://compass-style.org/ "Compass"
[3]: http://opensource.org/licenses/MIT "MIT License"