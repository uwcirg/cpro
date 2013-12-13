# Uncomment the next line for testing
#set -x

systemTables="acos aros aros_acos"
studySpecificTablesToWriteToProd="alerts clinics conditions items options pages projects projects_questionnaires questionnaires questions scales sites subscales teaching_tips teaching_tips_percentages targets categories"
today=`date +"%m%d%y"`
devDbConfig='../config/database.php'
devDbName=`grep -m 1 "'database' =>" ${devDbConfig} | cut -f4 -d"'"`
devStructTodayFileName="${devDbName}_struct_${today}.sql"
devStructToCommitFileName="${devDbName}_db_struct.sql"
devSystemTablesToCommitAndWriteToProd="${devDbName}_system_tables.sql"
devStudySpecificTablesToCommitAndWriteToProd="${devDbName}_study_specific_tables.sql"
devUser=`grep -m 1 "'login' =>" ${devDbConfig} | cut -f4 -d"'"`
devPw=`grep -m 1 "'password' =>" ${devDbConfig} | cut -f4 -d"'"`

prodDbConfig='/srv/www/esrac.cirg.washington.edu/htdocs/app/config/database.php'
prodDbName=`grep -m 1 "'database' =>" ${prodDbConfig} | cut -f4 -d"'"`
prodStructTodayFileName="${prodDbName}_struct_${today}"".sql"
prodSystemTables="${prodDbName}_system_tables_${today}.sql"
prodStudySpecificTables="${prodDbName}_study_specific_tables_${today}.sql"
prodTodayFileName="${prodDbName}_${today}.sql"
prodUser=`grep -m 1 "'login' =>" ${prodDbConfig} | cut -f4 -d"'"`
prodPw=`grep -m 1 "'password' =>" ${prodDbConfig} | cut -f4 -d"'"`

echo "This script compares the schema and content of the ${devDbName} and ${prodDbName} databases, optionally commits the ${devDbName} version to subversion, and also optionally updates the struct and data of the ${prodDbName} tables that don't contain instance-specific data, PHI, etc."

echo "Exporting struct of ${devDbName} and ${prodDbName}, and diffing them."

#the following didn't seem to work: --ignore-table=${devDbName}.%_deleted 
mysqldump --no-data -u ${devUser} -p${devPw} ${devDbName} --skip-comments --ignore-table=${devDbName}.tickets | sed 's/AUTO_INCREMENT=[0-9]*\b//' > ${devStructTodayFileName} 

# the following didn't seem to work: --ignore-table=${prodDbName}.%_deleted 
#mysqldump --no-data -u ${prodUser} -p${prodPw} ${prodDbName} --ignore-table=${prodDbName}.patient_T1s --ignore-table=${prodDbName}.patient_T1s_SCCA | sed 's/AUTO_INCREMENT=[0-9]*\b//' > ${prodStructTodayFileName} 

mysqldump --no-data -u ${prodUser} -p${prodPw} ${prodDbName} \
--skip-comments | sed 's/AUTO_INCREMENT=[0-9]*\b//' > ${prodStructTodayFileName}
# --ignore-table for views has a bug ...
#--ignore-table=${prodDbName}.clinicians_deleted \
#--ignore-table=${prodDbName}.T2_audio_file_view \
#--ignore-table=${prodDbName}.T2_audio_file_view_count \
#--ignore-table=${prodDbName}.audio_file_norecordingmade_view \
#--ignore-table=${prodDbName}.consented_patients_T1s \
#--ignore-table=${prodDbName}.consented_patients_T1s_count \
#--ignore-table=${prodDbName}.logs_intervention_non_test \
#--ignore-table=${prodDbName}.logs_intervention_non_test_count \
#--ignore-table=${prodDbName}.participants_T2_reportable_view \
#--ignore-table=${prodDbName}.participants_T2_reportable_view_count \
#--ignore-table=${prodDbName}.patients_clinics_view \
#--ignore-table=${prodDbName}.patients_clinics_view_count \
#--ignore-table=${prodDbName}.scales_subscales_view \
#--ignore-table=${prodDbName}.survey_sessions_non_test_view_count \
#--ignore-table=${prodDbName}.treatment_participants_wo_trtmnt_start \
#--ignore-table=${prodDbName}.treatment_participants_wo_trtmnt_start_count \
#--ignore-table=${prodDbName}.T2_audio_file_view \
#--ignore-table=${prodDbName}.T2_audio_file_view_count \
#--ignore-table=${prodDbName}.audio_file_norecordingmade_view \
#--ignore-table=${prodDbName}.consented_patients_T1s \
#--ignore-table=${prodDbName}.consented_patients_T1s_count \
#--ignore-table=${prodDbName}.logs_intervention_non_test \
#--ignore-table=${prodDbName}.logs_intervention_non_test_count \
#--ignore-table=${prodDbName}.participants_T2_reportable_view \
#--ignore-table=${prodDbName}.participants_T2_reportable_view_count \
#--ignore-table=${prodDbName}.patient_T1s \
#--ignore-table=${prodDbName}.patient_T1s_SCCA \
#--ignore-table=${prodDbName}.patients_clinics_view \
#--ignore-table=${prodDbName}.patients_clinics_view_count \
#--ignore-table=${prodDbName}.scales_subscales_view \
#--ignore-table=${prodDbName}.survey_sessions_non_test_view_count \
#--ignore-table=${prodDbName}.treatment_participants_wo_trtmnt_start \
#--ignore-table=${prodDbName}.treatment_participants_wo_trtmnt_start_count \

structDiff=`diff ${devStructTodayFileName} ${prodStructTodayFileName}`

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

echo "Exporting ${devDbName} and ${prodDbName} system tables, and diffing them."
sleep 5 

mysqldump -u ${devUser} -p${devPw} ${devDbName} ${systemTables} > ${devSystemTablesToCommitAndWriteToProd} 

mysqldump -u ${prodUser} -p${prodPw} ${prodDbName} ${systemTables} > ${prodSystemTables} 

sed -e 's/(/\n(/g' ${devSystemTablesToCommitAndWriteToProd} > ${devSystemTablesToCommitAndWriteToProd}".tokenized"
sed -e 's/(/\n(/g' ${prodSystemTables} > ${prodSystemTables}".tokenized"

systemDiff=`diff ${devSystemTablesToCommitAndWriteToProd}.tokenized ${prodSystemTables}.tokenized`
systemDiff='Tokenized result of system tables diff (output files are: '${devSystemTablesToCommitAndWriteToProd}' & '${prodSystemTables}'):'${systemDiff}

printf %s\n "${systemDiff}" > "systemDiffTemp.txt"
printf %s\n "${systemDiff}"
echo
echo "(Also output to systemDiffTemp.txt)"
echo

echo "Exporting ${devDbName} and ${prodDbName} study-specific tables, and diffing them."
sleep 5 

mysqldump -u ${devUser} -p${devPw} ${devDbName} ${studySpecificTablesToWriteToProd} > ${devStudySpecificTablesToCommitAndWriteToProd} 

mysqldump -u ${prodUser} -p${prodPw} ${prodDbName} ${studySpecificTablesToWriteToProd} > ${prodStudySpecificTables} 

sed -e 's/(/\n(/g' ${devStudySpecificTablesToCommitAndWriteToProd} > ${devStudySpecificTablesToCommitAndWriteToProd}".tokenized"
sed -e 's/(/\n(/g' ${prodStudySpecificTables} > ${prodStudySpecificTables}".tokenized"

studySpecificDiff=`diff ${devStudySpecificTablesToCommitAndWriteToProd}.tokenized ${prodStudySpecificTables}.tokenized`
studySpecificDiff='Tokenized result of study-specific tables diff (output files are: '${devStudySpecificTablesToCommitAndWriteToProd}' & '${prodStudySpecificTables}'):'${studySpecificDiff}

printf %s\n "${studySpecificDiff}" > "studySpecificDiffTemp.txt"
printf %s\n "${studySpecificDiff}"
echo
echo "(Also output to studySpecificDiffTemp.txt)"
echo

echo "Commence with svn commit of ${devStructToCommitFileName}, ${devSystemTablesToCommitAndWriteToProd}, and ${devStudySpecificTablesToCommitAndWriteToProd}? Note that this will not update ${prodDbName} - that option follows."
select yn_commit in "Yes" "No"; do
    case $yn_commit in
        Yes )
            break;;
        No )
            echo "Okay, exiting then";exit;;
    esac
done

writtenToProdMsg=""

echo "Also update the ${prodDbName} DB with ${devSystemTablesToCommitAndWriteToProd} and ${devStudySpecificTablesToCommitAndWriteToProd}?"
select yn_prod_update in "Yes" "No"; do
    case $yn_prod_update in
        Yes )
            writtenToProdMsg=" ${prodDbName} database updated."
            break;;
        No )
            writtenToProdMsg=" ${prodDbName} database not modified."
            break;;
    esac
done

echo -n "Text to append to this script's default svn commit message: "
read comment

# svn commit dev DB struct (w/ AUTO-INCREMENT intact)
mysqldump --no-data -u ${devUser} -p${devPw} ${devDbName} > ${devStructToCommitFileName} 
#| sed 's/Dump completed =[0-9]*\b//' 

msg=`svn commit -m "${devDbName} and ${prodDbName} DB structures match. Committing $devStructToCommitFileName schema, ${devSystemTablesToCommitAndWriteToProd} ($systemTables) data & struct, and ${devStudySpecificTablesToCommitAndWriteToProd} ($studySpecificTablesToWriteToProd) data & struct. ${writtenToProdMsg}. Other comments: ${comment}" "$devStructToCommitFileName" "$devSystemTablesToCommitAndWriteToProd" "$devStudySpecificTablesToCommitAndWriteToProd"`
if [ -z $msg ]; then
    echo "Nothing to commit for $devStructToCommitFileName or $devSystemTablesToCommitAndWriteToProd" 
else
    echo "${msg}"
fi

echo "(Over)writing snapshot of todays ${prodDbName} DB to filesystem...";

# save datestamped snapshot of entire prod DB to filesystem, only readable to owner
`rm -f ${prodTodayFileName}`
mysqldump -u ${prodUser} -p${prodPw} ${prodDbName} > ${prodTodayFileName} 
`chmod u=r,g=,o= ${prodTodayFileName}`
`chgrp www-data ${prodTodayFileName}`

echo "yn_prod_update = ${yn_prod_update}"

if [ "$yn_prod_update" = "Yes" ] ; then
    echo "Replacing ${prodDbName}'s system and study-specific tables w/ current ${devDbName} snapshot..."
    # replace prod system tables w/ dev system tables
    mysql -u ${prodUser} -p${prodPw} ${prodDbName} < ${devSystemTablesToCommitAndWriteToProd} 
    # replace prod study-specific tables w/ dev ones
    mysql -u ${prodUser} -p${prodPw} ${prodDbName} < ${devStudySpecificTablesToCommitAndWriteToProd} 
fi

echo "Done.";
