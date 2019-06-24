# Access Control

SAML Login access can be controlled at the Service Providers (SP) based on Craft 
user groups. These controls are found under the "Configure" tab in individual SP configuration. 
A number of combinations can be used but "Allow Any" and "No Group Assigned" are checked 
first, in that order. If the user is denied, an exception will be thrown with a HTTP 
status code of 403.

## Allow Any
Toggling "Allow Any" will let any user login to the SP.

## No Group Assigned
Toggle "No Group Assigned" to allow users to login who are not associated to any 
Craft user groups. 


## Allow Access by User Groups
For more granular control over login, toggle user groups to allow access. The user 
only needs to be apart of one of the allowed groups to gain access.  
