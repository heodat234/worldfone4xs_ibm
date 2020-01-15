#!/bin/bash
BASEDIR=$(dirname "$0")
test=abc
echo $BASEDIR
echo $test

for i in 1 2 3 4 5
do
echo "loop $i"
done

j=1
while [ $j -le 5 ]
do
 echo "Hello world $j"
 j=`expr $j + 1`
done

exit $?