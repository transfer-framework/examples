GoogleNewsBundle
================

This Symfony bundle is to demonstrate a quite simple way to import remote content.

Firstly it creates or updates a predefined content type from [a yaml file](src/Transfer/GoogleNewsBundle/Resources/contenttypes/google_news.yml), with the following fields:

* title (ezstring)
* category (ezstring)
* publish_date (ezdate)
* link (ezurl)

Then it contacts https://news.google.com/news for some content, and imports this to Content and Location in eZ Platform.
The locations will be displayed below a node, passed from [a service definition](src/Transfer/GoogleNewsBundle/Resources/config/services.yml#L8).

An import can be triggered like this, from your eZ Platform installation directory.
```php
php app/console transfer:manifest:run googlenews_to_ezplatform_content
```
