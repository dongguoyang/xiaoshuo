#!/bin/bash
path=/www/wwwroot/novel_sys
cd $path
alivet=`ps -aux|grep queue|grep -v grep|wc -l`
if [[ $alivet -eq 0 ]]
then
php artisan queue:listen --timeout=10000 > /dev/null &
fi
