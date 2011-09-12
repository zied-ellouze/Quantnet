=== SEO Pagebar 2 WordPress Plugin ===

Contributors: Oliver Bockelmann (fob marketing) and Sebastian Schmiedel (flexib Webcoding)
Donate link: none
Tags: SEO, nofollow, follow, pagenavi, pagebar, pagination, plugin, links, page, navigation, navi
Requires at least: 2.3
Tested up to: 2.7
Stable tag: trunk

The plugin provides a more advanced SEO pagination for WordPress to be configured with several options.

== Description ==

The plugin provides a more advanced SEO pagination for WordPress to be configured with several options.

= GERMAN: = 

* [SEO Pagebar German](http://www.flexib.de/seo-pagebar-21/ "SEO Pagebar - Deutsch"): Sebastian Schmiedel

= ENGLISH: =

* [SEO Pagebar English](http://www.fob-marketing.de/marketing-seo-blog/seo-pagebar-21-update.html "SEO Pagebar - English"): Oliver Bockelmann


The plugin is licensed under GPL. 
Everything is free - No warranties, no guarantees, no support. 

== Installation ==

= How to install the SEO Pagebar Plugin: =

* Create a folder wp-content/seopagebar and upload the seo pagebar plugin files into this folder -> activate the plugin -> configure your options page -> modify your template a bit ... and you are ready to go!


= Theme modification: =


* Put this little code into your template where ever you want your SEO Pagebar start working. 

In Kubrick Theme index.php and achive.php could be a good place to start. 

Just have a look for this code: 


&lt;?php endwhile; ?&gt;
<p> &lt;div class=&quot;navigation&quot;&gt;<br>
&lt;div class=&quot;alignleft&quot;&gt;&lt;?php next_posts_link(__('&laquo; Older Entries'), 'kubrick'); ?&gt;&lt;/div&gt;<br>
&lt;div class=&quot;alignright&quot;&gt;&lt;?php previous_posts_link(__('Newer Entries &raquo;', 'kubrick')); ?&gt;&lt;/div&gt;<br>
&lt;/div&gt;</p>
<p>&lt;?php else : ?&gt;<br>
</p>
<p>...and change it like that: <br>
</p>
<p>&lt;?php endwhile; ?&gt;<br>
    <br>
&lt;?php if (function_exists('seopagebar')) { seopagebar(); } else { ?&gt;</p>
<p> &lt;div class=&quot;navigation&quot;&gt;<br>
&lt;div class=&quot;alignleft&quot;&gt;&lt;?php next_posts_link(__('&laquo; Older Entries'), 'kubrick'); ?&gt;&lt;/div&gt;<br>
&lt;div class=&quot;alignright&quot;&gt;&lt;?php previous_posts_link(__('Newer Entries &raquo;', 'kubrick')); ?&gt;&lt;/div&gt;<br>
&lt;/div&gt;<br>
  <br>
&lt;?php } ?&gt;</p>
<p>&lt;?php else : ?&gt;</p>

== Screenshots ==

none

== Frequently Asked Questions ==

none