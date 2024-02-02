
# BileMo

BileMo is an API system that lets you manage a catalogue of mobile phones and a list of users.

This is part of my training with OpenClassRooms, it is my 7th project.


## Badges

![Generic badge](https://img.shields.io/badge/PHP-8.1-<COLOR>.svg)

![Generic badge](https://img.shields.io/badge/Symfony-6.4-<COLOR>.svg)

![Generic badge](https://img.shields.io/badge/MySQL-8.0.36-<COLOR>.svg)

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/2c2d67ddacd7486dbd3b9f2a7a70a318)](https://app.codacy.com/gh/devperez/BileMo/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
## Installation

Follow these steps to get the project up and running.

1 - Clone the repo

```bash
  git clone https://github.com/devperez/BileMo.git
```
2 - Run composer in the project's folder

```bash
    composer install
```

3 - Edit the .env file

At this point, you just need to provide a connection to your database.

4 - Create the database, run the migrations and load the fixtures

```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    php bin/console doctrine:fixtures:load
```
5 - Generate the keys to use JWT Token

```bash
    $ mkdir -p config/jwt
    $ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    $ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
6 - Update your .env file

```bash
    ###> lexik/jwt-authentication-bundle ###
    JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
    JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
    JWT_PASSPHRASE=VotreMotDePasse
    ###< lexik/jwt-authentication-bundle ###
```
## Documentation

Once you have the project running, you can access a local documentation here:

[Documentation](http://127.0.0.1:8000/api/doc)


## Screenshot
This is what the documentation looks like. On this page you will be able to test all the routes once you have provided a token to the API.

To do so :
- click on the post link of the Token section. Then click on "Try it out" and "Execute".
You will get a response and within its body you will find the token generally starting with "ey".
- Copy this token without the quotation marks and click on the Authorize button at the top of the page.
- In the modal input write "bearer" without the quotation marks and then paste your token. Click on the authorize button and then close the modal.

You are now authenticated and can use any route you want.

![App Screenshot](https://github.com/devperez/BileMo/blob/main/screenshot.png?raw=true)
