echo "This script deletes temp files which the esracDbDevToProdWBackups.sh creates"

# Uncomment the next line for testing
#set -x

devDbConfig='../config/database.php'
devDbName=`grep "'database' =>" ${devDbConfig} | cut -f4 -d"'"`

prodDbConfig='/srv/www/esrac.cirg.washington.edu/htdocs/sme/app/config/database.php'
#prodDbConfig='/srv/www/esrac-dev.cirg.washington.edu/htdocs/test/app/config/database.php'
prodDbName=`grep "'database' =>" ${prodDbConfig} | cut -f4 -d"'"`

`rm ../db/*tokenized`
`rm ../db/${devDbName}_struct_*.sql`
`rm ../db/${prodDbName}_system_tables_*`
`rm ../db/${prodDbName}_study_specific_tables_*`
`rm ../db/${prodDbName}_struct_*.sql`
`rm ../db/systemDiffTemp.txt`
`rm ../db/studySpecificDiffTemp.txt`
echo "Done.";
