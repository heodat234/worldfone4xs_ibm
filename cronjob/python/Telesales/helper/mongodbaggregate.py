#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Mongodbaggregate:
    def __init__(self, MONGODB, WFF_ENV=''):
        import pymongo
        import bson
        from pprint import pprint
        self.bson = bson
        self.pymongo = pymongo
        self.pprint = pprint
        if WFF_ENV in ['UAT', 'DEV']:
            connection = self.pymongo.MongoClient('127.0.0.1', 27017)
        else:
            connection = self.pymongo.MongoClient('127.0.0.1', 27017, username='worldfone4x', password='St3l37779db')
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

    