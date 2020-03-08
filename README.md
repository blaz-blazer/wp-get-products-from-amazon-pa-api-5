# WP Get Products From Amazon PA API 5

WP Get Products From Amazon PA API 5 allows you to easily add products from Amazon PA API 5 to your website. The plugin retrieves product title, image url, features and affiliate link from the API. You can then embed the product to your posts with the shortcode [gpfa asin="productAsin"] where productAsin is asin number of Amazon product.

## Installation

1. Navigate to the [releases](https://github.com/blaz-blazer/wp-get-products-from-amazon-pa-api-5/releases) page of the repository.
2. Download the latest release zip file
3. Rename the zip folder to wp-gpfa.zip
4. Rename the folder in the wp-gpfa.zip folder to wp-gpfa
5. On your WordPress website navigate to the plugins section and upload the plugin (wp-gpfa.zip)
4. Click Activate
5. Click Settings -> WP Get Products From Amazon PA API 5 in the main menu and configure the plugin
6. Add shortcode [gpfa asin="asinNumber"] where asinNumber is asin number of Amazon product.
7. Optional but recommended: Create a folder wp-gpfa in your child theme and copy template from wp-content/plugins/wp-gpfa/public/templates/basic-template.php to the wp-gpfa folder in your child theme. Edit the template to your liking.

## Requirements & Compatibiltiy

* WordPress Version 4.3.0 or higher
* PHP Version 5.3 or higher
* Tested up to: WordPress Version 5.4

## Features

1. Smart Cache - it allows you to store products information in the database rather than retrieving it from the API every time a page loads. Products in the database are then updated via CRON task which prevents throttled requests from the API and increases page speed.
2. Cache Refresh Interval - cached products are updated via CRON job daily, twice daily or hourly. You select the interval in the settings.
3. Custom Templates - while the plugin comes with a very basic template, it is recommended that you create your own template which fits your needs and website. How to create a custom template is explained in the [documentation](https://blazzdev.com/documentation/wp-get-products-from-amazon-api/).
4. Retrieve product information from whichever locale via filters. For more information see the [documentation](https://blazzdev.com/documentation/wp-get-products-from-amazon-api/).

## Want to contribute to WP Get Products From Amazon PA API 5?

### Getting started

Within your WordPress installation, navigate to wp-content/plugins and run the following commands:

```
git clone https://github.com/blaz-blazer/rate-my-post.git
cd rate-my-post
```

## Disclaimer

This plugin is NOT associated with Amazon in any way.

## Author

* **Blaz K.** - [BlazzDev](https://blazzdev.com/)

## License

This project is licensed under the GPLv3 License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone whose code was used
