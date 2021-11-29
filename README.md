# sms77 for Vtiger SMS Notifier Extension
Register an account and get free SMS credits for testing our service at [sms77](https://www.sms77.io).

Installing this plugin enables you to send SMS in Vtiger CRM via [sms77](https://www.sms77.io).

## Installation
### Via terminal
`wget https://github.com/sms77io/vtiger/blob/master/Sms77.php -P /path/to/vtigercrm/modules/SMSNotifier/providers`

### Via FTP

- Download the [provider File](https://github.com/sms77io/vtiger/blob/master/Sms77.php).
- Upload the file to your Vtiger CRM App **Vtiger/modules/SMSNotifier/providers** via FTP.
- Log in to your Vtiger administration dashboard.
- Click on the SMS Notifier tab.
- Click on the wrench icon and press `Server configuration`. 
- Click on `New configuration` and configure the details in the pop-up as described below.

**Provider**: Select `sms77` from the provider dropdown.

**Active**: Check for activating the sms77 provider.

**API Key**: Retrieve it from your [developer dashboard](https://app.sms77.io/developer).

**From**: Optionally, set a sender which gets displayed as the message origin.

Lets save the form and enable the provider `sms77` for sending SMS.

## Usage

### Send SMS from the list view

- Click on a module, e.g. `contacts` or `organizations`.

- Select one or more record(s) by clicking on the checkbox in that row.

- Click on `Actions` and click `Send SMS` in the dropdown menu.

- Set text message content and click on `Send`.


#### Support
Need help? Feel free to [send us an email](mailto:support@sms77.io).

[![MIT](https://img.shields.io/badge/License-MIT-teal.svg)](LICENSE)
