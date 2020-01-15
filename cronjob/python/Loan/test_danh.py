
from pprint import pprint
import time
import resource
from helper.common import Common

from helper.mongod import Mongodb
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)

print('t1: ' , time.time() , 'mm: ' , resource.getrusage(resource.RUSAGE_SELF).ru_maxrss)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
tests = mongodb.get(MONGO_COLLECTION= 'LO_LNJC05', WHERE={}, SELECT=None, SORT=[("$natural", 1)], SKIP=0, TAKE=0)

zaccf = []
for test in tests:
	account_number = test["account_number"];
	data = mongodb.get('LO_ZACCF', {'account_number' : account_number})
	for i_data in data:
		zaccf.append(i_data)

for i in zaccf:
	pprint(i)
	break

print('t2: ' , time.time() , 'mm: ' , resource.getrusage(resource.RUSAGE_SELF).ru_maxrss)