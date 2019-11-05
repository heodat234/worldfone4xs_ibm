#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

class Config:
    def __init__(self):
        import os
        import sys
        import ftplib
        # import Jaccs as Jaccsconfig
        self.sys = sys
        self.ftplib = ftplib
        # self.Jaccsconfig = Jaccsconfig
        self.ftp = ftplib.FTP()
        self.os = os

    # Thong tin config ftp
    def ftp_config(self):
        return {'host': '192.168.16.130', 'username': 'ftp01', 'password': 'Stel7779'}

    # Base url
    def base_url(self):
        return '/var/www/html/worldfone4xs_ibm/'