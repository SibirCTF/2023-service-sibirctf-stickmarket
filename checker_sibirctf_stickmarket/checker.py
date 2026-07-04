#!/usr/bin/python3
import requests
import re
import os
from random import randint, choice
from dataclasses import dataclass
from typing import Union
from argparse import ArgumentParser
import io
import zipfile
import mimesis


def log(error_code, status):
    print("%s - %s" % (error_code, status))


# put-get flag to service success
def service_up():
    print("[service is worked] - 101")
    exit(101)


# service is available (available tcp connect) but protocol wrong could not put/get flag
def service_corrupt():
    print("[service is corrupt] - 102")
    exit(102)


# waited time (for example: 5 sec) but service did not have time to reply
def service_mumble():
    print("[service is mumble] - 103")
    exit(103)


# service is not available (maybe blocked port or service is down)
def service_down():
    print("[service is down] - 104")
    exit(104)



@dataclass
class Stick:
    price : int
    name : str                     = ''
    description : str              = ''
    phraseOfTruth : str            = ''
    author : str                   = ''
    image : Union[bytearray, None] = None
    __names = ["Eric Cartman",
                "Kenny McCormick",
                "Kyle Broflovski",
                "Stan Marsh",
                "Tweek Tweak",
                "Wendy Testaburger",
                "Kathleen Kennedy",
                "Tolkien Black"]
    
    
    def to_yaml(self):
        ret = f"stick:\n"
        ret += "  nameOfStick: "
        if self.name: 
            ret += f"'{self.name}'"
        ret += '\n'
        ret += f"  price: {self.price}\n"
        ret += "  description: "
        if self.description:
            ret += f"'{self.description}'"
        ret += '\n'
        ret += "  phraseOfTruth: "
        if self.phraseOfTruth:
            ret += f"'{self.phraseOfTruth}'"
        ret += '\n'
        ret += "  author: "
        if self.author:
            ret += f"'{self.author}'"
        ret += '\n'
        ret += "  image: "
        if self.image:
            ret += f"'{self.image}'"
        return ret
    
    
    @staticmethod
    def create_without_flag():
        return Stick(randint(1, 100),
                     author=choice(Stick.__names))

    
    @staticmethod 
    def create_with_flag(flag):
        return Stick(randint(101,1000), 
                     phraseOfTruth=flag,
                     author=choice(Stick.__names))


def create_zip(file : str):
    zip_buffer = io.BytesIO()
    zip_buffer.name = "test.zip"
    with zipfile.ZipFile(zip_buffer, "w",
                         zipfile.ZIP_DEFLATED, False) as zip_file:
        zip_file.writestr('stick.yml', io.BytesIO(file.encode()).getvalue())
    with open('test.zip', 'wb') as f:
        f.write(zip_buffer.getvalue())
    path = os.getcwd() + '/test.zip'
    return path
    

def put_flag(f_id, flag, url):
    login = mimesis.Person().name() + mimesis.Person().last_name()
    password = mimesis.Person().password()
    stk = Stick.create_with_flag(flag)
    without = Stick.create_without_flag()
    try:
        with requests.Session() as s:
            r = s.post(f'{url}/register.php', data = {"username" : login, "password" : password}, timeout=2)
            r = s.post(f'{url}/login.php', data = {"username" : login, "password" : password}, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.get(f'{url}/index.php', timeout=2)
            if r.status_code != 200:
                service_corrupt()
            try:
                pagination = re.findall(r'<div class="pagination">([\s\S]*?)</div>', r.text)[0]
                pages = int(re.findall(r"<a href='\?page=(\d+)'", pagination)[-1])
            except:
                pages = 1
            data = {'file': ('file.zip', open(create_zip(stk.to_yaml()), 'rb'))}
            r = s.get(f'{url}/index.php?page={pages}', timeout=2)
            count_start = len(re.findall(r'src=images/stick.jpg', r.text))
            r = s.post(f'{url}/addstick.php', files = data, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            data = {'file': ('file.zip', open(create_zip(without.to_yaml()), 'rb'))}
            r = s.post(f'{url}/addstick.php', files = data, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.get(f'{url}/index.php?page={pages}', timeout=2)
            count_finish = len(re.findall(r'src=images/stick.jpg', r.text))
            if count_finish <= count_start and count_finish != 5:
                service_corrupt()
            service_up()
    except requests.exceptions.ConnectionError:
        service_down()
    except requests.exceptions.RequestException as e:
        service_mumble()
    except Exception as e:
        service_corrupt()

def check(f_id, url):
    login = mimesis.Person().name() + mimesis.Person().last_name()
    password = mimesis.Person().password()
    without = Stick.create_without_flag()
    try:
        with requests.Session() as s:
            r = s.post(f'{url}/register.php', data = {"username" : login, "password" : password}, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.post(f'{url}/login.php', data = {"username" : login, "password" : password}, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.get(f'{url}/index.php', timeout=2)
            if r.status_code != 200:
                service_corrupt()
            try:
                pagination = re.findall(r'<div class="pagination">([\s\S]*?)</div>', r.text)[0]
                pages = int(re.findall(r"<a href='\?page=(\d+)'", pagination)[-1])
            except:
                pages = 1
            r = s.get(f'{url}/index.php?page={pages}', timeout=2)
            count_start = len(re.findall(r'src=images/stick.jpg', r.text))
            data = {'file': ('file.zip', open(create_zip(without.to_yaml()), 'rb'))}
            r = s.post(f'{url}/addstick.php', files = data, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.get(f'{url}/index.php?page={pages}', timeout=2)
            count_finish = len(re.findall(r'src=images/stick.jpg', r.text))
            if count_finish <= count_start and count_finish != 5:
                service_corrupt()
            r = s.post(f'{url}/bonus.php', timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.post(f'{url}/buy.php/buy.php?id=', data = {"id" : 1}, timeout=2)
            if r.status_code != 200:
                service_corrupt()
            r = s.get(f'{url}/index.php', timeout=2)
            if r.status_code != 200:
                service_corrupt()
            service_up()
    except requests.exceptions.ConnectionError:
        service_down()
    except requests.exceptions.RequestException as e:
        service_mumble()
    except Exception as e:
        service_corrupt()
    
def main():
    pargs = ArgumentParser()
    pargs.add_argument("host")
    pargs.add_argument("command", type=str)
    pargs.add_argument("f_id", nargs='?')
    pargs.add_argument("flag", nargs='?')
    args = pargs.parse_args()
    port = 80
    
    url = f"http://{args.host}:{port}"
    
    
    if args.command == "put":
        if not args.flag:
            pargs.error("You need to specify flag with PUT method")
        put_flag(args.f_id, args.flag, url)
        service_up()
    elif args.command == "check":
        check(args.f_id, url)
        service_up()
    else:
        pargs.error("Wrong command")


if __name__ == "__main__":
    main()
