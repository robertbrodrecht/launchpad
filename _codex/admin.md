<center>[Previous](js.md) | [Home](index.md) | [Next](seo.md)</center>

Launchpad Admin Modifications
=============================

Launchpad makes several modifications to the WordPress admin.  Most of them have been covered in previous sections:

* Launchpad Settings: [Settings Section](settings.md)
* Custom Metaboxes, Flexible Content, and Help: [Post Types Section](post-types.md)
* Adding Fields to Launchpad Settings, Relocating Launchpad Settings, etc: [Launchpad Hooks Section](hooks.md)
* Where to add additional admin styles if you are brave: [SASS Section](sass.md)

With that knowledge, you are armed with everything you need to know.  I think it's apt to point out here, again, that you can add help to any post type, metabox, flexible content, or field that is built through Launchpad's post types to add help menus for your user.

## Media Tools

Launchpad adds two commonly needed media tools:

### Media Replace

Media replace can be found by editing the media in the Media Library.  Using the "Media Replace" metabox below the "Save" metabox, you may attach a new file that will replace the current file.  On save, the uploaded file is renamed to what the previous file was named, so it is important that you only replace files with a file of the same type.  If you are replacing an image, all the thubmnails will be replaced, too.  Note that this keeps an additional entry in the media library for the replacement file apart from the replaced file.

### Regenerate Thumbnails

Very often in the process of development, a developer will add or change an image size dimension.  To help with this inevitability, Launchpad has thumbnail regeneration baked in.  Once you've made your change, go to Tools > Regen Thumbnails.  After clicking "Start Regenearating Thumbnails," each image in your media library will have all thumbnails deleted and regenerated from the original file.  The process is done one file at a time, so you can cancel at any point to stop the process.

I thought I had more to say here, but I can't remember what I thought was so important.  So, maybe start reading about [Launchpad's SEO](seo.md).