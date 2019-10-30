#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Ftp:
    def __init__(self):
        import os
        import sys
        import ftplib
        sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python/config/')
        import Jaccs as Jaccsconfig
        self.sys = sys
        self.ftplib = ftplib
        self.Jaccsconfig = Jaccsconfig
        self.ftp = ftplib.FTP()
        self.os = os

    def connect(self):
        self.ftp.connect(self.Jaccsconfig.ftp_config['host'])
        self.ftp.login(self.Jaccsconfig.ftp_config['username'], self.Jaccsconfig.ftp_config['password'])

    def getAllFileFromDirectory(self, directory = ''):
        files = self.ftp.nlst()
        return files

    def grabFile(self, filename=''):
        localfile = open(filename, 'wb')
        self.ftp.retrbinary('RETR ' + filename, localfile.write, 1024)
        localfile.close()

    def placeFile(self, filename):
        self.ftp.storbinary('STOR '+filename, open(filename, 'w'))

    def downLoadFile(self, local_path='', filename=''):
        if not self.os.path.exists(self.os.path.dirname(local_path)):
            self.os.makedirs(self.os.path.dirname(local_path), 4777)
        lf = open(local_path, "wb")
        self.ftp.retrbinary("RETR " + filename, lf.write, 8*1024)

    def close(self):
        self.ftp.close()