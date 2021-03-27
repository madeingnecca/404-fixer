# 404 Fixer

## Scenario
You have just started working on a already deployed project to add new features.
You downloaded the latest version of database from production, latest version of code from source control. You couldn't download all resources (images/documents) from production because there are a lot of files and document root is very large.
You would like to download missing files only when they are required.

## Overview
This script solves 404 errors caused by missing files in local version which instead exist in production.

## Usage
* Copy `404_fixer.php` into the document root of your website;
* Modify your `.htaccess` to that this script will handle 404s caused by missing images/documents;

## Example
Have a look at the `example` folder for a practical use case.

## TODO
Test and provide documentation for websites using nginx instead of apache.
