# Uncomment the next line for testing
#set -x

echo
echo "This script:"
echo "1) compares the schema and content of this cake site's database (eg a dev instance) with that of a destination cake site (eg a production instance)"
echo "2) optionally commits this site's schema to subversion"
echo "3) optionally updates the struct and data of the destination tables that don't contain instance-specific data, PHI, etc."
echo

# destDomainRoot eg 'p3p.cirg.washington.edu', 'esrac.cirg.washington.edu'
destDomainRoot='p3p.cirg.washington.edu'
echo "What is the domain name of the destination system? For ${destDomainRoot}, simply hit enter"
read nonStdDomainRoot
if [ "$nonStdDomainRoot" != "" ] ; then 
    destDomainRoot="${nonStdDomainRoot}"
fi
echo

#destSrvRoot eg 'srv/www/p3p-dev.cirg.washington.edu/htdocs/uva'. Leave as-is for standard production updates
destSrvRoot="srv/www/${destDomainRoot}/htdocs"
echo "What is the subdirectory of the destination system? This is under ${destSrvRoot}. For ${destSrvRoot}, simply hit enter"
read destDir
if [ "$destDir" != "" ] ; then 
    destDir="/${destDir}"
fi
echo

destDbConfig="/srv/www/${destDomainRoot}/htdocs${destDir}/app/config/database.php"

echo "Okay, reading destination DB config: ${destDbConfig}."
echo

systemTables="acos aros aros_acos"
studySpecificTablesToWriteToProd="alerts clinics conditions items options pages projects projects_questionnaires questionnaires questions scales sites subscales targets influential_factors"
today=`date +"%m%d%y"`
sourceDbConfig='../config/database.php'
sourceDbName=`grep -m 1 "'database' =>" ${sourceDbConfig} | cut -f4 -d"'"`
sourceStructTodayFileName="../db/${sourceDbName}_struct_${today}.sql"
sourceStructToCommitFileName="../db/${sourceDbName}_db_struct.sql"
sourceSystemTablesToCommitAndWriteToProd="../db/${sourceDbName}_system_tables.sql"
sourceStudySpecificTablesToCommitAndWriteToProd="../db/${sourceDbName}_study_specific_tables.sql"
sourceUser=`grep -m 1 "'login' =>" ${sourceDbConfig} | cut -f4 -d"'"`
sourcePw=`grep -m 1 "'password' =>" ${sourceDbConfig} | cut -f4 -d"'"`

destDbName=`grep -m 1 "'database' =>" ${destDbConfig} | cut -f4 -d"'"`
destStructTodayFileName="../db/${destDbName}_struct_${today}"".sql"
destSystemTables="../db/${destDbName}_system_tables_${today}.sql"
destStudySpecificTables="../db/${destDbName}_study_specific_tables_${today}.sql"
destTodayFileName="../db/${destDbName}_${today}.sql"
destUser=`grep -m 1 "'login' =>" ${destDbConfig} | cut -f4 -d"'"`
destPw=`grep -m 1 "'password' =>" ${destDbConfig} | cut -f4 -d"'"`

echo "This script compares the schema and content of the ${sourceDbName} and ${destDbName} databases, optionally commits the ${sourceDbName} version to subversion, and also optionally updates the struct and data of the ${destDbName} tables that don't contain instance-specific data, PHI, etc."

echo "Exporting struct of ${sourceDbName} and ${destDbName}, and diffing them."

#the following didn't seem to work: --ignore-table=${sourceDbName}.%_deleted 
mysqldump --no-data -u ${sourceUser} -p${sourcePw} ${sourceDbName} --skip-comments --ignore-table=${sourceDbName}.tickets | sed 's/AUTO_INCREMENT=[0-9]*\b//' > ${sourceStructTodayFileName} 

# the following didn't seem to work: --ignore-table=${destDbName}.%_deleted 
#mysqldump --no-data -u ${destUser} -p${destPw} ${destDbName} --ignore-table=${destDbName}.patient_T1s --ignore-table=${destDbName}.patient_T1s_SCCA | sed 's/AUTO_INCREMENT=[0-9]*\b//' > ${destStructTodayFileName} 

mysqldump --no-data -u ${destUser} -p${destPw} ${destDbName} \
--skip-comments --ignore-table=${destDbName}.tickets | sed 's/AUTO_INCREMENT=[0-9]*\b//' > ${destStructTodayFileName}
# --ignore-table for views has a bug ...
#--ignore-table=${destDbName}.consented_patients_T1s \
#--ignore-table=${destDbName}.consented_patients_T1s_count \
#--ignore-table=${destDbName}.logs_intervention_non_test \
#--ignore-table=${destDbName}.logs_intervention_non_test_count \
#--ignore-table=${destDbName}.patients_clinics_view \
#--ignore-table=${destDbName}.patients_clinics_view_count \
#--ignore-table=${destDbName}.scales_subscales_view \
#--ignore-table=${destDbName}.survey_sessions_non_test_view_count \
#--ignore-table=${destDbName}.treatment_participants_wo_trtmnt_start \
#--ignore-table=${destDbName}.treatment_participants_wo_trtmnt_start_count \
#--ignore-table=${destDbName}.logs_intervention_non_test \
#--ignore-table=${destDbName}.logs_intervention_non_test_count \
#--ignore-table=${destDbName}.patient_T1s \
#--ignore-table=${destDbName}.patient_T1s_SCCA \
#--ignore-table=${destDbName}.patients_clinics_view \
#--ignore-table=${destDbName}.patients_clinics_view_count \
#--ignore-table=${destDbName}.scales_subscales_view \
#--ignore-table=${destDbName}.survey_sessions_non_test_view_count \
#--ignore-table=${destDbName}.treatment_participants_wo_trtmnt_start \
#--ignore-table=${destDbName}.treatment_participants_wo_trtmnt_start_count \

structDiff=`diff -b ${sourceStructTodayFileName} ${destStructTodayFileName}`

echo "View struct diff?"
select yn_struct in "Yes" "No"; do
    case $yn_struct in
        Yes )
            echo "Result of struct diff: ${structDiff}"
            break;;
        No )
            break;;
    esac
done

echo "Exporting ${sourceDbName} and ${destDbName} system tables, and diffing them."
sleep 5 

mysqldump -u ${sourceUser} -p${sourcePw} ${sourceDbName} ${systemTables} > ${sourceSystemTablesToCommitAndWriteToProd} 

mysqldump -u ${destUser} -p${destPw} ${destDbName} ${systemTables} > ${destSystemTables} 

sed -e 's/(/\n(/g' ${sourceSystemTablesToCommitAndWriteToProd} > ${sourceSystemTablesToCommitAndWriteToProd}".tokenized"
sed -e 's/(/\n(/g' ${destSystemTables} > ${destSystemTables}".tokenized"

systemDiff=`diff -b ${sourceSystemTablesToCommitAndWriteToProd}.tokenized ${destSystemTables}.tokenized`
systemDiff='Tokenized result of system tables diff (output files are: '${sourceSystemTablesToCommitAndWriteToProd}' & '${destSystemTables}'):'${systemDiff}

printf %s\n "${systemDiff}" > "../db/systemDiffTemp.txt"
printf %s\n "${systemDiff}"
echo
echo "(Also output to systemDiffTemp.txt)"
echo

echo "Exporting ${sourceDbName} and ${destDbName} study-specific tables, and diffing them."
sleep 5 

mysqldump -u ${sourceUser} -p${sourcePw} ${sourceDbName} ${studySpecificTablesToWriteToProd} > ${sourceStudySpecificTablesToCommitAndWriteToProd} 

mysqldump -u ${destUser} -p${destPw} ${destDbName} ${studySpecificTablesToWriteToProd} > ${destStudySpecificTables} 

sed -e 's/(/\n(/g' ${sourceStudySpecificTablesToCommitAndWriteToProd} > ${sourceStudySpecificTablesToCommitAndWriteToProd}".tokenized"
sed -e 's/(/\n(/g' ${destStudySpecificTables} > ${destStudySpecificTables}".tokenized"

studySpecificDiff=`diff -b ${sourceStudySpecificTablesToCommitAndWriteToProd}.tokenized ${destStudySpecificTables}.tokenized`
studySpecificDiff='Tokenized result of study-specific tables diff (output files are: '${sourceStudySpecificTablesToCommitAndWriteToProd}' & '${destStudySpecificTables}'):'${studySpecificDiff}

printf %s\n "${studySpecificDiff}" > "../db/studySpecificDiffTemp.txt"
printf %s\n "${studySpecificDiff}"
echo
echo "(Also output to studySpecificDiffTemp.txt)"
echo

echo "Commence with svn commit of ${sourceStructToCommitFileName}, ${sourceSystemTablesToCommitAndWriteToProd}, and ${sourceStudySpecificTablesToCommitAndWriteToProd}? Note that this will not update ${destDbName} - that option follows."
select yn_commit in "Yes" "No"; do
    case $yn_commit in
        Yes )
            break;;
        No )
            echo "Okay, exiting then";exit;;
    esac
done

writtenToProdMsg=""

echo "Also update the ${destDbName} DB with ${sourceSystemTablesToCommitAndWriteToProd} and ${sourceStudySpecificTablesToCommitAndWriteToProd}?"
select yn_dest_update in "Yes" "No"; do
    case $yn_dest_update in
        Yes )
            writtenToProdMsg=" ${destDbName} database updated."
            break;;
        No )
            writtenToProdMsg=" ${destDbName} database not modified."
            break;;
    esac
done

echo -n "Text to append to this script's default svn commit message: "
read comment

# svn commit source DB struct (w/ AUTO-INCREMENT intact)
mysqldump --no-data -u ${sourceUser} -p${sourcePw} ${sourceDbName} > ${sourceStructToCommitFileName} 
#| sed 's/Dump completed =[0-9]*\b//' 

msg=`svn commit -m "${sourceDbName} and ${destDbName} DB structures match. Committing $sourceStructToCommitFileName schema, ${sourceSystemTablesToCommitAndWriteToProd} ($systemTables) data & struct, and ${sourceStudySpecificTablesToCommitAndWriteToProd} ($studySpecificTablesToWriteToProd) data & struct. ${writtenToProdMsg}. Other comments: ${comment}" "$sourceStructToCommitFileName" "$sourceSystemTablesToCommitAndWriteToProd" "$sourceStudySpecificTablesToCommitAndWriteToProd"`
if [ -z $msg ]; then
    echo "Nothing to commit for $sourceStructToCommitFileName or $sourceSystemTablesToCommitAndWriteToProd" 
else
    echo "${msg}"
fi

echo "(Over)writing snapshot of todays ${destDbName} DB to filesystem...";

# save datestamped snapshot of entire dest DB to filesystem, only readable to owner
`rm -f ${destTodayFileName}`
mysqldump -u ${destUser} -p${destPw} ${destDbName} > ${destTodayFileName} 
`chmod u=r,g=,o= ${destTodayFileName}`
`chgrp www-data ${destTodayFileName}`

#echo "yn_dest_update = ${yn_dest_update}"

if [ "$yn_dest_update" = "Yes" ] ; then
    echo "Replacing ${destDbName}'s system and study-specific tables w/ current ${sourceDbName} snapshot..."
    # replace dest system tables w/ source system tables
    mysql -u ${destUser} -p${destPw} ${destDbName} < ${sourceSystemTablesToCommitAndWriteToProd} 
    # replace dest study-specific tables w/ source ones
    mysql -u ${destUser} -p${destPw} ${destDbName} < ${sourceStudySpecificTablesToCommitAndWriteToProd} 
fi

echo "Done.";
