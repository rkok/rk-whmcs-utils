# RK WHMCS Utils

A quick slap-together of views I needed to make sense of a WHMCS instance:

- **Customer Overview**: show active WHMCS clients, their active 
  domains+services, affiliates+fees, VAT fees (assuming VAT inclusive),
  gross monthly revenue

- **eNom Check**: Compare domains from eNom and WHMCS and show problems

- **Transactions Overview**: transactions by invoice. Useful for bookkeeping.

- **Transaction Export**: CSV exports for use with GnuCash

Tested with PHP 8.1 and WHMCS 8.5.

Special Backender® Design.

## Setting up

1. Copy `config.example.php` to `config.php` and edit as needed
2. Run `composer install` to get dependencies
3. Put everything in a subdirectory of the WHMCS admin dir, for instance https://mydomain.com/whmcs-admin/utils/

## Development

Just run `php -S localhost:8080` and point your browser to it.

## Bugs

- Hardcoded VAT rate of 20%
- Probably some bugs matching users to clients and vice versa
- Not currency-aware
