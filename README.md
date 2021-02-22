# MTEval project

## Web application to evaluate (compare) translation systems

## Problem Statement
Given a text and one or more possible translations, 
conduct a 'survey' to evaluate the translations, 
that is to evaluate the translators - human or machine.

The idea is to support three different types of evaluation:
**Maxdiff : ** select best and worst translation for each sentence.
**Rating  : ** rate each translation with a value from 1 to 4 or 1 to 5 (similar to MTEqual software).
**Ranking : ** rank the systems from better to worse, from 1 to N, where N is the # of alternatives.

## Components

### Web
Web pages in php to create a survey definition, process results, display a report

### Db
Database script to create a MySql db.

### Backoffice
Python application (a simple command line script) to create a survey html page.

