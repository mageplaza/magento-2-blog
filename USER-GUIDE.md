# Blog User Guide

## Documentation
- Installation guide: https://www.mageplaza.com/install-magento-2-extension/
- User guide: https://docs.mageplaza.com/blog-m2/index.html
- Download from our Live site: https://www.mageplaza.com/magento-2-blog-extension/
- Get Support: https://github.com/mageplaza/magento-2-blog-extension/issues
- Contribute on Github: https://github.com/mageplaza/magento-2-blog/
- Changelog: https://www.mageplaza.com/releases/blog
- License https://www.mageplaza.com/LICENSE.txt


## How to install

### Method 1: Install ready-to-paste package

- Download the latest version at [Mageplaza Blog for Magento 2](https://www.mageplaza.com/magento-2-blog/)
-  [Installation guide](https://www.mageplaza.com/install-magento-2-extension/)

### Method 2: Install via composer [Recommend]

Run the following command in Magento 2 root folder

```
composer require mageplaza/magento-2-blog-extension
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## FAQs

#### Q: I got error: `Mageplaza_Core has been already defined`
A: Read solution: https://github.com/mageplaza/module-core/issues/3

#### Q: My site is down
A: Please follow this guide: https://www.mageplaza.com/blog/magento-site-down.html
