#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
import json
import csv
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

try:
    excel = Excel()
    common = Common()
    base_url = common.base_url()
    wff_env = common.wff_env(base_url)

    mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
    _mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
    file_path = '/data/test/importNote_test.xlsx'
    subUserType = 'LO'
    collection = common.getSubUser(subUserType, 'Note')
    inputDataRaw = excel.getDataExcel(file_path=file_path, active_sheet="Sheet1", header=None, names=modelColumns, na_values='')
    pprint(inputDataRaw)
except Exception as e:
    pprint(str(e))
