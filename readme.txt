this webapp is made with : 
- PHP 8.5.3 (cli)
- Symfony CLI version 5.16.1 
- sql

commands to install essantial recipies and bundles  :
    . composer require webapp : it installs security features ,orm & database , templates and assets like twig engin and asset mapper
                    other things like mailer etc.
    . composer require orm    : it installs maps database to php entities , doctrine migration bundle , it installs only the database                     layer and the webapp install the other orm features 
    . composer require maker --dev : helps you generates entities and controllers and forms using commands 
    . composer require security :  install jwt apitoken features , automatic password hashing 
    . composer require form validator  :for handling and validation form data , map form data to entities
    configure the .env file to connect to the database : DATABASE_URL= "mysql://root:password@localhost:port/dbname?serverVersion=8.0"
    - creat the database : php bin/console doctrine:database:create
 
    . composer require symfonycasts/tailwind-bundle and then php bin/console tailwind:init 
    . composer require cocur/slugify
    . composer require easycorp/easyadmin-bundle : essential for the admin dashboard
        these are for generating fake product and category data :
            composer require --dev orm-fixtures
            composer require --dev fakerphp/faker
            to start gen and load into sql use this commande: php bin/console doctrine:fixtures:load
            you can also download fake images to match ur products : php bin/console app:download-images
    . composer require knplabs/knp-paginator-bundle : for pagination 
    . composer require liip/imagine-bundle  : for image resizin and thumbnails
    . composer require vich/uploader-bundle : for Storing the file to public/uploads/products/
                                                Saving only the filename to the DB
                                                Deleting the file when the product is deleted
                                                Generating URLs automatically
    . composer require stripe/stripe-php :  for stripe payment
    in your env you have to configure these by makin a stripe account you get the first two keys from ur dashboard
    STRIPE_PUBLIC_KEY=pk_test_...
    STRIPE_SECRET_KEY=sk_test_...
    STRIPE_WEBHOOK_SECRET=whsec_... : this require to run this commande to listen to stripe and u will get the key stripe listen --forward-to localhost:8000/stripe/webhook 
    also add this in you env APP_BASE_URL=http://localhost:8000
