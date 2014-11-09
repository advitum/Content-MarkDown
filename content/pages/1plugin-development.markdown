/**
 * Author: Lars Ebert
 * Date: 2014/11/09
 * Title: Plugin development
 * NavTitle: Plugin development
 */
Plugin development
==================

To create a plugin for Cmd, you first have to think of a good name. The name can consist of only letters, the first one being uppercase.

Then you create a folder with that name inside the plugins folder, containing at least a php file with the same name. This will be the main file of your plugin.

Inside that file, you create a new class with the same name, extending the class Advitum\Cmd\Plugin.

    <?php
        
        class Gallery extends Advitum\Cmd\Plugin
        {
            public function frontend() {
                
            }
            
            public function backend() {
                
            }
        }
        
    ?>

At the very least, your class will need a frontend function, which will be used to generate the frontend content of the plugin. You can also optionally define a backend function which will cause the plugin to be displayed in the backend, possibly giving the user configuration option.

Both functions should return the HTML (or markdown) content they produce. Do not output anything directly.

To add stylesheets or scripts to the frontend or backend, use the addStylesheet and addScript functions of the Advitum\Cmd\Layout class in the frontend function and of the Advitum\Cmd\Admin class in the backend.

To access the database, you can use the Advitum\Cmd\DB class. The class is already connected to the database. Look at the file core/DB.php for more information on that.