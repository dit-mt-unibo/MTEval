# create survey to compare translations in maxdiff way (pick best and worst of each system)
# ask for directory to look in:
import glob
import random
from os import path

input_dir = input("Folder containing original.txt and translation files: ")
# find original.txt
if not path.exists(input_dir):
    print("Couldn't find folder ", input_dir)
    exit(0)
input_file = input_dir + '\\original.txt'
if not path.exists(input_file):
    print("Couldn't find file ", input_file)
    exit(0)
print("processing file: ", input_file)
with open(input_file) as orf:
    original = orf.readlines()
num_sentences = len(original)

num_systems = 0
translation = []

# find t?.txt, one for each system
translation_files = glob.glob(input_dir + "\\tr*.txt", recursive=False)
if len(translation_files) > 0:
    num_systems = len(translation_files)
    # Creates a list containing num_sentences lists, each of num_systems items, all set to ""
    translation = [["" for x in range(num_systems)] for y in range(num_sentences)]
    sys = 0
    for tr_filename in translation_files:
        with open(tr_filename) as tfile:
            lines = tfile.readlines()
            lc = 0
            for line in lines:
                translation[lc][sys] = line
                lc += 1
        sys += 1
else:
    print("no translation files found!")
    exit(2)

# we won't create the DB survey object, but we could.
# ask for survey ID:
survey_str = input("Survey Id: ")
surveyid = int(survey_str)

# now create the html file that contains the survey:

begin_html = "<!DOCTYPE html>\n<html><head><meta charset='UTF-8'><title>Valuta Traduzioni</title> " + \
             "<link rel='stylesheet' href='css/style.css'>\n" + \
             "<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'>" +\
             "</script> \n" + \
             "<script type='text/javascript' src='js/maxdiff.js'></script></head>\n" + \
             "<body><h1>Valuta le traduzioni</h1>\n" + \
             "<p>Vi chiediamo di valutare delle traduzioni diverse per lo stesso testo. Per ogni frase, " +\
             "vi preghiamo di scegliere la migliore e la peggiore traduzione tra quelle proposte.<br/>\n" +\
             "Quando avete finito, cliccate sul bottone verde in fondo. <br/>Grazie!</p>\n" +\
             "<div id='page-wrap'>" + \
             "<form id='myForm' surveyId='{0}'>"

end_html = "</form><div class='end'><button id='btn1' class='button'>Finito!</button></div>\n" +\
           "<div><p id='postdata'></p></div><p id='results'></p></div>\n" +\
           "</body></html>\n"

filename = "survey{0}.html"
#surveyid = 42
#num_systems = 4
# num_sentences = 3
# original = ["the first", "the second", "the third"]

# create an array with system IDs (0 to num_systems)
sys_arr = []
sys_arr = [k for k in range(num_systems)]

with open(str.format(filename, surveyid), "w") as f:
    f.write(str.format(begin_html, surveyid))
    tr_count = 1  # count individual translation, for each row
    for i in range(num_sentences):
        f.write("<div class='question'><table>")
        f.write("<tr><th> " + original[i] + "</th><th>Best</th><th>Worst</th></tr>")
        # shuffle the list of systems, so they appear in a different order each time.
        random.shuffle(sys_arr)
        for j in sys_arr:        # for j in range(num_systems):
            f.write("<tr><td> " + translation[i][j] + "</td>")
            # data-col must be one for all 'best' and another for all 'worst' of the same sentence to eval
            f.write("<td><input type='radio' name='row-" + str(tr_count) + "' data-col='" + str(i*2+1) + "' system='" + str
            (j) + "' sent='" + str(i) + "'></td>")
            f.write("<td><input type='radio' name='row-" + str(tr_count) + "' data-col='" + str(i*2) + "' system='" + str
            (j) + "' sent='" + str(i) + "'></td>")
            f.write("</tr>")
            tr_count += 1
        f.write("</table></div>")
    f.write(end_html)
