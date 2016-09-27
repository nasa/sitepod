# Setup & Usage

Make shure you have installed this script as written in the [installation guide](./installation.md)

Follow theses steps to generate your sitemap:

1. Setup phpSitemapNG
2. Run phpSitemapNG
3. Edit the result
4. Store sitemap
5. Inform Google
6. Watch your website within Google

Setup

Execute the script with your browser: browse to [http://podaac.jpl.nasa.gov/admin/sitemap/](http://podaac.jpl.nasa.gov/admin/sitemap/).
You’ll get a screen like this:

...


Set the settings according to the folling cases
Common settings:

    Check the settings in Page root (this is the path on the webserver where your files are stored) and the Website (the url of your website) and maybe correct them if necessary.
    Exclude directories – this directories will not be scanned for urls;
    Example: to exlude all files in the directory img but not images, add /img/ to the list of directories; there is only a substring comparison performed
    Exclude files – urls containing strings that are in this list, will neither be added to the sitemap nor crawled for urls

Please choose the item that matches your website and set the settings accordingly:

I have a small website with less than 500 files

Make the following settings:

    Check Ping google
    Uncheck compress sitemap
    Check Scan website
    Check Display edit screen after scan
    Check Store filelist
    Adapt Lastmod, Priority and Changefreq to your needs

With this settings, phpSitemapNG will crawl your website and finally give you an edit screen where you can adapt the values.

I have a bigger website with more than 500 files

Make the following settings:

    Check Ping google
    Check compress sitemap
    Check Scan website
    Uncheck Display edit screen after scan
    Uncheck Store filelist
    Adapt Lastmod, Priority and Changefreq to your needs

With this settings, phpSitemapNG will crawl your website and will write the result direct into the sitemap file.

There will be no edit screen since this can slow down or kill your browser. The created sitemap file will be compressed (if available). Check the expert settings to get some background information about the timeout functionality and the crawler if you’re running into problems when saving the settings and executing the scan.

Detailed information about the settings available

    Ping google – inform Google when a sitemap has been created
    Timeout functionality – phpSitemapNG allows you to set the time how long it will perform the actions. This is necessary if you’re running a big website with many urls, but your webhoster allows you to execute php scripts only for a short amount of time.
    There are two different timeouts that might occur:

        PHP timeout: The PHP engine stops the exection and prints an error at the end of the script.
        Solution: Just press the Setup link, enable the timeout and type in the average time to the timeout (normally 30 seconds) minus 5 seconds (backup) time. 🙂 Now the browser is forced to reload the page with javascript (if not enabled, please do it here!) when a timeout occurs.
        Webserver timeout: The webserver stops the output thread and does not print an error at the end of the script.
        Solution: Just press the Setup link, enable the timeout and type in the average time to the timeout (normally 300 seconds) minus 5 seconds (backup) time. 🙂

Run phpSitemapNG

Press the Submit Settings button to store the settings and start the scan of your website.

Edit the result

If you checked the “Display edit screen after scan” checkbox, you’ll see a page like this:

...

You can now adopt the settings to your needs. Press the “Create file” button to use this information for generation of the sitemap file.

Store sitemap

When you’ve pressed the button “Create file” in the result overview page the sitemap will be generated and written to the sitemap file specified at the settings page.

Inform Google, Yahoo and Bing

When the sitemap has been successfuly written to the filesystem you’ll get the option to inform Google. Just press the “Submit to google” button.
Is this your first sitemap Google suggests to submit the sitemap within a Google Sitemaps account. You can get one at http://www.google.com/webmasters/sitemaps/. This is the only possibility to track the status of the Google sitemap at the moment – so please do so.
The message given by phpSitemapNG does only mean that Google got the url of the sitemap, not that it successfuly downloaded and computed the sitemap.

Watch your website within Google

That’s it, you’ve successfuly created your Google sitemap.
So track the impact of Google Sitemaps you can query Google about the number of indexed pages: Type site:enarion.net (modify the website after site: to match your website). Then you’ll see the number of indexed pages: Results 1-… of about x from … Where x is the number of pages of your website that is integrated in the Google Search index at the moment. This number can differ depending on your location because of the not sychronized Google index.
Maybe you have some webpages in the result without description, only the url of the webpage. This means that Google got these urls (from the sitemap file) but does not have crawled, computed or added of this webpage to its index. It normally takes 1-5 days than there will be a description.
