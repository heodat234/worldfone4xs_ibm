#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Mongodb:
    def __init__(self, MONGODB):
        import pymongo
        import bson
        self.bson = bson
        self.pymongo = pymongo
        connection = self.pymongo.MongoClient('127.0.0.1', 27017)
        self.MONGODB = MONGODB
        self.db = connection[self.MONGODB]

    def get(self, MONGO_COLLECTION='', WHERE=None, SELECT=None, SORT=[("$natural", 1)], SKIP=0, TAKE=30):
        collection = self.db[MONGO_COLLECTION]
        return collection.find(WHERE, SELECT).sort(SORT).skip(SKIP).limit(TAKE)

    def getOne(self, MONGO_COLLECTION='', WHERE=None, SELECT=None, SORT=[("$natural", 1)], SKIP=0, TAKE=30):
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
