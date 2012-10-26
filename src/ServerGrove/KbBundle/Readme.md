# ServerGroveKbBundle


This bundle allows to easily create a knowledge base application with a few simple steps.


## Installation

You need to add the bundle to your composer file. You can easily do so by running the following command:

```bash
# temporarily until we find the proper name for the bundle
$ composer require servergrove/kb-bundle:dev-master 
```


### Add the bundle to your project

There is only one requirement, to add the Bundle instance to your Kernel

```php
<?php
// app/AppKernel.php

public function registerBundles() {
    return array(
        // …
        new ServerGrove\KnowledgeBaseBundle\SGKnowledgeBaseBundle(),
        // ...
    );
}
```


Configuration
-------------

### Configure this bundle

Add the locales you desire to use in your application.

```yaml
# app/config/config.yml

server_grove_kb:
    locales:        [en, es, pt]
    default_locale: en
    article:
        enable_related_urls: false
        front_page_category: Homepage
        front_page_keyword:  homepage
        top_keyword:         feature
    editor_type: markdown
    mailer:
        from:
            email: noreply@servergrove.com
            name:  ServerGrove KnowledgeBase System
```

### Configure the SecurityBundle

#### Users

This Bundle provides with a document called *User* and a service for the user provider, which you can use to manage the access to the admin area. The path to this document is `Document/User.php`

To complete the user configuration, you need to add the encoder and provider for the mentioned User document.

##### Encoder

```yaml
# app/config/security.yml
encoders:
        ServerGrove\KbBundle\Document\User: sha512
```

Note that you can use the encoder strategy that you like the most.

##### Provider

```yaml
# app/config/security.yml
providers:
        user_db:
            id: server_grove_kb.security.user.provider
```

Remember, these are encoder and provider are available in the bundle, but feel free to use your own implementation.


Test Data
---------

In order to use some test data, you have to run the following commands

```bash
$ php app/console doctrine:phpcr:workspace:create sgkb
$ php app/console doctrine:phpcr:register-system-node-types
$ php app/console doctrine:phpcr:fixtures:load
```


Application
===========

This section assumes that you have your application installed under kb.local

### Frontend

The frontend area is located in the main route `/`. So you can start navigating the application by opening the following address in your web browser: <http://kb.local/>

### Backend

The backend is located under `/admin`, so you will have to go to <http://kb.local/admin>.
This is a secure area, so you will have to login with valid credentials. If you are using the test data, then you can access it with `admin:abc123`
