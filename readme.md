wip

## Cache Groups
* options
* users
* userlogins
* useremail
* userslugs
* user_meta
* transient
* posts
* posts_meta
* category_relationships
* post_tag_relationships
* themes
* nav_menu
* terms
* transient
* site-transient
* shopp_category
* counts

## Document Types
* Post      - Thing > CreativeWork > Article > BlogPosting
* Page
* Media     - Thing > CreativeWork > MediaObject
* Image     - Thing > CreativeWork > MediaObject > ImageObject
* Music     - Thing > CreativeWork > MusicRecording
* Software  - Thing > CreativeWork > SoftwareApplication
* User
* Option
* Cache
* Link
* Comment   - Thing > CreativeWork > Comment
* Product
* Term
* Order

## Global Groups (global_groups)
Non-blog specific on multisite.

[users] => 1
[userlogins] => 1
[usermeta] => 1
[user_meta] => 1
[site-transient] => 1
[site-options] => 1
[site-lookup] => 1
[blog-lookup] => 1
[blog-details] => 1
[rss] => 1
[global-posts] => 1
[blog-id-cache] => 1



## Settings
* Server URL
* Server Authentication

### Advanced Settings
* Data Types (Posts, Terms, Users)
* Index Name
* Data Mapping

## Service Functions
* Initialize (create index)
* Export (re-index)
* Import
* Synchornize
* Flush

## API Endpoints

* /wp-admin/admin-ajax.php?action=/elastic/status
* /wp-admin/admin-ajax.php?action=/elastic/settings

## Building
