CART MIGRATION CONNECTOR INSTALLATION GUIDE

Notice: the connector is only used in case of self-hosted carts, SaaS carts don't need this file.

1. Download the connector: http://demo3.litextension.com/cartmigration_connector.zip

2. Upload the whole folder to your Source store/Target store root directory, so that we can get this exact link:
http://your-store.com/cartmigration_connector/connector.php
(If your store is put in a inner directory, please just make sure to upload the file to correct location accordingly, for example, your source store is located at http://your-domain.com/shop/, please upload the connector at http://your-domain.com/shop/cartmigration_connector/connector.php )
You can test this link by entering it into your browser, if you have placed the file correctly, you should see a message:
"Connector installed"

3. For security, please open connector.php, find this very first line: define('LECM_TOKEN', '123456');
And change "123456" to another string, this will be used to enter to migration form and act like "password" to to prevent unauthorized access to your store.

4. Log on Magento store with Cart Migration module installed, enter the following info in migration form:
- Cart type: your source cart type, example: osCommerce
- Cart url: http://your-store.com/ (please enter source store's root url only)
- Token: 123456 ( or any thing else if you have changed the default token)

Click on "Next". It should take you to the next step.

MIGRATION FULL GUIDE

http://litextension.com/docs/cart-migration-guide/

HELP & SUPPORT

For any question, please drop us a message at: http://litextension.com/contacts, or email us: contact@litextension.com
We are striving to response within 24 hours.

