# CreateSurvey.py

Application to create an html survey for maxdiff, based on text files.
It is a command-line script, no UI, no automation.
It will ask for a **folder** containing a file **'original.txt'**, which represents the text to translate, one sentence per line.
In the same folder, the program looks for tr*.txt files, each one containing a translation output for the 'original' text.
Finally, the program asks for a **survey ID**, which should have been created using the php web site.
The output is a file with name survey<ID>.html in the same folder where the input files were.

The file should be manually copied / deployed where the web app MTEval is installed.
The html file is a simple form and uses the logic in the shared js file of MTEval to post data to the server.

### Future development
Lots of features should be added:
- a UI :)
- a UI to choose files without naming convention, for original and for each desired system to evaluate.
- command line params to pick the folder, in alternative to a UI.
- automatic creation of the Survey record in the MySql db, since the program can just ask for a friendly Name,
and all other data is known from the text files.
- of course, options to choose a type of survey, and create the appropriate html for Maxdiff, Ranking, Rating.
