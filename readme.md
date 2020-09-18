# bol.com customers downloader

### Description

 Download information about customers from orders in the bol.com seller account and save them to csv file. Optionally, can send email with updated customers file. Existed file will be updated with new customers.

### Requirements
 
Install PHP version 7.4.0 or greater

Install composer: https://getcomposer.org/
 
### Installation

```bash
git clone git@gitlab.com:DmitryP/bol-com-repricer.git repricer
cd repricer
composer install
```
Copy and rename file ./config.php.tpl to ./config.php. Than modify ./config.php file and setup it with proper STPM email server and bol.com client API keys.

### Usage

In project's folder run commands.


* Simple export customers:
 
```bash
php ./bin/console.php download-customers
```

Csv file with exported customers can be found in file: 

**[[project_folder]]/var/[[client_label_name]].csv**

* Export customers and send csv file to email addresses defined in config:
 
```bash
php ./bin/console.php download-customers --send-email
```

* Export customers and send csv file to custom email address instead of one in config:
 
```bash
php ./bin/console.php download-customers --send-email --address=custom-address@example.org
```

* Additional information about export :