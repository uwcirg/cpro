# Initialize file permissions. Can be re-run if need be 

chgrp -R dusers ../../app
chmod -R u=rw,g=rw,o=r ../../app
chmod -R u+X,g+X,o+X ../../app
find ../../app -type d -print0 | xargs -0 chmod g+s

chgrp -R dusers ../../app/Config
chmod -R u=rw,g=rw ../../app/Config
chmod -R u+X,g+X ../../app/Config
find ../../app/Config -type d -print0 | xargs -0 chmod g+s

chgrp -R dusers ../../app/system_tools
chmod -R u=rwx,g=rwx,o= ../../app/system_tools

chmod -R u=rwx,g=rwx,o= ../../app/Console

chgrp -R www-data ../../app/tmp
chmod -R u=rw,g=rw,o= ../../app/tmp
chmod -R u+X,g+X ../../app/tmp
find ../../app/tmp -type d -print0 | xargs -0 chmod g+s

chgrp -R www-data ../../app/webroot/js/tmp
chmod -R u=rw,g=rw,o= ../../app/webroot/js/tmp
chmod -R u+X,g+X ../../app/webroot/js/tmp
find ../../app/webroot/js/tmp -type d -print0 | xargs -0 chmod g+s

chgrp -R www-data ../../app/securedata
chmod -R u=rw,g=rw,o= ../../app/securedata
chmod -R u+X,g+X ../../app/securedata
find ../../app/securedata -type d -print0 | xargs -0 chmod g+s

chmod -R 777 `find ../../ -name ".svn"`

chmod -R g+w `find ../../ -name ".htaccess"`
