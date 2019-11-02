#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
import os

import conector as cn

import time as t
from datetime import datetime as time, timedelta as delta

from textwrap import wrap

class CronServer():
    def __init__(self, nombre="", dbConfig={}):
        self.DEF_IGNORE_CHAR = '_'
        self.DEF_INIT_STATUS = cn.Status.STARTING
        self.conf = { "CRON_TIMEJUMP_SEC": 0.1,
                    "sync_frec": 300, "API_validation": "48370255gBrgdlpl050588",
                    "CONN_TIMEOUT": 0.6, "CONN_CHECK_TIMEOUT": 5 , 
                    "DB_TIMEOUT" : { "CONNECT": 3, "STATUS_READ": 1000, "STATUS_WRITE": 2000 }
                    } 
        self.nombre = nombre
        self.status = cn.Status.OFF
        print("Iniciando servidor",self.nombre)
        
        # Establecer conexion con BBDD
        self.source = cn.DBSource(dbConfig,self.conf["DB_TIMEOUT"],self)
        # Comprobar conexión con BBDD
        if not self.source.connect():
            print("Error de conexion a BBDD. Compruebe los datos de conexion.")
            ##exit(1) sync: NO ABORTA SI FALLA CONEXION CON BBDD
        ##else:
        
        # Procesa setup obteniendo ultimo estado (recuperación post falla)
        currStatus = self.setup()
        # Define estado a procesar segun estado previo (Status.STARTING por defecto) 
        newStatus = self.DEF_INIT_STATUS if currStatus in [cn.Status.OFF,cn.Status.RESTARTING] else cn.Status(2 * (int(currStatus) // 2))
        print("BD", currStatus, "=> procesar", newStatus)
        # Definir nuevo estado y guardar en BBDD
        self.processNewState(newStatus) 

    def getStatus(self):
        stat = self.status
        now = time.now()
        stat = { "name": self.nombre, 
                "update": now.strftime("%Y-%m-%d %H:%M:%S"),
                "status": int(stat),
                "statdesc": stat.name }
        return stat
        
    def setup(self):
        print("Configurando servidor",self.nombre)
        # Obtiene configuración de servidor, salvo que no exista y toma la BASE
        newStatus, newConf = self.source.readSvrInfo()
        if newConf == {}:
            # Falló recuperando información de servidor
            print("> se mantiene misma configuracion")
            newStatus = self.status
            newConf = self.conf
        
        # Actualizar diccionario de configuracion (se reemplazan valores coincidentes)
        self.conf.update(newConf)
        # Actualiza configuracion BBDD
        self.source.setup(self.conf["DB_TIMEOUT"])
    ##TODO: chequear newStatus no es asignado
    ##TODO: chequear configuración cargada correctamente
        return newStatus
        
    def start(self):
        self.status = cn.Status.WORKING
    
    def suspend(self):
        self.status = cn.Status.SUSPENDED
    
    ''' Procesar nuevo estado de servidor (control externo)
    - Se recibe status=[STARTING,RESTARTING,SUSPENDING]
    - Se procesan funciones: setup, start, suspend, ...
    - Solo procesa cuando el newStatus difiere del actual
    - Actualiza estado en BBDD '''
    def processNewState(self, newStatus=cn.Status.OFF):
        forceWrite = False
        if (self.status != newStatus):
            forceWrite = True
            print("Nuevo Estado: actual (",int(self.status),",",str(self.status),") >> nuevo (",int(newStatus),",",str(newStatus),")")
            if (self.status != cn.Status.OFF 
                and newStatus == cn.Status.RESTARTING):
                #Recargar configuracion servidor (si es OFF, setup se omite)
                self.setup() 
            if (self.status in [cn.Status.OFF,cn.Status.SUSPENDED]
                and newStatus in [cn.Status.STARTING]): #,cn.Status.RESTARTING
                #iniciar servidor / comenzar reconocimiento
                self.start()
            if (self.status in [cn.Status.OFF,cn.Status.WORKING]
                and newStatus == cn.Status.SUSPENDING):
                #suspender servidor / detener reconocimiento
                self.suspend()
        self.source.writeSvrStatus(self.nombre, self.status, self.conf, forceWrite)

    def keyStop(self):
        #uso variable "estática" para nuevos llamados a la función
        if not hasattr(CronServer.keyStop,"exit"):
            setattr(CronServer.keyStop,'exit', False)
        # if not getattr(CronServer.keyStop,'exit'):
        #     if cv2.waitKey(25) & 0xFF == ord('q'):
        #         self.processNewState(cn.Status.SUSPENDING)
        #         setattr(CronServer.keyStop,'exit', True)
        return getattr(CronServer.keyStop,'exit')

    ''' Proceso background de servidor '''
    def runService(self):
        try:
            now = time.now()
            # inicializa última sincronizacion
            lastsync = now 
            # Bucle infinito (funciona en background como servicio)
            while not self.keyStop():
                now = time.now()
                #----------------------------- BBDD STATUS_READ start -------------------------
                # Obtener, procesar y actualizar estado en BBDD (la funcion tiene un 
                # timeout interno definido en STATUS_READ para reducir la cantidad de consultas)
                res, newStatus = self.source.readSvrStatus(self.status)
                if res:
                    self.processNewState(newStatus)
                
                #----------------------------- BBDD STATUS_READ end -------------------------
                if self.status is cn.Status.WORKING:
                    #----------------------------- SYNC start -------------------------
                    # Evalúa si corresponde ejecutar Sync
                    tout = now - lastsync
                    #print(tout,time.now(),toutDiff)
                    if tout.total_seconds() > self.conf["sync_frec"]:
                        # Actualizar lastsync
                        lastsync = now
                        #TODO: Ejecutar Sync
                        pass #llamado a API con self.conf["API_validation"]
                    #----------------------------- SYNC end -------------------------
                    
                    # permitir a otro thread trabajar
                    t.sleep(self.conf["CRON_TIMEJUMP_SEC"])
                    
                
        except IOError as e:
            print("Error IOError no capturado correctamente.")
            #print(time.now(), "Error abriendo socket: ", ipcamUrl)
        ##except cv2.error as e:
        ##    print(time.now(), "Error CV2: ", e)
        #    if e.number == -138:
        #        print("Compruebe la conexión con '" + ipcamUrl + "'")
        #    else:
        #        print("Error: " + e.message)
        except KeyboardInterrupt as e:
            print(time.now(), "Detenido por teclado.")
            
    #    except BaseException as e:
    #        print(time.now(), "Error desconocido: ", e)

if __name__ == "__main__":
    ##TODO: recibir lo siguiente como parámetros de entrada
    serverName = "SYNC1"
    dbConfig = {'user':"usher",
                'passwd':"usher101",
                'svr': "usher.sytes.net",
                'db':"usher_web"}

    svr = CronServer(serverName, dbConfig) #(sourceDB|sourceFile)
    svr.runService()
else:
    print("Ejecutando desde ", __name__)
