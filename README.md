# Magento 2 Blog extension FREE

**Magento 2 Blog by Mageplaza** is integrated right into the Magento backend so you can manage your blog and your e-commerce store all in the one place. It is the perfect marketing tool for your bussiness that not only provides update information to your existing customers but also drive more visitors to your online store.

Demo: http://blog.demo.mageplaza.com/blog/

## Documentation

- Installation guide: https://www.mageplaza.com/install-magento-2-extension/
- User guide: https://docs.mageplaza.com/blog-m2/index.html
- Download from our Live site: https://www.mageplaza.com/magento-2-blog-extension/
- Marketplace: https://marketplace.magento.com/mageplaza-magento-2-blog-extension.html
- Get Support: https://github.com/mageplaza/magento-2-blog-extension/issues
- Contribute on Github: https://github.com/mageplaza/magento-2-blog/
- Changelog: https://www.mageplaza.com/changelog/m2-blog.txt
- License https://www.mageplaza.com/LICENSE.txt

## FAQs

#### Q: I got error: `Mageplaza_Core has been already defined`
A: Read solution: https://github.com/mageplaza/module-core/issues/3



## How to install

### Method 1: Install ready-to-paste package

- Download the latest version at [Mageplaza Blog for Magento 2](https://www.mageplaza.com/magento-2-blog/)
-  [Installation guide](https://www.mageplaza.com/install-magento-2-extension/)

### Method 2: Install via composer

Run the following command in Magento 2 root folder

```
composer require mageplaza/magento-2-blog-extension
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```


## Contribute to this module

Feel free to **Fork** and contrinute to this module and create a pull request so we will merge your changes to `master` branch.

--------

## Blog benefits

Magento 2 blog extension can give you a greate opportunity to communicate with your potential and existing customers. Blog posts can help you to create a 2-way communication platform to publish new information such as news, promotions, comming products. Read more benefits of Blog.

![Magento 2 blog extension by Mageplaza](https://www.mageplaza.com/assets/img/extensions-images/magento-2-blog/mobile-mockup.jpg)

### Drive more traffics
“Raise your hand if you need more targeted visitors. Sure, me too.”

Think of total number of pages you can find on your online store. Maybe not a ton, right? And think of how frequently you update those pages. Maybe not that usually, right? Well, running a blog helps resolve both of those problems.

Writing a blog helps as well you get discovered via social networks. Any time you write a blog post, you are building content that many people can share on social networking sites such as Twitter, LinkedIn, Facebook, Pinterest – which will help expose your web business to a new one audience that will not know you yet.

### It may help convert that traffic into leads.
Similar to every blog post you write is yet another indexed page, each post is really a new chance to generate new leads. The way in which this works is absolutely simple: Just put in a lead-generating call-to-action to each blog post.

### Boost Search Engine Optimization Ranking
Search Engines like Google loves fresh content. What better method to deliver frequent content compared to blog posts. By writing blog consistently, you provide Google and other search engines new content to index and you also create chances to plug in those all-important keywords to boost your visibility on search engine results pages (SERPS).

### Build relationships with your customers
Writing a blog enables you to interact with your website visitors. You can accomplish this by asking your visitors questions at the conclusion of your posts to obtain the conversation going or simply by allowing comments and feedback. By reviewing and answering readers’ comments, you may create a rapport together with your audience, build trust, and gain valuable insight into what your visitors are seeking.

## Magento 2 Blog full feature list

- Responsive design
- SEO friendly
- Post attributes
- Multiselect tags
- Numerous Comment engine: Magento Built-in comments, Facebook Comment, Disqus Comment.
- RSS Optimization
- WYSIWYG editor in admin
- Blog breadcrumbs
- Integrate Magento Sitemap or Blog sitemap
- Multistores support

## Blog features for Magento 2
A better blog for your e-commerce store without adding any 3rd-party framework. Better blog extension allows you manage categories, posts, comments on Magento 2 back-end. The system will update you when there is a new comment submit. This is a great solution for building relevant SEO-friendly text links, and serves as assistance in building sales strategy.

### Responsive design
Mobile-friendly design will make allow you customers to read your blog posts with maximum comfort on tablets and smartphones. Using the off-canvas menu, they will be able to see widgets and interact with the blog search. Tested on Madison, Ultimo, Porto theme.

### Flexible display posts
It is very easy to add recent posts, post in specific category in homepage. Your customers also see recent posts in sidebar.

### SEO friendly
Properly optimized blog can get your site higher in search results and lead more potential customers. Better Blog allows you to create meta information not only for posts, but also for categories and tags.

### Post attributes
You can create unlimited attributes for post. It is extremely customized your blog by adding as many post attributes as you want to. It displays right in Post Edit and Frontend. No code modification required.

### Comments
Better Blog supports 3 types of comments: built-in comment, Disqus comment (coming soon), and Facebook comment (coming soon). You can switch comment solution in configuration.

### Sharing posts
Better blog is integrated with Addthis, that why your customers can share posts via about hundred social networks such as Facebook, Google+, Twitter, Instagram, Pinterest, Tumblr, Delicious, Digg , StubmleUpon, Linkedin, Reddit or email ….

### RSS Optimization
Full RSS integration into Magento 2. The blog RSS feed appears right next to the standard product and Magento 2 RSS feeds, allowing users that are familiar with your website to easily find all the blog RSS feeds easily and simply.


---------

## USER GUIDE

### General Configuration


* Login to Magento Admin, `Better Blog > Settings`.

* Open **General** section, 

  * Enable the module when choosing “Yes” in the required field.
  
  * Set name for the new blog 
  
  * Enter the `URL Prefix` and `URL Suffix`. If leave empty for no suffix, use the default configuration (html)
  
  * To make the blog link visible on the top/ footer links on your site, set `Show Blog Link in Top/ Footer Links` to "Yes".
  
  * To show the sidebar on the storefront, select "Yes" in the `Show Sidebar Right/ Left` field.

![blog sidebar](https://i.imgur.com/0dIdrwc.png)

* Open **Sidebar** section,

  * Enter `Number of recent posts` that display on the sidebar.
  
  * Enter `Number of most view posts` that display on the sidebar.

![config sidebar](https://i.imgur.com/kMwejsJ.png)

* Open **Comments** section,

  * Set `Number of recent posts` to one of the following options:
  
    * Disqus Comment
    * Facebook Comment 
    * Disable Completely

![configure comment](https://i.imgur.com/UJnhJl1.png)

* Open **SEO** section, you can configure the meta that is useful for your better SEO

  * Set `Meta title for posts list page` 
  * Set `Meta description for posts list page`
  * Set `Meta keywords for posts list page`

![configure SEO](https://i.imgur.com/bGgJsMB.png)

* Open **Social** section,

  * Enable social buttons on the blog page
  * In the `Pubid` field, insert ID for Share buttons that get from https://addthis.com.
  
  In Social share session, we use Addthis.com to add more than 200 share buttons, and display only 4 most popular share buttons. You can custom these buttons by add your own Addthis Pub-id

![social buttons](https://i.imgur.com/QIs3FXz.png)

* Click on `Save Config` when complete.

### How to Create a New Post

* Step 1: Complete the post information
* Step 2: Assign to Topic
* Step 3: Assign to Categorie
* Step 4: Assign Tags to the post
  
#### Step 1: Complete the post information


* To display the new post, choose "Yes" in the `Enabled` field
* Login to Magento Admin, `Better Blog > Posts`
* On the upper-right corner, tap `Add New Posts` button
* Set `Name` for the new post
* Enter `Short Description` if you need
* Use WYSIWYG Mode to insert the post content that allows showing right as in the storefront.
  
  If you want to show the content under code, tap `Show/ Hide Editor` button.

* Upload image from your brower in the **Image** option.
* Enter the `URL Key` that the visitor can access the blog post
* To allow the readers leave comments on your post, choose "Yes" in the `Allow Comment` field.

* Complete `Meta Title`, `Meta Description`,and `Meta Keywords` for your better SEO.
* Set `Meta Robots` to one of the following opitons:

  * Index, Follow
  * NoIndex, NoFollow
  * NoIndex, Follow
  * Index, NoFollow

  ![magento 2 blog create a new post](https://cdn.mageplaza.com/docs/blog-create-new-blog-post.gif)

* Tap `Save and Continue Edit`

#### Step 2: Assign the post to Topic


* You can do it when you create a new post in the **Topics** tab, or go to **Posts** tab
* In the list of the available posts, mark the checkbox that is next to the posts you want to assign

![blog assign to a topic](https://cdn.mageplaza.com/docs/blog-assign-to-topics.gif)

* Tap `Save and Continue Edit`

#### Step 3: Assign to Categories


* You can do it when you create a new post in the **Categories** tab, or go to **Posts** tab
* In the list of the available posts, mark the checkbox that is next to the posts you want to assign

![assign categories](https://cdn.mageplaza.com/docs/blog-assign-to-categories.gif)

* Tap `Save and Continue Edit`

#### Step 4: Assign Tags to the post


* You can do it when you create a new post in the **Tags** tab, or go to **Posts** tab
* In the list of the available posts, mark the checkbox that is next to the posts you want to assign

![assign tags](https://cdn.mageplaza.com/docs/blog-assign-to-tags.gif)

* Tap `Save and Continue Edit`, then hit `Save Post` to finish. 

#### How to create a new Tag


* Login to Magento Admin, `Better Blog > Categories`
* To active the new tag, set `Enabled` to "Yes"
* Set `Name` for the new tag
* Use WYSIWYG Mode to enter the description of the tag
* Enter the `URL Key` that the visitor can access the tag

![create a new tag](https://cdn.mageplaza.com/docs/blog-create-new-tag.gif)

* If need, you can assign the new tag to specific post.
* Click on `Save Tag` when complete.


#### How to Create a new Topic


* Login to Magento Admin, `Better Blog > Topics`
* To active the new topic, set `Enabled` to "Yes"
* Set `Name` for the new topic
* Use WYSIWYG Mode to enter the description of the topic
* Enter the `URL Key` that the visitor can access the topic
* Complete `Meta Title`, `Meta Description`,and `Meta Keywords` for your better SEO.
* Set `Meta Robots` to one of the following opitons:

  * Index, Follow
  * NoIndex, NoFollow
  * NoIndex, Follow
  * Index, NoFollow

![create a new topic](https://cdn.mageplaza.com/docs/blog-create-new-topic.gif)

* If need, you can assign the new topic to specific post.
* Click on `Save Topic` when complete.

#### How to Create a new category


* Login to Magento Admin, `Better Blog > Categories`
* To active the new category, set `Enabled` to "Yes"
* Set `Name` for the new category
* Use WYSIWYG Mode to enter the description of the category
* Enter the `URL Key` that the visitor can access the category
* Complete `Meta Title`, `Meta Description`,and `Meta Keywords` for your better SEO.
* Set `Meta Robots` to one of the following opitons:

  * Index, Follow
  * NoIndex, NoFollow
  * NoIndex, Follow
  * Index, NoFollow

![create a new category](https://cdn.mageplaza.com/docs/blog-create-new-category.gif)

* If need, you can assign the new category to specific post.
* Click on `Save Category` when complete.



### How to add custom CMS Static Block in Blog Post



In this guide, we will show you how to add a custom content in Blog post such as: Call to action, promoted banner ...

There are few hidden tricks in Mageplza Blog for Magento 2. Here are hidden CMS Static Block:

- `mageplaza_blog_view_under_content` : Under Content section
- `mageplaza_blog_view_above_comment` : Above Comment section
- `mageplaza_blog_sidebar_above_popular_widget` : Above Popular/Recent posts widgets
- `mageplaza_blog_sidebar_above_categories_widget` : Above Categories widgets
- `mageplaza_blog_sidebar_above_tags_widget` : Above Tags widgets
- `mageplaza_blog_sidebar_under_tags_widget` : Under Tags wigets


See this:

![blog magento 2](https://www.mageplaza.com/assets/img/extensions-images/magento-2-blog/hidden-static-block.jpg)




#### How to create CMS static block in Magento 2


From Admin panel > Content > Blocks > Add New Block

![add a new block](https://i.imgur.com/vHoPfUE.png)

Then fill the content of block such as Name, idenity, content ...

![block content](https://i.imgur.com/cuHNsfk.png)

then click on `Save and continue`



--------------
## BLOG CHANGELOG 


### SEO v1.4.1
Released on  2017-06-12
Release notes: 

- Optimize Structured Data ld-json
- Optimize Social share Open graph (Google+ and Pinterest) and twitter card
- Fixed Breadcrumbs issue
- Fixed canonical link issue



### SEO v1.4.0
Released on  2017-05-23
Release notes: 

- Released Seo Crosslinks
- Fixed minor bugs



### SEO v1.3.1
Released on  2017-05-23
Release notes: 

- Fixed issue on bundle products
- Fixed Remove inject object in Search/Catalog block
- Optimize config object
- Optimize Search Nofollow



### SEO v1.3.0
Released on  2017-04-24
Release notes: 

**SEO Core , Sitemap**

* Exclude CMS pages in HTML Sitemap
+ Include custom links in HTML Sitemap
* Opt to exclude out-of-stock in HTML, XML Sitemap
* Optimize canonical tag, rich snippet, hrefLang, alternate

**Rule templates**

+ Compatible with Layered Navigation
+ Add Mageplaza_Blog posts meta data template
+ Optimize template rule products, categories, CMS pages.
+ Preview rule template




### SEO v1.2.2
Released on  2017-04-24
Release notes: 

- Edit composer.json to require mageplaza/module-core instead of mageplaza/core-m2



### SEO v1.2.1
Released on  2017-04-09
Release notes: 

- Fix composer issue



### SEO v1.2.0
Released on  2017-04-09
Release notes: 

**Features**

+ Add Verification configuration
+ Add Stop words feature
+ Hreflang tags feature

**Bug fixes**

- Performance optimization
- Group SEO menu
- Optimize Configuration options




### SEO v1.1.4
Released on  2016-11-23
Release notes: 

- Fix Upgrade issue




### SEO v1.1.2
Released on  2016-11-22
Release notes: 

- Improve Robots meta tags categories, products, cms pages




### SEO v1.1.1
Released on  2016-10-20
Release notes: 

- add HTML Sitemap features




### SEO v1.1.0
Released on  2016-10-20
Release notes: 

## Improvement
- Optimize code and performance
- Comment code

## Features
- Add config Disable URL parameter in canonical URL

## Bug fixing
- Fix bug canonical url




### SEO v1.0.2
Released on  2016-08-09
Release notes: 

- Compatible Magento 2.1
- Fix bug Dependency 
- Fix JSONLD broken tags
- Optimize Review rating value




### SEO v1.0.1
Released on  2016-07-20
Release notes: 

- Add composer and packagist.org



### SEO v1.0.0-beta
Released on  2016-04-23
Release notes: 

- Release first version




