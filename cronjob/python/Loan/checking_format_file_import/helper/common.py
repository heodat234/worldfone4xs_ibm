#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Common:
    def __init__(self):
        import calendar, time
        import sys
        import re
        import json
        import os
        from pprint import pprint
        from datetime import date, timedelta, datetime
        from pathlib import Path
        self.pprint = pprint
        self.calendar = calendar
        self.time = time
        self.re = re
        self.json = json
        self.date = date
        self.datetime = datetime
        self.Path = Path
        self.os = os
        self.sys = sys
        self.download_folder = '/data/upload_file/'
        self.config_file = '/data/python_config.json'

    def getSubUser(self, type, collection):
        typeList = {
            'TS': 'TS',
            'LO': 'LO'
        }
        return typeList[type] + '_' + collection

    def getFullPath(self, projectName='', path=''):
        return '/var/www/html/' + projectName + '/' + path

    def convertStr(self, value, formatType=''):
        try:
            seperator = ', '
            result = ''
            if isinstance(value, list):
                result = seperator.join(value)
            else:
                result = str(value)
                result = self.re.sub(' +', ' ', result)
                result = result.lstrip()
                result = result.rstrip()

            return str(result)
        except Exception as e:
            str(e)

    '''
    %a - abbreviated weekday name
    %A - full weekday name
    %b - abbreviated month name
    %B - full month name
    %c - preferred date and time representation
    %C - century number (the year divided by 100, range 00 to 99)
    %d - day of the month (01 to 31)
    %D - same as %m/%d/%y
    %e - day of the month (1 to 31)
    %g - like %G, but without the century
    %G - 4-digit year corresponding to the ISO week number (see %V).
    %h - same as %b
    %H - hour, using a 24-hour clock (00 to 23)
    %I - hour, using a 12-hour clock (01 to 12)
    %j - day of the year (001 to 366)
    %m - month (01 to 12)
    %m1- month (1 to 12)
    %M - minute
    %n - newline character
    %p - either am or pm according to the given time value
    %r - time in a.m. and p.m. notation
    %R - time in 24 hour notation
    %S - second
    %t - tab character
    %T - current time, equal to %H:%M:%S
    %u - weekday as a number (1 to 7), Monday=1. Warning: In Sun Solaris Sunday=1
    %U - week number of the current year, starting with the first Sunday as the first day of the first week
    %V - The ISO 8601 week number of the current year (01 to 53), where week 1 is the first week that has at least 4 days in the current year, and with Monday as the first day of the week
    %W - week number of the current year, starting with the first Monday as the first day of the first week
    %w - day of the week as a decimal, Sunday=0
    %x - preferred date representation without the time
    %X - preferred time representation without the date
    %y - year without a century (range 00 to 99)
    %Y - year including the century
    %Z or %z - time zone or name or abbreviation
    %% - a literal % character
    '''
    def convertTimestamp(self, value, formatString="%d/%m/%Y"):
        if formatString in ["%d/%m/%Y", "%d/%m/%y", "%d-%m-%Y", "%d-%m-%y", "%d%m%Y", "%d%m%y", "%Y-%m-%d %H:%M:%S"]:
            if len(str(value)) < 6:
                value = '0' + str(value)
        result = int(self.time.mktime(self.time.strptime(str(value), formatString)))
        return result

    def convertInt(self, value, formatType=''):
        try:
            if value in ['']:
                value = 0
            if isinstance(value, str):
                value = value.replace(',', '')
            return int(value)
        except Exception as e:
            str(e)

    def convertBoolean(self, value, formatType=''):
        return bool(value)
    
    def convertDouble(self, value, formatType=''):
        try:
            if value in ['']:
                value = 0
            if isinstance(value, str):
                value = value.replace(',', '')
            return float(value)
        except Exception as e:
            str(e)

    def convertDefault(self, value, formatType=''):
        return value

    def convertDatetime(self, value, formatType="%d/%m/%Y %H:%M:%S"):
        result = ''
        if isinstance(value, int):
            result = self.time.strftime(formatType, self.time.localtime(value))
        return result

    def convertDataType(self, data, datatype='', formatType=''):
        switcher = {
            'string'        : self.convertStr,
            'timestamp'     : self.convertTimestamp,
            'int'           : self.convertInt,
            'boolean'       : self.convertBoolean,
            'double'        : self.convertDouble,
            'array'         : self.convertDefault,
            'ObjectId'      : self.convertDefault,
            'arrayObject'   : self.convertDefault,
            'arrayObjectId' : self.convertDefault,
            'phone'         : self.convertDefault,
            'arrayPhone'    : self.convertDefault,
            'name'          : self.convertDefault,
            'datetime'      : self.convertDatetime,
        }
        return switcher[datatype](data, formatType)

    def getDownloadFolder(self):
        wff_env = self.wff_env(self.base_url())
        
        if wff_env in ['UAT']:
            # serverfolder = 'YYYYMMDD'
            today = self.datetime.strptime('03/02/2020', "%d/%m/%Y").date() 
            serverfolder = today.strftime("%Y%m%d")
        else:
            today = self.date.today()
            serverfolder = today.strftime("%Y%m%d")
        return self.download_folder + serverfolder + '/'

    def countWorkingDaysBetweendate(self, starttime, endtime, mongodb):
        count_days = 0
        while starttime <= endtime:
            date = self.datetime.fromtimestamp(starttime)
            isHoliday = mongodb.getOne(MONGO_COLLECTION='LO_Report_off_sys', WHERE={'off_date': starttime})
            if isHoliday is None:
                count_days += 1
            starttime += 86400
        return count_days

    def base_url(self):
        config = {}
        base_url = ''
        if self.os.path.isfile('/data/python_config.json'):
            with open('/data/python_config.json') as f:
                config = self.json.load(f)
                base_url = config['base_url']
        return base_url

    def wff_env(self, base_url):
        wff_env = ''
        sysConfig = {}
        if self.os.path.isfile(self.base_url() + 'system/config/wffdata.json'):
            with open(self.base_url() + 'system/config/wffdata.json') as f:
                sysConfig = self.json.load(f)
                wff_env = sysConfig['wff_env']
        return wff_env

    def array_column(self, list_dict=[], value='', index=''):
        try:
            if index != '':
                return map(lambda x: {x[index]: x[value]}, list_dict)
            else:
                return map(lambda x: x[value], list_dict)
        except Exception as e:
            return str(e)
