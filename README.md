# Magento 2 Customer Account Validation

## Description
This module is designed to add the possibility for the site owner to manually validate customer accounts at registration.

Such feature could be required, for instance, in situation of B to B sales or private sales.


This module has been developed on a Magento 2.1.7 instance, but there is not apparent reason it wouldn't
work on other releases.

* Magento 2.1.6 and above: not tested
* Magento 2.1.7 OK
* Magento 2.2 RC: OK

## Installation

You can manually download the archive and put it in app/code/Enrico69
or, the most simple way, install it via composer:

```
composer require enrico69/magento2-customer-activation
```

Whatever the way you choosed, then run the following command:

```
php magento cache:clean
```

## Configuration

In the back-office, got to _Store/Configuration_. In the _Customers_ tab, select _Customer
Configuration_. In the bottom, set _Customer account need to be activated by an admin user_ to true.

## How does it work?
After the activation of the module and once you have set the configuration to require account
activation by an admin user, the following process will be followed.

* At the customer registration, the new customer will be logged-out and a message
will notify it that its account is currently waiting for validation.
* On the other side, the site owner will receive an email notifying it.
* Until the account is confirmed by the admin, user which created an account AFTER
the installation and the configuration of the module cannot connect.
* To make an account active, the site owner has to go to the back office, edit the 
customer account an set this value to true: _Account is active_.