#TODO sort by klasse
#TODO lehrerkürzel aus PDF extrahieren, oder als parameter angeben
#FIXME wenn keine Anrede, dann darf es nicht nach links geshiftet werden mögl. von hinten Eintrag beginnen


import re
import csv
from py_pdf_parser.loaders import load_file

document = load_file("combined.pdf")

with open('data.csv', 'w', newline='') as csvfile:

	writer = csv.writer(csvfile, delimiter=';', quoting=csv.QUOTE_MINIMAL)
	writer.writerow(['Form1','Form2','Form3','Form4','Form5','Form6'])

	for pagenumber in range(1,document.number_of_pages,2):
		page = document.get_page(pagenumber)
		adresse = re.findall('.*\n?',page.elements[0].text())
		
		page = document.get_page(pagenumber + 1 )
		name = re.findall('Name: (.*)\((.*)\)',page.elements[1].text() )

		res = []
		for item in adresse:
			res.append(item.replace("\n","")) if len(item) > 0 else 0
			
			
		for item in name[0]:
			res.append(item)
		print(res)
		writer.writerow(res)