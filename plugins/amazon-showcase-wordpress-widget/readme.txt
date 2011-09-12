=== Amazon Showcase Wordpress Plugin ===
Contributors: forgueam
Donate link: http://www.aaronforgue.com
Tags: Amazon, widget, plugin, books, products, money, library, affiliate, 
Requires at least: 2.7
Tested up to: 2.8.4
Stable tag: 2.2

A plugin for showcasing items from Amazon. Simply enter the ASIN/ISBN number of any product and optionally enter an Associate ID.

== Description ==

Amazon Showcase is a Wordpress Plugin for showcasing items from Amazon. Simply enter the ASIN/ISBN numbers of any products and optionally enter an Associate ID for earning commissions. The product image will be displayed with a link to the product detail page on Amazon.com. More advanced users can have full control over the way the products are displayed. Showcases can be used as widgets, placed in posts/pages, or inserted directly into your template.

== Installation ==

1. Unzip and place the 'amazonshowcase' directory in the '/wp-content/plugins/' directory on your web server
2. Activate the 'Amazon Showcase' plugin from within the 'Plugins' section of your Wordpress administration area
3. You should now have access to the plugin settings via 'Settings > Amazon Showcase'

== Frequently Asked Questions ==

= What configuration options are available? =

There are several attributes available in each showcase: Showcase Name, Associate ID, Locale and Item ASIN/ISBNs & Image Sizes.

**Name**
Use this attribute for naming a particular showcase. The text entered here will be used to reference the showcase.

**Associate ID**
This is is an optional field that allows Amazon Associates to enter their Associate ID. When you supply an Associate ID, it is automatically inserted appropriately into the item's link. This allows you to earn commissions when people click on the item and purchase it through Amazon.

**Locale**
The locale field allows you to select which of the international Amazon websites you would like to pull items from.
You have 6 options:
* United States (.com)
* United Kingdom (.co.uk)
* Germany (.de)
* Japan (.co.jp)
* France (.fr)
* Canada (.ca)

**ASIN/ISBN**
Use this field to define the Amazon product you would like to display in the showcase. This can be any item from the Amazon catalog of the locale you selected. Simply locate the item's ASIN or ISBN and enter it in one of these fields.

Exmaples:
ASIN: B000I1ZWRC
ISBN: 1582345236

**Image Size**
Amazon usually provides five different product image sizes: swatch, small, tiny, medium and large. Select the image size that you would like to use within your showcase.

**Template**
Use the template field to define the HTML that is generated for each product. There are several pre-defined 'tags' that you can used as placeholders for product information. For example, any instance of the tag '[author]' in the template will be replaced by the product's author name when outputted.
Available tags:
* [title] - The title of the product
* [author] - The author(s) or creators of the product, if available
* [url] - The URL for the product's detail page on Amazon.com
* [image] - The HTML for the product image (fully formed <img> tag)
* [image_url] - The URL of the specific image size you selected
* [image_width] - The width (in pixels) of the specific image size you selected
* [image_height] - The height (in pixels) of the specific image size you selected

= Can I create multiple Amazon Showcases on my site? =

Yes, you can create as many showcases as you like and each showcase can contain an arbirary number of Amazon items.

= Is it possible to change the style of the output? =

I've tried to make the output of the showcases as clean and customizable as possible. Using CSS, you should be able to customize the look and feel of the item display. Additionally, you may customize the output of each showcase to your specifications by using the optional 'Template' field.

This is the default structure of the HTML that is produced:

`<div class="amzshcs" id="amzshcs-[showcaseIdentifier]">
  <div class="amzshcs-item" id="amzshcs-item-[itemIdentifier]">
    <a>
      <img />
    </a>
  </div>
  <div class="amzshcs-item" id="amzshcs-item-[itemIdentifier]">
    <a>
      <img />
    </a>
  </div>
  <div class="amzshcs-item" id="amzshcs-item-[itemIdentifier]">
    <a>
      <img />
    </a>
  </div>
</div>`

== Screenshots ==

1. Configuring Amazon Showcase
2. Sample output
