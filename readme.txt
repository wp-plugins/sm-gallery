=== SM Gallery ===

Contributors: sethcarstens, sethmatics
Donate link: http://sethmatics.com/extend/plugins/sm-gallery
Tags: gallery, featured image, image, media, plugin, page
Requires at least: 3.7
Tested up to: 4.2
Stable tag: 1.1.8

Gallery plugin that's simple because it leans on existing WordPress gallery features provided by http://sethmatics.com/.

== Description ==

Gallery plugin that's simple because it leans on existing WordPress gallery features provided by http://sethmatics.com/.

NEW TO WORDPRESS? Please read about how the default gallery works at http://codex.wordpress.org/Gallery_Shortcode

We decided that the default gallery that WordPress builds is lacking some very simple features. 
Enhance your gallery's without having to learn a completely new interface! Upload images as you
normally would, insert the "gallery" tag as you normally would, with the plugin enabled BAAM!
Your gallery is not in a beautiful showcase. Wait, don't have room on your page for a showcase?
No problem, we got you covered! Ad a simple attribute (modal) to the gallery code and define a 
thumbnail image... now you have a thumbnail that opens an overlay box on the page with your gallery showcase
in it. No, it doesn't navigate away from the current page, its very similar to a lightbox effect.

But wait, you want more you say? Oh you mean you use the featured image enhancement from WordPress?
Good news, we even offer you the ability to link your featured image to your gallery showcase. It 
will cause the featured image, when clicked, to open the sexy overlay box for you. Snazzy!

Great.... so now I have to worry about 10 more javascript libraries on my page... Nope! The only 
scripts we are loading on the page are jQuery UI and small gallery plugin for jQuery. This means it
shouldn't conflict with any of your other site features. Well, if your not sold yet, just try it out
and see for yourself how quick and easy it is to extend the gallery WordPress features.

Features include:

* uses the existing WordPress gallery shortcode to keep inserting galleries easy and simple
* tiny plugin, overhead media files and scripts are minimal
* scripts optimized to be loaded at proper times to keep page load time down
* extends featured images to link them to a gallery of your choosing
* built as part of the SM plugins group which is being compiled to optimize page load speeds when using SM plugins together by minimizing files loaded on the site to 1 library. Stop loading 50 stylesheets and 39 javascript files, instead using the SM Plugins will pull from a single library and detect whats been loaded to keep you site load time down.

Features Coming in version 2, Next Version Updates:

* javascript post-load images upon visibility to reduce page load time
* modal box will load its contents with AJAX to reduce page load time even more
* some styling options inside the wordpress interface (currently only available using CSS)
* ability to select more plugin options while inserting the gallery from the WordPress media popup
* ability to select an existing image from the media gallery for use as a gallery thumbnail image

Don't forget to rate our plugin so we know how we are doing!

== Installation ==

To install the plugin manually:

1. Extract the contents of the archive (zip file)
2. Upload the sm-gallery folder to your '/wp-content/plugins' folder
3. Activate the plugin through the Plugins section in your WordPress admin
4. Upload photos to a page or post.
5. Insert a [gallery] into a page or post, or use the "Featured Image Gallery" tools to activate the gallery on the featured image.

== Changelog ==
- added filters and moved code into classes for easier extensibility
- Changed featured image exclusion to be optional. Defaults to false.
- Cleaned up debug code that was not needed and commented plugin code to extend the use of the plugin for other developers.
- Corrected missing jQuery UI issue and styling issues

== Upgrade Notice ==
Version 1.1.5
Had a bug, and doesn't work. Please do not use this version.

Version 1.0.2
Requires at least WordPress version 3.1.

== Frequently Asked Questions ==

Q: How do I use the plugin? How do I get a gallery into my page?
A: The same way you would have before you installed the plugin! We tried to keep it simple, simply upload a handfull of images to your page and then choose the "insert gallery" option from within the WordPress media popup box. This will insert some code onto your page that looks like this, [gallery], then update and view the page to see how the plugin has enhanced the gallery features within WordPress.

Q: What parameters does the shortcode accept?
A: Setting the modal parameter to false will add the gallery directly on the page, setting it to true will allow you to create a clickable thumbnail with a gallery that opens in a modal box. Setting the post_id parameter will pull the gallery from a specific post, leaving it blank will use the id of the current post. Title can be used to set a title. Thumbnail is used to set the sorce of the thunmbnail image.  The thumb_class parameter can be used to add a class to your thumbnail image. Example [gallery modal="true" post_id="1234" title="My Slideshow" thumbnail="/wp-content/my-theme/images/my-thumbnail.png" thumb_class="gallery-thumb"].  

Q: What is a modal box and why do I care?
A: A modal box is a "pop-up" that loads inside of your webpage without navigating the URL. This means pop-up blockers won't effect it, and users won't actually leave your website or URL to view the contents of the popup. Its more like an "overlay" that sits on top of your webpage until its closed. Why do you care? Modal boxes are becoming more and more popular to supply additional content and information to visitors that wouldn't normally fit on the webpage.

Q: Where is the options panel?
A: There is no options panel. Currently the only options are related to the featured image and those are availble in the sidebar of the "edit post" and "edit page" webpages within your wp-admin.

== Screenshots ==

1. Sample of the gallery, which looks similar inline to what you see in this popup modal box.
2. Sample of the "featured image gallery" options which links your featured image to a gallery popup.