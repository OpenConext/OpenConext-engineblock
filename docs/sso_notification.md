# SSO Notification

SSO Notifications enable schools to reduce the number of steps users have to take during the login process and increase 
user-friendliness by providing the possibility to skip the WAYF (Where Are You From) screen. This is done via the use 
of a cookie, which can be set for example after the user has logged in on his own environment (for example an intranet page or the 
homepage of an Electronic Learning Environment).

Setting SSO Notification cookies is performed by a separate application - the SSO Notification service 
(found [here](https://github.com/OpenConext/OpenConext-SSO-Notification)). Engineblock
is able to read the cookie and retrieve the entity id of the Identity Provider which should be used to start an 
authentication with. The contents of the SSO Notification cookie are encrypted by the SSO Notification service.
Engineblock contains a few configuration parameters to set the encryption details.

## Configuration of SSO Notification in Engineblock

SSO Notification is an optional feature and can be enabled with:

    feature_enable_sso_notification: true

By default, AES-256 encryption is used by the SSO Notification service and is also the default configured
in Engineblock. Configure the encryption key and salt used with the parameters:

    # The encryption key used to decrypt the SSO notification
    sso_notification_encryption_key: <xxx>
    # The encryption key salt used to decrypt the SSO notification
    sso_notification_encryption_key_salt: <xxx>
    
The values for key and salt should match the ones used in the SSO Notification service.