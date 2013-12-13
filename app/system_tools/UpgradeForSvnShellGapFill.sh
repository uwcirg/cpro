# THIS SCRIPT EXECUTES A CUSTOMIZED VERSION OF THE CAKE 1.3 -> 2.0 MIGRATION SCRIPT, AND DOES OTHER dhair2 SPECIFIC TASKS FOR MIGRATION.

#uncomment for debugging
#set -x

cake2BaseDir="${HOME}/cake2"
cake2Dir="${cake2BaseDir}/cakephp-2.3.0-RC1"
upgradeDir="${HOME}/cake2upgrade"
existingConfigDir="/srv/www/p3p-dev.cirg.washington.edu/htdocs/mazzone"
mergeDir="${HOME}/cake2mergetmp"

cd "$upgradeDir"
rm -rf ./* ./.htaccess ./.svn
svn checkout svn+ssh://svn.cirg.washington.edu/svnroot/dhair2/trunk/ .
cp -R ${cake2Dir}/lib ${upgradeDir}/
cp -R ${cake2Dir}/app/Console ${upgradeDir}/app/
cp ${cake2BaseDir}/UpgradeForSvnShell.php ${upgradeDir}/lib/Cake/Console/Command/
cd app
${upgradeDir}/app/Console/cake upgrade_for_svn all
cd ..
svn delete cake

cp ${existingConfigDir}/app/config/database.php ${upgradeDir}/app/Config/
# TODO change 'driver' => 'mysql', to 'datasource' => 'Database/Mysql',
svn mkdir ${upgradeDir}/app/Config/dhair
svn move ${upgradeDir}/app/config/dhair/*.php ${upgradeDir}/app/Config/dhair/
svn mkdir ${upgradeDir}/app/Config/Schema
svn move ${upgradeDir}/app/config/*.php ${upgradeDir}/app/Config/

#TODO BEFORE FINAL RUN: CREATE THESE AND COMPARE W/ 2.3 BEFOREHAND:
cp ${mergeDir}/bootstrap.php ${upgradeDir}/app/Config/ 
cp ${mergeDir}/core.php ${upgradeDir}/app/Config/
#Didn't include phpsession.php for now; testing will reveal whether I need it.
#TODO merge dhair config changes as seemed necessary...

#TODO edit bootstrap.php change config/dhair to Config/dhair
# TODO comment out the next line:
cp ${existingConfigDir}/app/config/dhair/dhair_p3pmazzone.php ${upgradeDir}/app/Config/dhair/

#We never edited this, so: 
cp ${cake2Dir}/app/Config/acl.ini.php ${upgradeDir}/app/Config/
cp ${cake2Dir}/app/Config/routes.php ${upgradeDir}/app/Config/

cp ${cake2Dir}/app/Config/acl.php ${upgradeDir}/app/Config/
svn add ${upgradeDir}/app/Config/acl.php
cp ${cake2Dir}/app/Config/email.php.default ${upgradeDir}/app/Config/
svn add ${upgradeDir}/app/Config/email.php.default
cp ${cake2Dir}/app/Config/Schema/* ${upgradeDir}/app/Config/Schema/
svn add ${upgradeDir}/app/Config/Schema/*

cp ${cake2Dir}/app/webroot/test.php ${upgradeDir}/app/webroot/
cp ${cake2Dir}/index.php ${upgradeDir}/
cp ${cake2Dir}/app/index.php ${upgradeDir}/app/
cp ${cake2Dir}/app/webroot/index.php ${upgradeDir}/app/webroot/

#no models were moved, for some reason (except behaviours - odd!) so, I did: 
svn move ${upgradeDir}/app/models/*.php ${upgradeDir}/app/Model/

# TODO in AppModel:
# App::uses('Model', 'Model');
# App::uses('Controller/Component', 'CakeSession');
# //App::import('Model','CakeSession');


svn mkdir app/View/ActivityDiaries
svn mkdir app/View/Admin
svn mkdir app/View/Associates
svn mkdir app/View/AudioFiles
svn mkdir app/View/ChartCodings
svn mkdir app/View/Charts
svn mkdir app/View/Clinicians
svn mkdir app/View/DataAccess
svn mkdir app/View/Elements
svn mkdir app/View/Errors
svn mkdir app/View/Journals
svn mkdir app/View/Layouts
svn mkdir app/View/Logs
svn mkdir app/View/MedicalRecords
svn mkdir app/View/P3p
svn mkdir app/View/Pages
svn mkdir app/View/Patients
svn mkdir app/View/Results
svn mkdir app/View/Scaffolds
svn mkdir app/View/Surveys
svn mkdir app/View/Teaching
svn mkdir app/View/Users

svn move app/views/activity_diaries/*.ctp app/View/ActivityDiaries/
svn move app/views/admin/*.ctp app/View/Admin/
svn move app/views/associates/*.ctp app/View/Associates/
svn move app/views/audio_files/*.ctp app/View/AudioFiles/
svn move app/views/chart_codings/*.ctp app/View/ChartCodings/
svn move app/views/charts/*.ctp app/View/Charts/
svn move app/views/clinicians/*.ctp app/View/Clinicians/
svn move app/views/data_access/*.ctp app/View/DataAccess/
svn move app/views/elements/*.ctp app/View/Elements/
svn move app/views/journals/*.ctp app/View/Journals/
svn move app/views/layouts/*.ctp app/View/Layouts/
svn move app/views/logs/*.ctp app/View/Logs/
svn move app/views/medical_records/*.ctp app/View/MedicalRecords/
svn move app/views/p3p/*.ctp app/View/P3p/
svn move app/views/pa/*.ctp app/View/P3p/
svn move app/views/pages/*.ctp app/View/Pages/
svn move app/views/pages/*.thtml app/View/Pages/
svn move app/views/patients/*.ctp app/View/Patients/
svn move app/views/results/*.ctp app/View/Results/
svn move app/views/surveys/*.ctp app/View/Surveys/
svn move app/views/teaching/*.ctp app/View/Teaching/
svn move app/views/users/*.ctp app/View/Users/
#For some reason, views/helpers/jsmin-1.1.1.php was not moved to View/Helpers by the upgrade script (though it was renamed properly to JSMin.php), so I moved it manually:
svn move app/views/helpers/JSMin.php app/View/Helpers/

cd app

cp ${cake2Dir}/app/View/Helper/AppHelper.php ${upgradeDir}/app/View/Helper/AppHelper.php
svn add View/Helper/AppHelper.php
#svn mkdir app/Helper

sed -i 's/Javascript/Js/g' `grep -Rl Javascript . | grep -v svn | grep elper`
sed -i 's/Javascript/Js/g' Controller/AppController.php
# vi View/Helper/MinifyHelper.php
# %s/Js->link/Html->script/gc

# TODO vi View/Helper/InstanceSpecificsHelper.php
# remove ClassRegistry reference and replace $view w/ $this->_View

# TODO
#   just before </body> in Views/Layouts/default & survey
#   echo $this->Js->writeBuffer(); 


svn revert views/helpers/jsmin-1.1.1.php
move views/helpers/jsmin-1.1.1.php View/Helper/JSMin.php
sed -i 's/jsmin-1.1.1/JSMin/g' View/Helper/MinifyHelper.php
rm View/Helpers # junk artifact of script...
# TODO vi View/Helper/JSMin.php and %s/JSMinException/Exception/gc , and remove class declaration

# TODO many model find() calls will need to be re-factored to remove pre 1.3 syntax
# Questionnaire.php
# Answer.php
# Tip.php


svn move Model/MeddayNonOpioid.php Model/MeddayNonopioid.php
# TODO vi Model/MeddayNonopioid.php to change that name there
sed -i 's/MeddayNonOpioid/MeddayNonopioid/g' Model/MeddayNonopioid.php

# TODO in View, replace Form fxn calls which pass all variables (eg ...,null,...), as most have one less than they did before due to the 'selected' param being removed
sed -i 's/null, null,/null,/g' View/Helper/PatientDataHelper.php
# TODO edit 5-6 other Form-> call in PatientDataHelper.php
# TODO edit 1 Form->select call in View/Elements/nonopioids.ctp
# TODO edit 1 Form->select call in View/Patients/medications.ctp 
# TODO edit 6-7 Form->select,hour,min calls in View/Patients/edit.ctp
# the following are helpful for the above:
# diff View/Patients/edit.ctp <(ssh mcjustin@oxygen.cirg.washington.edu 'cat ~/cake2upgrade/app/View/Patients/edit.ctp')
# scp mcjustin@oxygen.cirg.washington.edu:~/cake2upgrade/app/View/Patients/edit.ctp View/Patients/


# TODO clean up Form->create, passing first param model name (eg 'Patient') as needed, and removing eg controllers > 'patients', which doesn't seem to work anymore
# View/Patients/edit.ctp
# View/Patients/add.ctp
# View/Patients/consents.ctp
# View/Patients/medications.ctp // use 0 args
# View/Patients/search.ctp // use 0 args
# View/Patients/change_username.ctp
# View/P3p/priorities.ctp
# View/P3p/next_steps.ctp
# View/Admin/pwchange.ctp

# TODO Comment out the following in View/Elements/question.ctp
# 'htmlHelper' => $html,

sed -i 's/?url=$1//g' webroot/.htaccess

#TODO Controllers: add params to construct
# function __construct($request = null, $response = null)
# parent::__construct($request, $response)

sed -i 's/$appts = Set::sort/if (sizeof($appts) > 0) $appts = Set::sort/g' Controller/PatientsController.php

sed -i 's/initialize(&$controller/initialize($controller/g' Controller/Component/*.php
sed -i 's/initialize($controller, $settings = array()/initialize($controller/g' Controller/Component/*.php
sed -i 's/initialize($controller, $settings/initialize($controller/g' Controller/Component/*.php
sed -i 's/controller =& $controller/controller = $controller/g' Controller/Component/*.php


#OK TO IGNORE: The following were renamed but not moved to new locations, because they don't subclass the parent, I think: controllers/ components: logging_component & traverse
#THE FOLLOWING SEEMS REDUNDANT, NOT SURE WHY IT WAS STILL THERE
svn delete controllers/components/traverse.php

svn move Controller/Component/TraverseComponent.php Controller/Component/TraverseSkippedComponent.php
# TODO edit the above new file to remove other classes
# TODO edit the above to subclass Component, not TraverseComponent
# easiest to just copy what i've already done at other installs, since this won't be touched recently

#TODO vi `grep -Rl "RequestHandler->" . | grep -v svn`
# then in controllers:    then :%s/RequestHandler->/request->/gc

sed -i 's/RequestHandler->/request->/g' `grep -Rl "RequestHandler->" Controller/ | grep -v svn | grep -v log | grep -v omponent`
sed -i 's/RequestHandler->/controller->request->/g' `grep -Rl "RequestHandler->" Controller/Component | grep -v svn | grep -v log`

sed -i 's/getClientIP/clientIp/g' `grep -Rl getClientIP .`

# note the next line really only affects the DhairAuthComponent
# note also that the upgrade script does this more widely
sed -i 's/controller->data/controller->request->data/g' `grep -Rl "controller->data" .`

# TODO replace controller->request->data w/ controller->request->query where the request is GET, as request->data seems to always be populated in 2.0. May suffice to do this in DhairAuthComponent

# TODO I couldn't get this sed statement to work... there should just be one problem in this single file.
#sed -i 's/this->request->params[\'url\'][\'url\']/this->request->url/g' Controller/AppController.php

#TODO couldn't get the sed syntax correct on this: sed -i 's/authorize = \'controller\'/authorize = array(\'Controller\')' Controller/Component/DhairAuthComponent

sed -i 's/new Aro()/\$this->Acl->Aro/g' Controller/Component/DhairAuthComponent.php
sed -i 's/new Aco()/\$this->Acl->Aco/g' Controller/Component/DhairAuthComponent.php

#TODO UsersController.php will need Auth->login replaced w/ Auth->user. might want to just copy file over... 
sed -i 's/Auth->login($id)/Auth->login(array("id" => $id))/g' Controller/PatientsController.php

#TODO DhairAuthComponent.php : might want to just copy file over, for logging statements.


sed -i 's/this->request->data/user/g' Controller/Component/PasswordComponent.php

sed -i 's/config/Config/g' system_tools/setFilePermissions.sh

#TODO: Class loading with App::import - might need to replace w/ App::uses(), 
#AppController,
# TODO vi `grep -Rl "import('Sanitize')" Controller/ | grep -v svn`
# TODO %s/import(\'Sanitize\')/uses(\'Sanitize\', \'Utility\')/gc

svn move locale Locale 

${upgradeDir}/app/Console/cake upgrade_for_svn helpers
${upgradeDir}/app/Console/cake upgrade_for_svn i18n 
${upgradeDir}/app/Console/cake upgrade_for_svn basics
${upgradeDir}/app/Console/cake upgrade_for_svn request
${upgradeDir}/app/Console/cake upgrade_for_svn configure 
${upgradeDir}/app/Console/cake upgrade_for_svn constants
${upgradeDir}/app/Console/cake upgrade_for_svn components 
${upgradeDir}/app/Console/cake upgrade_for_svn exceptions
 
cd ..

