# importing the requests library 
import requests 
from enum import IntEnum

class Method(IntEnum):
    GET = 1
    POST = 2

class ResponseType(IntEnum):
    Text = 1
    JSON = 2

# class Synchro(IntEnum):
#     Synchronous = 1
#     Asynchronous = 2

class HTTPRequest():

    def __init__(self, url=None, params={}, method=Method.GET, apikey=None, respType=ResponseType.Text):
        #self.setup(url,params,method,apikey)
        self.url = url
        self.params = params
        self.method = method
        self.apikey = apikey
        self.respType = respType

    def setup(self, url=None, params={}, method=None, apikey=None, respType=None):
        if not url is None:
            self.url = url
        # definición de parametros de API: dict for the parameters to be sent to the API: params = {'address':location} 
        if not params == {}:
            self.params = params
        if not method is None:
            self.method = method
        if not apikey is None:
            self.apikey = apikey
        if not respType is None:
            self.respType = respType

    def get(self, url=None, dataDict=None, respType=None):
        url = url if not url is None and url != "" else self.url
        dataDict = dataDict if not dataDict is None and dataDict != {} else self.params
        apikey = None if self.apikey == "" else self.apikey
        respType = respType if not respType is None and respType != "" else self.respType
        return HTTPRequest.sendRequest(url= url, dataDict= dataDict, method= Method.GET, apikey= apikey, respType= respType)

    def post(self, url=None, dataDict=None, apikey=None, respType=None):
        url = url if not url is None and url != "" else self.url
        dataDict = dataDict if not dataDict is None and dataDict != {} else self.params
        apikey = apikey if not apikey is None and apikey != "" else self.apikey
        respType = respType if not respType is None and respType != "" else self.respType
        return HTTPRequest.sendRequest(url= url, dataDict= dataDict, method= Method.POST, apikey= apikey, respType= respType)

    def request(self):
        if self.method == Method.GET:
            return self.get()
        if self.method == Method.POST:
            return self.post()

    @staticmethod
    def sendRequest(url, apikey=None, dataDict={}, method=Method.GET, respType=ResponseType.Text):
        
        # define dato token para api key
        if not apikey is None:
            dataDict['token'] = apikey

        r = None
        if method == Method.GET:
            # sending get request and saving the response as response object 
            r = requests.get(url=url, 
                            params=dataDict)
        elif method == Method.POST:
            # sending post request and saving response as response object 
            r = requests.post(url=url, 
                            data=dataDict)
        else:
            r = None #TODO: excepcion "Metodo no soportado"

        # respuesta de API
        if r is None:
            data = None
        else:
            if respType == ResponseType.Text:
                data = r.text # extracting data as text 
            elif respType == ResponseType.JSON:
                # si el formato no es compatible con JSON, devuelve el Texto
                try:
                    data = r.json() # extracting data in json format 
                except BaseException as wrongFormat:
                    data = r.text # extracting data as text 
            else:
                data = None #TODO: excepcion "tipo de respuesta no soportada"
            # extracting val1 
            #val1 = data['results'][0]['val1'] 
  
            # printing the output 
            print("Data:",data) 

        return data