#!/bin/bash
#
# #automysqlcheck.sh
# mcjustin mod of http://dev.mysql.com/doc/refman/5.0/en/check-table.html
#
# This is a small bash script that checks all mysql databases for errors
# and mails a log file to a specified email address. All variables are
# hardcoded for ease of use with cron. Any databases you wish not to check
# should be added to the DBEXCLUDE list, with a space in between each name.
#
# Note that DBEXCLUDE will only work with GNU sed, as BSD regular expressions
# on Darwin seem to have some trouble with word boundary anchors.
#
# original version by sbray@csc.uvic.ca, UVic Fine Arts 2004
#
# modified by eyechart AT gmail.com and Mickael Sundberg at mickael@pischows.se
# (see Change Log for details)
#
# Some of this code was inspired from automysqlbackup.sh.2.0
# http://sourceforge.net/projects/automysqlbackup/
#
#=====================================================================
# Change Log
#=====================================================================
#
# VER 1.2 - (2006-10-29)
# Added "\`" arround the tables in $DBTABLES, otherwise it'll create
# errors if tablenames containt characters like -.
# Modified by Mickael Sundberg
# VER 1.1 - (2005-02-22)
# Named script automysqlcheck.sh
# Added PATH variable to make this script more CRON friendly
# Removed the $DBTABLES loop and replaced it with single command
# that executes the CHECK TABLE command on all tables in a given DB
# Changed code to only check MyISAM and InnoDB tables
# Cleaned up output to make the email prettier
# Modified script to skip databases that have no tables
# Modified by eyechart
# VER 1 - (2004-09-24)
# Initial release by sbray@csc.uvic.ca


# system variables (change these according to your system)
PATH=/usr/local/bin:/usr/bin:/bin:$PATH
USER=whositgonnabe
PASSWORD=
DBHOST=localhost
#LOGFILE=/var/log/automysqlcheck.log
LOGFILE=/home/CIRG/mcjustin/esrac_utils/db/automysqlcheck.log
#MAILTO=root@localhost
MAILTO="mcjustin@uw.edu"
MAILTOCORRUPT="mcjustin@uw.edu, ew@uw.edu, gsbarnes@uw.edu, justinmc@speakeasy.net"
TYPE1= # extra params to CHECK_TABLE e.g. FAST
TYPE2=
CORRUPT=no # start by assuming no corruption
DBNAMES="all" # or a list delimited by space
#DBEXCLUDE="" # or a list delimited by space
#always exclude the information_schema table, it shows errors even when ok (for this version of MySQL)
DBEXCLUDE="information_schema" # or a list delimited by space

# I/O redirection...
touch $LOGFILE
exec 6>&1
exec > $LOGFILE # stdout redirected to $LOGFILE

echo -n "AutoMySQLCheck: "
date
echo "---------------------------------------------------------"; echo; echo

# Get our list of databases to check...
# NOTE: the DBEXCLUDE feature seemed to only work with Linux regex, GNU sed
if test $DBNAMES = "all" ; then
DBNAMES="`mysql --user=$USER --password=$PASSWORD --batch -N -e "show databases"`"
for i in $DBEXCLUDE
do
DBNAMES=`echo $DBNAMES | sed "s/\b$i\b//g"`
done
fi

# Run through each database and execute our CHECK TABLE command for all tables
# in a single pass - eyechart
for i in $DBNAMES
do
# echo the database we are working on
echo "Database being checked:"
echo -n "SHOW DATABASES LIKE '$i'" | mysql -t -u$USER -p$PASSWORD $i; echo

# Check all tables in one pass, instead of a loop
# Use GAWK to put in comma separators, use SED to remove trailing comma
# Modified to only check MyISAM or InnoDB tables - eyechart
DBTABLES="`mysql --user=$USER --password=$PASSWORD $i --batch -N -e "show table status;" \
| awk 'BEGIN {ORS=", " } $2 == "MyISAM" || $2 == "InnoDB"{print "\`" $1 "\`"}' | sed 's/, $//'`"

# Output in table form using -t option
if [ ! "$DBTABLES" ]
then
echo "NOTE: There are no tables to check in the $i database - skipping..."; echo; echo
else
echo "CHECK TABLE $DBTABLES $TYPE1 $TYPE2" | mysql -t -u$USER -p$PASSWORD $i; echo; echo
fi
done

exec 1>&6 6>&- # Restore stdout and close file descriptor #6

# test our logfile for corruption in the database...
for i in `cat $LOGFILE`
do
if test $i = "warning" ; then
CORRUPT=yes
elif test $i = "Warning" ; then
CORRUPT=yes
elif test $i = "error" ; then
CORRUPT=yes
elif test $i = "Error" ; then
CORRUPT=yes
fi
done

# Send off our results...
if test $CORRUPT = "yes" ; then
cat $LOGFILE | mail -s "MySQL CHECK Log [ERROR FOUND] for $DBHOST-`date`" $MAILTOCORRUPT
else
cat $LOGFILE | mail -s "MySQL CHECK Log [PASSED OK] for $HOST-`date`" $MAILTO
fi

