from module.header import *
from module.header import mongodb
from module.header import _mongodb

log = open(base_url + "cronjob/python/Loan/checking_format_file_import/log/checking_format_file_LNJC05F.txt","a")
subUserType = 'LO'
fileName = "LNJC05F"
collection = common.getSubUser(subUserType, 'LNJC05')
sep = ';'
try:
    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'])
    var_dump(models)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str('Runned') + '\n')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))
