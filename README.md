Symfony2 Application
====================



1) Installing
-------------

    git clone git@github.com:alexbrouwer/s2-zero-app.git
    
    php composer.phar install
    
    HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs


What's inside?
---------------

Defaults:

  * Twig is the only configured template engine;

  * Doctrine ORM/DBAL is configured;

  * Swiftmailer is configured;

  * Annotations for everything are enabled.

It comes pre-configured with the following bundles:

  * **FrameworkBundle** - The core Symfony framework bundle

  * [**SensioFrameworkExtraBundle**][1] - Adds several enhancements, including
    template and routing annotation capability

  * [**DoctrineBundle**][2] - Adds support for the Doctrine ORM

  * [**TwigBundle**][3] - Adds support for the Twig templating engine

  * [**SecurityBundle**][4] - Adds security by integrating Symfony's security
    component

  * [**SwiftmailerBundle**][5] - Adds support for Swiftmailer, a library for
    sending emails

  * [**MonologBundle**][6] - Adds support for Monolog, a logging library

  * [**AsseticBundle**][7] - Adds support for Assetic, an asset processing
    library

  * **WebProfilerBundle** (in dev/test env) - Adds profiling functionality and
    the web debug toolbar

  * **SensioDistributionBundle** (in dev/test env) - Adds functionality for
    configuring and working with Symfony distributions

  * [**SensioGeneratorBundle**][8] (in dev/test env) - Adds code generation
    capabilities

[1]:  http://symfony.com/doc/2.4/bundles/SensioFrameworkExtraBundle/index.html
[2]:  http://symfony.com/doc/2.4/book/doctrine.html
[3]:  http://symfony.com/doc/2.4/book/templating.html
[4]:  http://symfony.com/doc/2.4/book/security.html
[5]:  http://symfony.com/doc/2.4/cookbook/email.html
[6]:  http://symfony.com/doc/2.4/cookbook/logging/monolog.html
[7]:  http://symfony.com/doc/2.4/cookbook/assetic/asset_management.html
[8]:  http://symfony.com/doc/2.4/bundles/SensioGeneratorBundle/index.html
