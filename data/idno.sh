#!/usr/bin/env bash

HOME_ADMIN="/Users/Admin"

export PATH=$PATH:/usr/local/bin:/usr/local/git/bin:/usr/local/jruby/bin:/usr/local/apache-maven/bin
export CLASSPATH=$CLASSPATH:$HOME_ADMIN/Library/saxon/saxon9he.jar

beehiveHomeDev="$HOME_ADMIN/beehive.dev"
beehiveHomeProd="$HOME_ADMIN/beehive"
beehiveDataDev="$beehiveHomeDev/src/Papyrillio/BeehiveBundle/Resources/data"
beehiveDataProd="$beehiveHomeProd/src/Papyrillio/BeehiveBundle/Resources/data"
log="$beehiveDataDev/idno.log" # cl: log to dev

date > $log

echo "PATH: $PATH" >> $log
echo "CLASSPATH: $CLASSPATH" >> $log

gitRepository="$HOME_ADMIN/idp.data/master_readonly"
gitDirectory="$gitRepository/.git"
xql="$beehiveDataDev/idno.xql" # cl: use development xql
xmlDev="$beehiveDataDev/idno.xml"
xmlProd="$beehiveDataProd/idno.xml"

echo "git repos: $gitRepository" >> $log
echo "git dir  : $gitDirectory" >> $log
echo "xql (dev): $xql" >> $log
echo "xmldev   : $xmlDev" >> $log
echo "xmlprod  : $xmlProd" >> $log

echo "git update ... (master branch in Ordner master_readonly)" >> $log
date >> $log
git --git-dir $gitDirectory checkout . >> $log 2>&1
#git --git-dir $gitDirectory clean -fd >> $log 2>&1
git --git-dir $gitDirectory fetch >> $log 2>&1
git --git-dir $gitDirectory merge origin/master >> $log 2>&1
date >> $log
echo "... git update done." >> $log

echo "xql query run ... (run in dev and copy to prod)" >> $log
date >> $log
java -Xms512m -Xmx1536m net.sf.saxon.Query -q:$xql idpData=$gitRepository > $xmlDev 2>> $log
cp $xmlDev $xmlProd 2>> $log
date >> $log
echo "... xql query done." >> $log

echo "register update dev ..." >> $log
date >> $log
cd $beehiveHomeDev
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/UpdateRegister --append >> $log 2>&1
date >> $log
echo "... register update dev done." >> $log

echo "register update prod ..." >> $log
date >> $log
cd $beehiveHomeProd
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/UpdateRegister --append >> $log 2>&1
date >> $log
echo "... register update prod done." >> $log

date >> $log

exit 0
