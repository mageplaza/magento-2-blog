# How to install Blog for Magento 2

There are 2 different solutions to install Mageplaza extensions:

- Solution #1. Install via Composer (Recommend)
- Solution #2: Ready to paste (Not recommend)

## Important:
- We recommend you to duplicate your live store on a staging/test site and try installation on it in advanced.
- Backup magento files and the store database.
- This extension requires [Mageplaza_Core](https://github.com/mageplaza/module-core) installed first.

You will get an error, if **Mageplaza_Core** not installed.

## Solution #1. Install via Composer (Recommend)

Run the following command in Magento 2 root folder:

```
composer require mageplaza/magento-2-blog-extension
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## Solution #2: Ready to paste (Not recommend)

Please make sure that you've [installed Mageplaza_Core module](https://github.com/mageplaza/module-core#how-to-install--upgrade-mageplaza_core) already.

If you don't want to install via composer, you can use this way. 

- Download [the latest version here](https://github.com/mageplaza/magento-2-blog/archive/master.zip) 
- Extract `master.zip` file to `app/code/Mageplaza/Blog` ; You should create a folder path `app/code/Mageplaza/Blog` if not exist.
- Go to Magento root folder and run upgrade command line to install `Mageplaza_Blog`:

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
