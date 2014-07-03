Launchpad Installation
======================

If you haven't installed WordPress yet, you need to do that before trying to install Launchpad.

First, download Launchpad from github by navigating to the [Launchpad Repository](https://github.com/robertbrodrecht/launchpad/), selecting the branch you need, then clicking the "Download Zip" button on the right sidebar.  As versions are completed, I intend to create a stable branch for each version and develop new features on the master branch.  Once the download completes, unzip it and drop it in your theme folder.

If you are a CodeKit user, launch CodeKit and drag the theme folder onto CodeKit.  Click the gear icon and update the following settings if needed:

1. Languages > Sass: Output style should be "compressed" for production sites and file output should be "../css" relative tot he source file's folder.  If you find that the CSS output is not compressed, edit config.rb and set <code>output_style = :compressed</code>
2. Languages > Javascript: Make sure the file suffix is "-min" and that the output is to the same folder as the source file.
3. Minifiers > Ulgify.js: I highly suggest unchecking the "mangle" option.

Once CodeKit is configured, click the gear icon again, then process / compile the following:

1. sass/admin-style.scss
2. sass/editor-style.scss
3. sass/print.scss
4. sass/screen.scss
5. sass/unsupported.scss
6. js/admin.js
7. js/main.js

If you are not a CodeKit user, figure out how to do what you need to do.

Next, log into your WordPress admin and navigate to Appearance > Themes and activate Launchpad.  If you are using a child theme of Launchpad, activate the child theme and good luck!

Activating Launchpad will delete the page and hello world post, as well as perform the following actions unless you have set up alternate values via hooks in your custom functions file:

1. Set the post permalink structure to "/articles/%postname%/."
2. Disable comments and pingbacks/trackbacks.
3. Clear the blog description if it is still set to "Just another WordPress site."
4. Create a page called "Home" if it does not exist.
5. Create a page called "Articles" if it does not exist.
6. Set the "Front page displays" to "A static page" with "Home" as the front page and "Articles" as the posts page.
7. Change the upload path to /assets/.
8. Generate .htaccess rewrite rules.

Once this process is complete, you will be sent to the [Launchpad settings screen](settings.md).