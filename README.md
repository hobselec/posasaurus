## POSasaurus

This is a Point of Sale written in Laravel.  It is in the process of being rewritten from an old system that had no framework.  I'm trying to get this implemented in Laravel with minimal changes to the front-end and then eventually convert to Angular or VueJS.

The 2FA implementation was largely taken from https://epndavis.com/blog/laravel-fortify-two-factor-authentication/


## Installation

1. Setup variables in .env
2. Install the tables in database/schema.sql
3. Run the migrations
4. Run the command billing:cache-balances to do an initial calculation of the balances
5. Setup a cron task to run the job queue
6. Build assets with npm run dev

## Notes


run php artisan report:aging to cache this report.  I plan to make a listener to run this when new tickets are made or maybe just run it every 5 minutes

run php artisan queue:work to run the job queue.  This processes mail jobs and creates Statement PDFs

Billing cached and updated in Jobs/UpdateAccount.php, which also fires the event Events/UpdateBilling.php to push to the websocket server.