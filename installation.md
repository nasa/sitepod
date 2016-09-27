# Installation

 * [Download the current release](https://github.com/lewismc/podaac_sitemap/archive/master.zip) and store it on your harddisc
 * Create a directory on your webserver (suggestion: /admin/podaac_sitemap) and protect it with a .htaccess file
 * Extract the archive you downloaded and copy the files to the directory created above, copy sitemap.xml and sitemap.xml.gz into the root directory of your website.
 * Make the following files writable (chmod 0666):
   * [/sitemap.xml]() (- or /sitemap.xml.gz for compressed sitemap)
   * [/sitemap.txt] (if you would like to write txt sitemaps files)
   * [settings/settings.inc.php] (to store your settings)
   * [settings/files.inc.php] – (store information about generated sitemap; only useful for small websites)
    
...That’s it, you can now proceed [using the sitemap generator](./usage.md).


