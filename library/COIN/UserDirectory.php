<?php
 
class COIN_UserDirectory 
{
    const USER_ID_ATTRIBUTE = 'uid';

    public function registerUserForAttributes($attributes, $attributeHash)
    {
        if (!defined('ENGINEBLOCK_USER_DB_DSN') && ENGINEBLOCK_USER_DB_DSN) {
            return false;
        }

        $uid = $attributes[self::USER_ID_ATTRIBUTE][0];

        $dbh = new PDO(ENGINEBLOCK_USER_DB_DSN, ENGINEBLOCK_USER_DB_USER, ENGINEBLOCK_USER_DB_PASSWORD);
        $statement = $dbh->prepare("INSERT INTO `users` (uid, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
        $statement->execute(array($uid));

        $sqlValues = array();
        $bindValues = array(self::USER_ID_ATTRIBUTE => $uid);

        $nameCount = 1;
        $valueCount = 1;
        foreach ($attributes as $attributeName => $attributeValues) {
            if ($attributeName===self::USER_ID_ATTRIBUTE) {
                continue;
            }

            $bindValues['attributename' . $nameCount] = $attributeName;

            foreach ($attributeValues as $attributeValue) {
                $sqlValues[] = "(:uid, :attributename{$nameCount}, :attributevalue{$valueCount})";
                $bindValues['attributevalue' . $valueCount] = $attributeValue;
                $valueCount++;
            }
            $nameCount++;
        }

        // No other attributes than uid found
        if (empty($sqlValues)) {
            return false;
        }

        $statement = $dbh->prepare("INSERT IGNORE INTO `user_attributes` (`user_uid`, `name`, `value`) VALUES " . implode(',', $sqlValues));
        $statement->execute($bindValues);
    }
}
