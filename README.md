KnowledgeBase
=============

This is a complete Knowledge Base software built on Symfony2 and PHPCR developed and open sourced by ServerGrove. Our website http://kb.servergrove.com is ran on this KnowledgeBase software.

Features
--------

- Data is stored in a PHPCR complaint server like Jackrabbit. Other implementations will be available.
- Complete Administration UI
- Multiple Users, with admin and editor privileges
- Multiple categories
- Categories and Articles can be localized in multiple languages

Installation
------------

The installation is quite simple, follow these steps:

	# Clone git repository
	
	git clone git@github.com:servergrove/KnowledgeBase.git kb.local
	
	cd kb.local
	
	# Install vendor dependencies with Composer
	
	curl -s https://getcomposer.org/installer | php
	
	php composer.phar install
	
	# start jackrabbit server
	
	php app/console doctrine:phpcr:jackrabbit start

	# wait a few seconds for the server to initialize and be ready

	# setup PHPCR database
	
	php app/console doctrine:phpcr:workspace:create sgkb
	php app/console doctrine:phpcr:register-system-node-types
	php app/console doctrine:phpcr:fixtures:load
	
	# start web server (PHP 5.4 only)
	
	php app/console  server:run

Once you completed these steps, if you are not using PHP 5.4, setup your web server virtual host to point to kb.local/web.

Access the KB site with either `http://localhost:8000` or `http://kb.local/`

Management
----------

The system includes an administration UI to manage categories and articles. To access it go to `http://localhost:8000/admin` or `http://kb.local/admin`

Login with:

* username: admin
* password: abc123

Please make sure to change the password immediately.

Contributing
------------

We hope people find this software useful. We also accept contributions through pull requests. If you find any bugs, feel free to open issues on github.

If you have any ideas on how to improve it or add new features, please contact us!

TODO
----

We still have many features we want to add. Some of these are:

- RESTful API
- Ability to rate content
- Multi-versions and rollback
- Allow users to submit new articles and edition improvement of existing articles