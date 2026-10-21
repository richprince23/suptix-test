# Suptix
## Instructions & Guidelines

Repo Structure
- master branch is the live branch
- dev branch is for local development 
- staging branch is for testing dev branch and hotfixing
- feature/individual branches for individual or feature based dev. 
PS: This is where we work and merge upwords

php -d pcov.enabled=1 -d pcov.directory=app artisan test --coverage --coverage-html coverage 2>&1 | tail -80

composer run test:browser:watch
BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/TicketPurchaseFlowTest.php --filter="lets a guest" --headed

composer run test:browser:paystack
PAYSTACK_LIVE=1 BROWSER_DEMO=1 ./vendor/bin/pest tests/Browser/PaystackTestModeCheckoutTest.php --headed
