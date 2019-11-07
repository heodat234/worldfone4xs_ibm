#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/testReadFileNoExt.txt","a")

import ftplib
import calendar
import time
import sys
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
from ftp import Ftp
from pprint import pprint
from mongod import Mongodb
from excel import Excel
from datetime import datetime
from datetime import date
from bson import ObjectId
from common import Common
from dateutil import parser
import pandas as pd
import csv

try:
    importData = []
    now = datetime.now()
    filepath = '/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/LIST_OF_ACCOUNT_IN_COLLECTION_20190812'
    header = ['account_no', 'phone', 'cus_name', 'type', 'curr', 'due_date', 'overdue_amt', 'cur_bal', 'overdue_date']
    with open(filepath, 'r', newline='\n', encoding='ISO-8859-1') as fin, open(filepath + '.csv', 'w', newline='\n', encoding='ISO-8859-1') as fout:
        # o=csv.writer(fout)
        for line in fin:
            row = line.split()
            if len(row) > 1:
                if isinstance(row[1], str) and len(row[1]) > 12 and row[1].isdigit():
                    temp = {}
                    temp[header[0]] = row[1]
                    temp[header[1]] = row[2]
                    temp[header[-1]] = row[-1]
                    temp[header[-2]] = row[-2]
                    temp[header[-3]] = row[-3]
                    temp[header[-4]] = row[-4]
                    temp[header[-5]] = row[-5]
                    temp[header[-6]] = row[-6]
                    listName = row[3:-7]
                    temp[header[2]] = ' '.join(listName)
                    importData.append(temp)

                    # temp = {}
                    # for key, value in enumerate(row):
                    #     if key == 0:
                    #         continue
                    #     if key == 1:
                    #         temp['account_no'] = value
                    #     if key == len(row) - 1:
                    #         temp['overdue_date'] = value
                    #     if key == len(row) - 2:
                    #         temp['cur_bal'] = value
                    #     if key == 

            # for value in enumerate(dataRaw):
            #     pprint(value)
            # if isinstance(rowData['1'], str) and len(rowData['1']) > 12 and rowData['1'].isdigit():
                # pprint(rowData)

            # o.writerow(line.split())
    # with open(filepath, 'r', encoding='ISO-8859-1', newline='\n') as csvfile:
    #     stringLine = csvfile.split()
    #     pprint(stringLine)
        # for row in csvfile:
        #     pprint(row)
        # pprint(csvfile)
        # spamReader = csv.reader(csvfile, delimiter='\t')
        # for row in spamReader:
        #     pprint(row)
    # f = open(filepath, 'r', encoding='ISO-8859-1')
    # csv_reader = csv.reader(f, delimiter=' ')
    # pprint(csv_reader)
    # f.close()
    # f1 = open(filepath + '.csv', 'w')
    # csv.writer(f, delimiter='\t')
    # f1.close()
    # data = pd.read_csv(filepath + '.csv', sep='\t')
    # pprint(data)
    # pprint(csv_reader)
    # for csvRow in csv_reader:
    #     pprint(csvRow)
    # df = pd.DataFrame(data=f.read())
    # print(f.read())
    # pprint(df)
    # for x in f:
    #     pprint(x)

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
