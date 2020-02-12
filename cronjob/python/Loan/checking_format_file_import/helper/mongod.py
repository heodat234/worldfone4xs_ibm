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

    def getDistinct(self, MONGO_COLLECTION='' , SELECT=None, WHERE=None):
        collection = self.db[MONGO_COLLECTION]
        return collection.distinct(SELECT, WHERE)