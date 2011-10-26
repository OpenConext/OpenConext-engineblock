-- Update deprovisioning mail
UPDATE `emails` SET `email_text` = 'Dear {user},<br /><br />This mail is to inform you that you will be deprovisioned at {deprovision_time}. If you have any questions regarding this mail please contact help@surfconext.nl.<br /><br />You will be deleted as a member of all the groups. Currently you are member of the following groups:<br />{groups}<br />In the groups displayed below you are the only administrator. Please make another member administrator before you are deprovisioned, for these teams will be without an administrator otherwise.&nbsp;<br />{onlyAdminGroups}<br />Best regards,<br />SURFconext' WHERE `email_type` = 'deprovisioning_warning_email';