#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Crud:
    def __init__(self, mongodb, _mongodb, common):
        self.mongodb = mongodb
        self._mongodb = _mongodb
        self.common = common

        self.operators = {
            'eq'            : '$eq',
            'gt'            : '$gt',
            'gte'           : '$gte',
            'lt'            : '$lt',
            'lte'           : '$lte',
            'neq'           : '$ne',
            'isnotempty'    : '$exists'
        }
        self.model = []
        self.match = {}

    def create_model(self, collection=''):
        model_list = self._mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection})
        self.model = self.common.array_column(list_dict=list(model_list), value='field')

    def total(self, collection='', filter={}):
    
    def read(self, ):
        data = []
        total = 0
        return {data: data, total: total}

class Kendo_aggregate:
    def __init__(self):
        self.skip = 0
        self.limit = 1000
        self._model
        self._kendo_query
        self._aggregate
        self._default = {
            "sort"      : {
                "_id"   : -1
            },
            "skip"      : 0,
            "limit"     : 1000,
        }