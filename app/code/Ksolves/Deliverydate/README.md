# List of features: 


# Delivery Date Selection
The customers can select the desired date and time of order delivery as per their convenience. Also, the customers get an option to fill the custom delivery comment for the order. 

# Order Delivery Date Details
The customers can check the complete order delivery information in their account section. 

# Admin View
The order can view the order delivery information on the order view page in the backend. 


# Configuration Section for Delivery Date Module
This part has three sections:

# General Section
Admin can enable/disable the order delivery date.
Admin can set the Label for order delivery date.
Admin has the option to enable/disable the same day order delivery option.
Admin can enable/disable the order delivery date time slot option.
Admin can set the Label for  order delivery time slots.
Admin can enable/disable the delivery comment.
Admin can set the Label for order delivery date comments.
Admin has the option to choose the different order delivery date format.

#Time Slot Setting


Admin has the option to set a suitable time slot for order delivery.
Admin has the option to disable the specific time slot option from the list. 

# Holiday Management Setting
Admin has the option to select the day off in the calendar for the order delivery.
Admin has the option to add a particular day off in the calendar for specific occasions like Christmas, Diwali, and New Year.


# Installation Instruction

* Copy the content of the repo to the Magento 2 app/code/Ksolves/Deliverydate
* Run command:
<b>php bin/magento setup:upgrade</b>
* Run Command:
<b>php bin/magento setup:di:compile</b>
* Run Command:
<b>php bin/magento setup:static-content:deploy</b>
* Now Flush Cache: <b>php bin/magento cache:flush</b>



