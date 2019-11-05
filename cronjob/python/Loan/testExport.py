import sys
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
import pandas as pd
from mongod import Mongodb
from datetime import datetime
from datetime import date


mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")

now         = datetime.now()
data = []
try:
	result = mongodb.get(MONGO_COLLECTION='LO_LNJC05', SELECT=['overdue_amount_this_month','advance_balance','installment_type','group_id','account_number','mobile_num','cus_name'], SORT=([('_id', 1)]),TAKE=50)
	for row in result:
		if int(row['overdue_amount_this_month']) - int(row['advance_balance']) > 40000 or (int(row['overdue_amount_this_month']) - int(row['advance_balance']) < 40000 and row['installment_type'] == 'n'):
			data.append(row)

	df = pd.DataFrame(data) 
	# df.to_excel()
except Exception as e:
	raise e