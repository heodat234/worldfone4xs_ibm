#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Mongodb:
    def __init__(self, MONGODB, WFF_ENV=''):
        import pymongo
        import bson
        self.bson = bson
        self.pymongo = pymongo
        if WFF_ENV in ['UAT', 'DEV']:
            connection = self.pymongo.MongoClient('127.0.0.1', 27017)
        else:
            connection = self.pymongo.MongoClient('127.0.0.1', 27017, username='worldfone4x', password='St3l37779db')
        self.connection = connection
        self.MONGODB = MONGODB
        self.db = connection[self.MONGODB]
        mongo_common = Mongo_common()

    def create_db(self, DB_NAME=''):
        dblist = self.connection.list_database_names()
        if DB_NAME not in dblist:
            self.connection[DB_NAME]

    def create_col(self, COL_NAME=''):
        collist = self.db.list_collection_names()
        if COL_NAME not in collist:
            self.db[COL_NAME]

    def get(self, MONGO_COLLECTION='', WHERE=None, SELECT=None, SORT=[("$natural", 1)], SKIP=0, TAKE=0):
        collection = self.db[MONGO_COLLECTION]
        return collection.find(WHERE, SELECT).sort(SORT).skip(SKIP).limit(TAKE)

    def getOne(self, MONGO_COLLECTION='', WHERE=None, SELECT=None, SORT=[("$natural", 1)], SKIP=0, TAKE=0):
        collection = self.db[MONGO_COLLECTION]
        return collection.find_one(WHERE, SELECT)

    def insert(self, MONGO_COLLECTION, insert_data):
        collection = self.db[MONGO_COLLECTION]
        customer_id = collection.insert_one(insert_data).inserted_id
        return customer_id

    def batch_insert(self, MONGO_COLLECTION, insert_data):
        data = self.db[MONGO_COLLECTION]
        customer_id = data.insert_many(insert_data)
        return customer_id

    def update(self, MONGO_COLLECTION='', WHERE=None, VALUE=None):
        SET = {'$set': VALUE}
        collection = self.db[MONGO_COLLECTION]
        return collection.update_one(WHERE, SET,upsert=True)

    def batch_update(self, MONGO_COLLECTION='', WHERE=None, VALUE=None):
        SET = {'$set': VALUE}
        collection = self.db[MONGO_COLLECTION]
        return collection.update_many(WHERE, SET)

    def update_push(self, MONGO_COLLECTION='', WHERE=None, VALUE=None):
        PUSH = {'$push': VALUE}
        collection = self.db[MONGO_COLLECTION]
        return collection.update_one(WHERE, PUSH)

    def delete(self, MONGO_COLLECTION='', WHERE=None):
        collection = self.db[MONGO_COLLECTION]
        return collection.delete_one(WHERE)

    def batch_delete(self, MONGO_COLLECTION='', WHERE={}):
        collection = self.db[MONGO_COLLECTION]
        return collection.delete_many(WHERE)

    def dropCollection(self, MONGO_COLLECTION=''):
        collection = self.db[MONGO_COLLECTION]
        collection.drop()

    def remove_document(self, MONGO_COLLECTION='', WHERE={}):
        collection = self.db[MONGO_COLLECTION]
        collection.remove(WHERE)

    def count(self, MONGO_COLLECTION='', WHERE=None):
        collection = self.db[MONGO_COLLECTION]
        return collection.find(WHERE).count()

    def aggregate_pipeline(self, MONGO_COLLECTION='', aggregate_pipeline=[]):
        collection = self.db[MONGO_COLLECTION]
        return collection.aggregate(aggregate_pipeline)

class Mongo_common:
    def __init__(self):
        import re
        import time
        import datetime
        import dateutil.parser
        from bson import ObjectId
        self.re = re
        self.time = time
        self.datetime = datetime
        self.parser = dateutil.parser
        self.ObjectId = ObjectId

        self.model = {}
        self.matching = {}
        self.OPERATORS = {
            'eq'    : '$eq',
            'gt'    : '$gt',
            'gte'   : '$gte',
            'lt'    : '$lt',
            'neq'   : '$neq',
            'in'    : '$in',
            'nin'   : '$nin',
        }
        self.SPECIAL = {
            'isnull'    : {'$eq'    : None},
            'isnotnull' : {'$ne'    : None},
        }

    def filter_convert(self, filter_value={}, model={}):
        if filter_value['filters'] != None and filter_value['filters'] != {}:
            logic = filter_value['logic'] if filter_value['logic'] != None else 'and'
            wheres = []
            aggMatches = {}
            for key, value in filter_value['filters']:
                subfilter = value
                subWhere = self.filter_convert(subfilter_value, model)
                if subWhere:
                    wheres.append(subWhere)

            if wheres:
                aggMatches['$' + logic] = wheres
            return wheres
        else:
            where = {}
            if filter_value['field'] != None and filter_value['operator'] != None:
                field = filter_value['field']
                if self.SPECIAL[filter_value['operator']]:
                    if filter_value['operator'] == 'isempty':
                        condition_1 = {}
                        condition_2 = {}
                        condition_1[field] = {'$exists': False}
                        condition_2[field] = ''
                        where['$or'] = [condition_1, condition_2]
                    else:
                        where[field] = self.SPECIAL[filter_value['operator']]
                else:
                    value = filter_value['value'] if filter_value['value'] != None else ''
                    filter_type = 'string'
                    if model[field] != None and model[field]['type'] != None:
                        filter_type = model[field]['type']
                    else:
                        if isinstance(value, str):
                            filter_type = 'string'
                        
                        if isinstance(value, int):
                            filter_type = 'int'
                        
                        if isinstance(value, float):
                            filter_type = 'double'

                        if isinstance(value, bool):
                            filter_type = 'boolean'

                        if isinstance(value, list):
                            filter_type = 'array'

                    type_switcher = {
                        'string'        : self.string_filter_value,
                        'boolean'       : self.boolean_filter_value,
                        'timestamp'     : self.timestamp_filter_value,
                        'datetime'      : self.datetime_filter_value,
                        'int'           : self.int_filter_value,
                        'double'        : self.double_filter_value,
                        'ObjectId'      : self.ObjectId_filter
                    }
                    where = type_switcher[filter_value['operator']](filter_value, where)
                    return where

    def string_filter(self, filter_value, where):
        mode = ''
        if filter_value['ignoreCase'] != None and filter_value['ignoreCase'] != '' and filter_value['ignoreCase'] != False:
            mode = 'i'
        operator_switcher = {
            'eq'                : self.eq_operator,
            'neq'               : self.neq_operator,
            'contains'          : self.contains_operator,
            'doesnotcontain'    : self.doesnotcontain_operator,
            'startswith'        : self.startswith_operator,
            'endswith'          : self.endswith_operator,
            'in'                : self.in_operator,
            'default'           : self.default_operator
        }
        return operator_switcher[filter_value['operator']](where, filter_value, mode)

    def boolean_filter(self, filter_value, where):
        mongoOperation = self.OPERATORS[filter_value['operator']]
        where[filter_value['field']][mongoOperation] = {mongoOperation: True if filter_value['value'] == 'true' else False}
        return where
    
    def timestamp_filter(self, filter_value, where):
        mongoOperation = self.OPERATORS[filter_value['operator']]
        timeString = self.re.sub('/\([^)]*\)/', '', filter_value['value'])
        if filter_value['value'].find('Z'):
            where[filter_value['field']][mongoOperation] = self.strtotime(timeString)
        else:
            where[filter_value['field']][mongoOperation] = self.strtotime(timeString) - self.time.timezone
        return where

    def datetime_filter(self, filter_value, where):
        mongoOperation = self.OPERATORS[filter_value['operator']]
        timeString = self.re.sub('/\([^)]*\)/', '', filter_value['value'])
        # d = self.parser.parse(timeString)
        # d = d.replace(tzinfo=utc) - d.utcoffset()
        # where[filter_value['field']][mongoOperation] = d
        return where
    
    def int_filter(self, filter_value, where):
        mongoOperation = self.OPERATORS[filter_value['operator']]
        where[filter_value['field']][mongoOperation] = int(filter_value['value'])
        return where

    def double_filter(self, filter_value, where):
        mongoOperation = self.OPERATORS[filter_value['operator']]
        where[filter_value['field']][mongoOperation] = float(filter_value['value'])
        return where

    def ObjectId_filter(self, filter_value, where):
        mongoOperation = self.OPERATORS[filter_value['operator']]
        where[filter_value['field']][mongoOperation] = self.ObjectId(filter_value['value'])
        return where

    def eq_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$eq': filter_value['value']}
        return where

    def neq_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$ne': filter_value['value']}
        return where
    
    def contains_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$regex': filter_value['value'], '$option': mode}
        return where

    def doesnotcontain_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$regex': '^((?!' + filter_value['value'] + ').)*$', '$option': mode}
        return where

    def startswith_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$regex': '^' + filter_value['value'], '$option': mode}
        return where

    def endswith_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$regex': filter_value['value'] + '$', '$option': mode}
        return where

    def in_operator(self, where, filter_value, mode):
        where[filter_value['field']] = {'$in': filter_value['value']}
        return where

    def default_operator(self, where, filter_value, mode):
        mongoOperation = '$' + filter_value['operator']
        where[filter_value['field']] = {mongoOperation: mode}
        return where

    def strtotime(self, string_time=''):
        date_from_string = self.parser.parse(string_time, dayfirst=True)
        return self.time.mktime(date_from_string.timetuple())




