#!/bin/sh

for file in $(find . -iname "*.twig.html"); do
    new=$(echo $file | sed "s/^\(.*\)\.twig\.html$/\1.html.twig/")
    echo "$file -> $new"
    mv $file $new
done

for file in $(find . -iname "*.twig"); do
    if echo "$file" | egrep "\.html\.twig$" > /dev/null; then
        nothingtodo=
    else
        new=$(echo $file | sed "s/^\(.*\)\.twig$/\1.html.twig/")
        echo "$file -> $new"
        mv $file $new
    fi
done
