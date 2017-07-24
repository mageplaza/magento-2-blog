# Blog User Guide


## Documentation

- Installation guide: https://www.mageplaza.com/install-magento-2-extension/
- User guide: https://docs.mageplaza.com/blog-m2/index.html
- Download from our Live site: https://www.mageplaza.com/magento-2-blog-extension/
- Get Support: https://github.com/mageplaza/magento-2-blog-extension/issues
- Contribute on Github: https://github.com/mageplaza/magento-2-blog/
- Changelog: https://www.mageplaza.com/changelog/m2-blog.txt
- License https://www.mageplaza.com/LICENSE.txt

## FAQs

#### Q: I got error: `Mageplaza_Core has been already defined`
A: Read solution: https://github.com/mageplaza/module-core/issues/3

#### Q: My site is down
A: Please follow this guide: https://www.mageplaza.com/blog/magento-site-down.html



## How to install

### Method 1: Install ready-to-paste package

- Download the latest version at [Mageplaza Blog for Magento 2](https://www.mageplaza.com/magento-2-blog/)
-  [Installation guide](https://docs.mageplaza.com/kb/installation.html)

### Method 2: Install via composer

Run the following command in Magento 2 root folder

```
composer require mageplaza/magento-2-blog-extension
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

### Method 3: Manually install via composer

1. Access to your server via SSH
2. Create a folder (Not Magento root directory) in called: `mageplaza`, then upload the zip package to mageplaza folder.
Download the zip package at https://github.com/mageplaza/magento-2-blog/archive/master.zip

3. Add the following snippet to `composer.json`

```
	{
		"repositories": [
		 {
		 "type": "artifact",
		 "url": "path/to/root/directory/mageplaza/"
		 }
		]
	}
```

4. Run composer command line

```
composer require mageplaza/magento-2-blog-extension
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
