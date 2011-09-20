-- Turn off required signature validation for all SPs
UPDATE janus__metadata SET `value`='0' WHERE `key`='redirect.sign' OR `key`='redirect.validate';