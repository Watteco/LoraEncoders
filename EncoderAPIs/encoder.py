import os
import cgi
import base64
import json
import sys

# Create path to get codec srcs
installDir=os.path.dirname(__file__)+"/../"
f = open (installDir + 'install.json', "r")
data = json.loads(f.read())
pathPythonStdCodecDir=os.path.dirname(data["pathPythonStdCodec"])
if len(pathPythonStdCodecDir) > 0:
	if (pathPythonStdCodecDir[0] != '/'):
		pathPythonStdCodecDir = installDir + pathPythonStdCodecDir 
f.close()

sys.path.append(pathPythonStdCodecDir)

from Decoding_Functions import *

#Fonction permettant de verifier la validite du JSON recupere
def isValidJSON(dataString):
  try:
    json_object = json.loads(dataString)
  except:
    return False
  return True
#Determination de l'environnement de lancement du script
if 'REQUEST_METHOD' in os.environ :
	#On ajoute les headers HTML necessaires
	print ("Content-Type: text/html")
	print ("")

	#Recuperation des arguments dans l'URL
	arguments = cgi.FieldStorage()

	#Verification de l'argument et lancement du script
	if arguments.has_key("json"):
		jsonString = arguments["json"].value
		if isValidJSON(jsonString):
			try:
				Encoding_JSON(jsonString)
			except Exception as e:
				print ("errorMsg: " + str(e))
		else:
			print ("errorMsg")
	else:
		print ("errorMsg")
else :
	#Verification de l'argument et lancement du script
	if len(sys.argv) == 2:
		jsonString = base64.b64decode(sys.argv[1])
		if isValidJSON(jsonString):
			try:
				Encoding_JSON(jsonString)
			except Exception as e:
				print ("errorMsg :" + str(e))
		else:
			print ("errorMsg")
	else:
		print("errorMsg")