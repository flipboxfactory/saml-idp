# Configuring the Service Provider

## Security Tab
If you want the assertions (along with users attributes and pii) to be encrypted, check the "Wants Encrypt Assertions".

## Configure Tab

### NameID
You can overwrite the NameID (the unique id for the user) using template code in this filed. This field defaults 
to username but you can use any scalar field with template code. For example, `{username}` will use the username. 
`{uid}` will use the user's Craft uid value.

### Mapping

- Attribute Name: The name of the attribute being sent in the Assertion XML. You can use common SAML claim types like
  `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress` or name it something easier like `Email` or `email` (lowercased).
  
- Craft User Property: Select the Craft field that maps to the previously set attribute name.

- Templated Override: Like with the NameID, you can use twig templating to target a specific value. For example: 
`myCategoryField.one().title` OR `object.myCategoryField.one().title`.
  
### Preview Mapping
This allows you preview the mapping you are setting with the current user (you, logged in as) or you can enter the id
of another.

### Groups
Groups section allows you to give sso access/permission to users based on their Craft groups per Service Provider.