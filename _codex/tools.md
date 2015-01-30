<center>[Previous](settings.md) | [Home](index.md) | [Next](site-images.md)</center>

Launchpad Tools
===============

Launchpad includes helpful tools so that you don't have to download a bunch of plugins as soon as you install WordPress.

## Image Optimization

If you have image optimization software on your computer like jpegtran, jpegoptim, pngout, optipng, or pngcrush, Launchpad will automatically try to losslessly optimize your newly uploaded or newly regenerated images.  You don't have to do anything on your part to make it happen.

## Regenerate Thumbnails

Adding a new image size halfway though a project is pretty common.  So, Launchpad has thumbnail regeneration built in.  Under the tools menu, click Regen Thumbnails.  Click the "Start Regenerating Thumbnails" and Launchpad will go through your media library, delete old thumbnails for each image, and generate new ones based on the original image.

## Database and Asset Migration

Note: Database and Asset Migration assumes both sites are root-level installs and are both using the same version of Launchpad.  This tool is VERY, VERY beta.  It has not been tested on production sites at the time of this writing (version 1.5), so use EXTREME caution if you decide to use this.

Using your local install as a hub, Launchpad allows you to migrate the WordPress database and assets.  To migrate, you must log into the remote site as well as the local site.  On the remote site, go to Tools > Migrate and copy the communication key.  Next, go to your local site and go to Tools > Migrate.  Enter the full root URL and the communication key, then click "Verify." 

If your settings passed verification, select whether you want to push or pull data, whether you want to update attached files, and pick what tables to replace. When you're done, click "Start Migration" and wait.  Launchpad will truncate each table and copy data over.  If a post is an attachment and you opted to update files, the files will be uploaded / downloaded and resized remotely via the same code that handles regenerating thumbnails.

## Media Replace

It's annoying when a file that is linked all over your website needs to be updated.  You can either upload a new version and comb through the site updating links or you can break out your FTP client and overwrite the file.  Either way, it's a pain.  Launchpad adds Media Replace to help with those situations.  Simply find the file in the Media Browser, edit it, and use the Media Replace metabox to upload a new file to overwrite the old file.

Next, you might want to generate default [site images](site-images.md).