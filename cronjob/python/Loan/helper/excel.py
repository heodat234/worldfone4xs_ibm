#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Excel:
    def __init__(self):
        import os
        import traceback
        import pandas as pd
        from pprint import pprint
        self.pd = pd
        self.pprint = pprint
        self.traceback = traceback

    def getDataExcel(self, file_path, active_sheet='Sheet1', header=None, names=None, index_col=None, usecols=None, dtype=None, converters=None, skiprows=None, na_values=None, keep_default_na=False, encoding='utf-8'):
        try:
            # self.pprint(names)
            data = self.pd.read_excel(file_path, sheet_name=active_sheet, header=header, names=names, index_col=index_col, usecols=usecols, dtype=dtype, converters=converters, skiprows=skiprows, na_values=na_values, keep_default_na=keep_default_na, encoding=encoding)
            # self.pprint(list(data))
            data.dropna(how="all", inplace=True)
            data.fillna('', inplace=True)
            return data
        except Exception as e:
            self.pprint(self.traceback.format_exc())
            # print(str(e))

    def getDataCSV(self, file_path, sep=',', lineterminator='\r', header=None, names=None, index_col=None, usecols=None, dtype=None, converters=None, skiprows=None, na_values=None, encoding=None, engine=None, keep_default_na=False, low_memory=True, quotechar='"'):
        data = self.pd.read_csv(file_path, sep=sep, header=header, names=names, index_col=index_col, usecols=usecols, dtype=dtype, converters=converters, skiprows=skiprows, na_values=na_values, encoding = encoding, engine = engine, keep_default_na=keep_default_na, low_memory=low_memory, quotechar=quotechar)
        data.dropna(how="all", inplace=True)
        data.fillna('', inplace=True)
        return data

    def testCSV(self, file_path, sep=',', lineterminator='\r', header=None, names=None, index_col=None, usecols=None, dtype=None, converters=None, skiprows=None, na_values=None, encoding='utf-8', engine=None):
        try:
            data = self.pd.read_csv(file_path, header=header, names=names, index_col=index_col, usecols=usecols, dtype=dtype, converters=converters, skiprows=skiprows, na_values=na_values, encoding = encoding, engine = engine)
        # data.dropna(how="all", inplace=True)
        except Exception as e:
            print(str(e))
        return data

    def getColumnIndex(self, index_num_list=[]):
        return self.pd.Series(index_num_list)

    # def writeDataToCSV(self, file_path='', sep=',')