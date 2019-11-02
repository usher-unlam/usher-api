#!/usr/bin/env python
# -*- coding: utf-8 -*-
import abc
from abc import ABCMeta
import mysql.connector
import json
from textwrap import wrap
import socket
import os
from urllib.parse import urlparse
from datetime import datetime as time
from datetime import timedelta as delta

from enum import IntEnum
class Status(IntEnum):
    OFF = 1
    STARTING = 2
    WORKING = 3
    SUSPENDING = 4
    SUSPENDED = 5
    RESTARTING = 6
        
class CamStatus(IntEnum):
    OK = 0
    ERR_SOCKET = 1
    ERR_ADDRSS = 2
    ERR_CONNTN = 3
    ERR_CV2CAP = 4
    ERR_NOFILE = 10
    ERR_NOACCS = 11

    
class DataSource():
    __metaclass__ = ABCMeta
    def __init__(self, cronserver):
        print("Inicia DataSource -",type(self).__name__)
        self.camsvr = cronserver
        
    @abc.abstractmethod
    def readSvrInfo(self):
        pass
    @abc.abstractmethod
    def keepAlive(self):
        pass
    @abc.abstractmethod
    def readCamInfo(self):
        pass
    @abc.abstractmethod
    def writeCamInfo(self):
        pass
    @abc.abstractmethod
    def readOcupyState(self):
        pass
    @abc.abstractmethod
    def writeOcupyState(self):
        pass
    @abc.abstractmethod
    def close(self):
        pass
        
class FileSource(DataSource):
    def __init__(self, cronserver):
        DataSource.__init__(self, cronserver)
        #print("inicia FileSource")
        pass

class DBSource(DataSource):
    #{user="root",passwd="12345678",svr="localhost",db="usher_rec"}
    def __init__(self, connData, timeouts, cronserver):
        DataSource.__init__(self, cronserver)
        #print("inicia DBSource")
        self.tout = timeouts
        self.connData = connData
        self.conn = None
        self.cursor = None
        ## Realizar primer conexion a BBDD
        #self.connect()

    def connect(self):
        if self.conn is None or not self.conn.is_connected():
            self.cursor = None
            try:
                
                self.conn = mysql.connector.connect(user=self.connData['user'], 
                                                    password=self.connData['passwd'],
                                                    host=self.connData['svr'],
                                                    database=self.connData['db'],
                                                    connection_timeout= self.tout['CONNECT'])
               
        ##TODO: capturar errores SQL
                if self.conn and self.conn.is_connected():
                    self.conn.config(connection_timeout=30)
                    self.cursor = self.conn.cursor()
                    if self.cursor:
                        return True
            except mysql.connector.Error as error:
                print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
            finally:
                pass
            return False
        else:
            return True
    
    def setup(self, timeouts):
        self.tout = timeouts

    def readSvrStatus(self, defStat=Status.OFF, forced=False):
        newVal = False
        status = defStat
        tlimit = time.now() - delta(milliseconds=self.tout['STATUS_READ'])
        if not hasattr(DBSource.readSvrStatus, 'update'):
            setattr(DBSource.readSvrStatus, 'update', tlimit)
        if (forced 
            or getattr(DBSource.readSvrStatus, 'update') < tlimit):
            if self.connect():
                try:
                    self.cursor.execute("""SELECT status+0 FROM cronserver 
                                        WHERE id = %s""", 
                                        (self.camsvr.nombre,))
                    reg = self.cursor.fetchone()
                    if not reg is None and reg[0] > 0:
                        newVal = True
                        status = Status(reg[0])
                    print("")    
                    print("Lee BBDD status",getattr(DBSource.readSvrStatus, 'update'),status)
                except mysql.connector.Error as error:
                    print("Lee BBDD status","Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
                finally:
                    setattr(DBSource.readSvrStatus, 'update', time.now())
                #self.close()
            else:
                print("Lee BBDD status","ERROR CONEXION A BBDD")
    ##TODO: capturar errores SQL
        return newVal, status
        
    # Actualizar estado y fecha de vivo (keep alive) del servidor
    def writeSvrStatus(self, svrNombre, svrStatus, svrConf, forced=False):
        out = False
        tlimit = time.now() - delta(milliseconds=self.tout['STATUS_WRITE'])
        if not hasattr(DBSource.writeSvrStatus, 'update'):
            setattr(DBSource.writeSvrStatus, 'update', tlimit)
        if (forced 
            or getattr(DBSource.writeSvrStatus, 'update') < tlimit):
            if self.connect():
                try:
                    #(select z.config from cronserver as z where z.id='BASE' LIMIT 1)
                    self.cursor.execute("""INSERT INTO cronserver (id,alive,status,config) 
                                        VALUES (%s,NULL,%s,%s) 
                                        ON DUPLICATE KEY UPDATE alive=null, status=if(%s,VALUES(status),status)""",
                                        (svrNombre, int(svrStatus), json.dumps(svrConf),forced))
                    out = self.conn.commit()
                    print("Graba BBDD status",getattr(DBSource.writeSvrStatus, 'update'),svrStatus)
                    out = True
                except mysql.connector.Error as error:
                    print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
                except mysql.connector.InterfaceError as error:
                    print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
                finally:
                    setattr(DBSource.writeSvrStatus, 'update', time.now())
                #self.close()
            else:
                print("Graba BBDD status","ERROR CONEXION A BBDD")
    ##TODO: capturar errores SQL
        return out

    def readSvrInfo(self):
        status = Status.OFF
        server = {}
        if self.connect():
            try:
                self.cursor.execute("""SELECT status+0 as status, config FROM cronserver 
                                WHERE id in (%s, 'BASE') 
                                ORDER BY alive DESC LIMIT 1""", 
                                (self.camsvr.nombre,))
                reg = self.cursor.fetchone()
                if not reg is None:
                    if reg[0] > 0:
                        status = Status(reg[0])
                    server = json.loads(reg[1])
            except mysql.connector.Error as error:
                print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
            finally:
                pass
            #self.close()
    ##TODO: capturar errores SQL
        return (status,server)

    '''Leer info de cámaras de BD
        Output: <class 'list'> [{
        'nombre': 'cam1', 'minUbicacion': [ANCHOpx, ALTOpx], 
        'ip': '192.168.0.10', 'desc': 'camara del techo', 
        'ubicaciones': [
          {'nro': 1, 'coord': [X1, Y1], 'size'}, 
          {'nro': 2, 'coord': [X2, Y2]}, 
        ]}] '''
    def readCamInfo(self, cameras):
        if self.connect():
            try:
                query = "SELECT config FROM camara WHERE activa = true"
                if len(cameras) > 0:
                    cameras_list = ','.join(['%s'] * len(cameras))
                    query += " AND nombre in (%s)" % cameras_list
                    self.cursor.execute(query, tuple(cameras))
                else:
                    self.cursor.execute(query)
                reg = self.cursor.fetchall()
                cams = list()
                if not reg is None:
                    for r in reg:
                        cams.append(json.loads(r[0]))
            except mysql.connector.Error as error:
                print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
            finally:
                pass
            #self.close()
    ##TODO: capturar errores SQL
        return cams

    def writeCamInfo(self,cams=[]):
        if self.connect():
            try:
                for cam in cams:
                    self.cursor.execute("""UPDATE camara SET 
                                        config = %s 
                                        WHERE nombre = %s and activa = true""",
                                        (json.dumps(cam),cam["nombre"],))
            ##TODO: capturar errores SQL
                self.conn.commit()
            except mysql.connector.Error as error:
                print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
            finally:
                pass 

    '''Leer info de ocupación de ubicaciones de BBDD
        Output: <class 'list'> ['0', '0', '0'] '''
    def readOcupyState(self):
        if self.connect():
            try:
                self.cursor.execute("""SELECT estadoUbicaciones FROM estado 
                                    WHERE cronserver = %s 
                                    ORDER BY tstamp DESC LIMIT 1""", 
                                    (self.camsvr.nombre,))
                reg = self.cursor.fetchone()
                estado = list()
                if not reg is None:
                    estado = wrap(reg[0],1)
            ##TODO: capturar errores SQL
            except mysql.connector.Error as error:
                print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
            finally:
                pass
        return estado
        
    def writeOcupyState(self,tnewState=None, newState=[]):
        newState = ''.join(newState)
        if newState != "":
            if self.connect():
                try:
                    self.cursor.execute("""INSERT INTO estado (tstamp, prioridad, estadoUbicaciones, cronserver) 
                                                VALUES (%s, 255, %s, %s)
                                                ON DUPLICATE KEY UPDATE tstamp=VALUES(tstamp), estadoUbicaciones=VALUES(estadoUbicaciones)""", 
                                                (tnewState, newState, self.camsvr.nombre))
                     
                    #print("ESTADO UBICACIONES: ",newState)
                    # script = self.cursor.execute("""UPDATE estado SET tstamp=%s, estadoUbicaciones=%s 
                    #                             WHERE cronserver = %s""", 
                    #                             (tnewState, newState, self.camsvr.nombre))
            ##TODO: capturar errores SQL
                    self.conn.commit()
                    print("DATOS GRABADOS: ",tnewState, '{:7.7}'.format(newState) )  
                except mysql.connector.Error as error:
                    print("Error de BBDD: {}".format(error), "(", self.connData['svr'], ")")
                except BaseException as e:
                    print(time.now(), "Error desconocido grabando estado: ", e)
                finally:
                    pass
  

       
    def close(self):
        if self.conn and self.conn.is_connected():
            self.cursor.close()
            self.conn.close()
    ##TODO: capturar errores SQL
    
#import datetime
#query = ("SELECT first_name, last_name, hire_date FROM employees "
#         "WHERE hire_date BETWEEN %s AND %s")
#
#hire_start = datetime.date(1999, 1, 1)
#hire_end = datetime.date(1999, 12, 31)
#
#cursor.execute(query, (hire_start, hire_end))
#
#for (first_name, last_name, hire_date) in cursor:
#  print("{}, {} was hired on {:%d %b %Y}".format(
#    last_name, first_name, hire_date))
