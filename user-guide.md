# Blog User Guide


- User guide: https://docs.mageplaza.com/blog-m2/
- Contribute on Github: https://github.com/mageplaza/magento-2-blog/
- Get help: https://github.com/mageplaza/magento-2-blog-extension/issues
- License https://www.mageplaza.com/LICENSE.txt



## How to install?

### Install ready-to-paste package

- Download the latest version at [Mageplaza Blog for Magento 2](https://www.mageplaza.com/magento-2-blog/)
-  [Installation guide](https://docs.mageplaza.com/kb/installation.html)

### Install via composer

Run the following command in Magento 2 root folder

```
composer require mageplaza/magento-2-blog-extension
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

### Manually install via composer

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