ENGINEBLOCK_DB_HOST=$(php get_master_db_setting.php host) &&
ENGINEBLOCK_DB_NAME=$(php get_master_db_setting.php dbname) &&
ENGINEBLOCK_DB_USER=$(php get_master_db_setting.php user) &&
ENGINEBLOCK_DB_PASSWORD=$(php get_master_db_setting.php password) &&
cd .. &&
echo "Using: $ENGINEBLOCK_DB_USER:$ENGINEBLOCK_DB_PASSWORD@$ENGINEBLOCK_DB_HOST/$ENGINEBLOCK_DB_NAME" &&
java -jar database/liquibase/liquibase.jar \
    --classpath=database/mysql-connector-java-5.0.8/mysql-connector-java-5.0.8-bin.jar \
    --changeLogFile=database/generated.xml \
    --url="jdbc:mysql://$ENGINEBLOCK_DB_HOST/$ENGINEBLOCK_DB_NAME" \
    --username=$ENGINEBLOCK_DB_USER \
    --password=$ENGINEBLOCK_DB_PASSWORD \
    generateChangeLog
cd -
