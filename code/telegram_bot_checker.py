import xlsxwriter
import os
import glob
import hashlib
from zipfile import ZipFile
import requests
from collections import namedtuple
import subprocess
import re
from datetime import datetime
import json
from pathlib import Path
import wget
from openpyxl import load_workbook
from openpyxl import Workbook
import html
import urllib.request
import time


now = datetime.now()
date_time = now.strftime("%d.%m.%Y_%H.%M.%S")

workbook = Workbook()
sheet = workbook.active

sheet["A1"] = "SL No."
sheet["B1"] = "BOT URL" 
sheet["C1"] = "GET ME"
sheet["D1"] = "GET COMMANDS"
sheet["E1"] = "GET UPDATES"
sheet["F1"] = "GET WEBHOOK INFO"
sheet["G1"] = "Get Chat"
sheet["H1"] = "Get Chat Member Count"
sheet["I1"] = "Get Chat Administrators"
sheet["J1"] = "Export Chat Invite Link"
sheet["K1"] = "Chat id"
sheet["L1"] = "Bot Token"
sheet["M1"] = "SHA-1"
sheet["N1"] = "Create Invite Link"

MY_EXCEL_FILE_NAME = "TelegramBotAnalysisFinal-"+date_time+".xlsx"

workbook.save(filename=MY_EXCEL_FILE_NAME)

f = open("tel_bots.txt","r")
index=2

bot_number = 2
for x in f:
	bot_url = "https://api.telegram.org/bot"+x.split(":")[1]+":"+x.split(":")[2].strip()
	print("BOT NUMBER "+str(bot_number-1))
	sheet["A"+str(bot_number)] = str(bot_number-1)	
	print("BOT URL : "+bot_url+"\n")	

	sheet["B"+str(bot_number)] = str(bot_url)
	print("GET ME")

	result = os.popen("curl -X GET "+bot_url+"/getMe").read()
	#contents = urllib.request.urlopen().read()
	print(result+"\n")
	sheet["C"+str(bot_number)] = result	

	print("GET COMMANDS")
	result = os.popen("curl -X GET "+bot_url+"/getMyCommands").read()
	#contents = urllib.request.urlopen(x.strip()+"/getMyCommands").read()
	print(result+"\n")	
	sheet["D"+str(bot_number)] = result	
		
	print("GET UPDATES")
	result = os.popen("curl -X GET "+bot_url+"/getUpdates").read()
	#contents = urllib.request.urlopen(x.strip()+"/getUpdates").read()
	print(result+"\n")
	sheet["E"+str(bot_number)] = result	
	
	print("GET WEBHOOK INFO")
	result = os.popen("curl -X GET "+bot_url+"/getWebhookInfo").read()
	#contents = urllib.request.urlopen(x.strip()+"/getWebhookInfo").read()
	print(result+"\n")
	sheet["F"+str(bot_number)] = result

	
	print("Get Chat")
	result = os.popen("curl -X GET "+bot_url+"/getChat?chat_id="+x.split(":")[3].strip()).read()
	print(result+"\n")
	sheet["G"+str(bot_number)] = result


	print("Get Chat Member Count")
	result = os.popen("curl -X GET "+bot_url+"/getChatMemberCount?chat_id="+x.split(":")[3].strip()).read()
	print(result+"\n")
	sheet["H"+str(bot_number)] = result

	
	print("Get Chat Administrators")
	result = os.popen("curl -X GET "+bot_url+"/getChatAdministrators?chat_id="+x.split(":")[3].strip()).read()
	print(result+"\n")
	sheet["I"+str(bot_number)] = result
	
#	print("Export Chat Invite Link")
#	result = os.popen("curl -X GET "+bot_url+"/createChatInviteLink?chat_id="+x.split(":")[3].strip()).read()
#	print(result+"\n")
#	sheet["J"+str(bot_number)] = result

#	print("Create Additional Chat Invite Link")
#	result = os.popen("curl -X GET "+bot_url+"/createChatInviteLink?chat_id="+x.split(":")[3].strip()).read()
#	print(result+"\n")
#	sheet["N"+str(bot_number)] = result

	sheet["K"+str(bot_number)] = x.split(":")[3].strip() #chat_id
	sheet["L"+str(bot_number)] = x.split(":")[1].strip() + x.split(":")[2].strip() #token
	sheet["M"+str(bot_number)] = x.split(":")[0].strip() #zip_sha1
	
	print("============================")
	bot_number=bot_number+1
	time.sleep(1)
	workbook.save(filename=MY_EXCEL_FILE_NAME)
workbook.save(filename=MY_EXCEL_FILE_NAME)
f.close()
workbook.close()
