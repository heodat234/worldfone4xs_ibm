#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Mongodbaggregate:
    def __init__(self, MONGODB):
        import pymongo
        import bson
        from pprint import pprint
        self.bson = bson
        self.pymongo = pymongo
        self.pprint = pprint
        connection = self.pymongo.MongoClient('127.0.0.1', 27017)
        self.MONGODB = MONGODB
        self.db = connection[self.MONGODB]
        self.collection = ''
        self.aggregate_pipeline = []

    def set_collection(self, collection=''):
        self.collection = collection
    
    def add_aggregate(self, aggregate_element):
        self.aggregate_pipeline = self.aggregate_pipeline + aggregate_element

    def aggregate(self):
        try:
            collection = self.db[self.collection]
            return collection.aggregate(pipeline=self.aggregate_pipeline)
        except Exception as e:
            self.pprint(str(e))

    