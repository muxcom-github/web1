import serial
import time
import file_read
import RPi.GPIO as GPIO

time.sleep(30)
ser=serial.Serial('/dev/ttyAMA0',9600, timeout=1)
GPIO.setmode(GPIO.BCM)
mpw_pin = 6
GPIO.setup(mpw_pin, GPIO.OUT)
p25=25
GPIO.setup(p25, GPIO.OUT)
p08=8
GPIO.setup(p08, GPIO.OUT)
GPIO.output(p08, False)
loop=1
while(loop):
  for i in range(0,5):
    GPIO.output(p25, True)
    time.sleep(0.1)
    GPIO.output(p25, False)
    time.sleep(0.1)

  x=ser.readline()
  if(str(x)=='987'):
    GPIO.output(p08, True)
    time.sleep(0.1)
    ser.write('D987C')
    time.sleep(0.05)
    GPIO.output(p08, False)
    print(x)
    loop=0

file='/home/pi/result.txt'
f_sen='/home/pi/cmd_sen_req.txt'
f_valm='/home/pi/cmd_valm_req.txt'
f_light='/home/pi/cmd_light_req.txt'
f_misc1='/home/pi/cmd_misc1_req.txt'
f_misc2='/home/pi/cmd_misc2_req.txt'
cnt=1
while cnt:
  data=[]
  if(file_read.f_read(f_sen)):
      GPIO.output(p08, True)
      time.sleep(0.1)
      ser.write('D1C')
      time.sleep(0.05)
      GPIO.output(p08, False)
  if(file_read.f_read(f_valm)):
      GPIO.output(p08, True)
      time.sleep(0.1)
      ser.write('D2C')
      time.sleep(0.05)
      GPIO.output(p08, False)
  if(file_read.f_read(f_light)):
      GPIO.output(p08, True)
      time.sleep(0.1)
      ser.write('D3C')
      time.sleep(0.05)
      GPIO.output(p08, False)
  if(file_read.f_read(f_misc1)):
      GPIO.output(p08, True)
      time.sleep(0.1)
      ser.write('D4C')
      time.sleep(0.05)
      GPIO.output(p08, False)
  if(file_read.f_read(f_misc2)):
      GPIO.output(p08, True)
      time.sleep(0.1)
      ser.write('D5C')
      time.sleep(0.05)
      GPIO.output(p08, False)
  x=ser.readline()
#  ser.write(x)
  if(x[0:2]=='AB'):
    print x
    sink_status=str(x[2:3])
    data.append(sink_status+'\n')
    mpw_status=str(x[3:4])
    data.append(mpw_status+'\n')
    valm_status=str(x[4:5])
    data.append(valm_status+'\n')
#    valm_chapter_status=x[5:6]
#    data.append(valm_chapter_status+'\n')
    relay_status=str(x[5:6])
    data.append(relay_status+'\n')
#    misc_status=x[7:8]
#    data.append(misc_status+'\n')
    motion_status=str(x[6:7])
    data.append(motion_status+'\n')
#    ad1_val=str(x[7:11])
    ad1_val=str(x[9:11]+x[7:9])
    ad1_i=int(ad1_val,base=16)
    ad1_f=float(ad1_i)
    ad1_f=ad1_f*0.018-0.158
#    if (ad1_f<0 or ad1_f>15) ad1_f=7.8
    ad1="%0.1f" % float(ad1_f)
    ad1_value=str(ad1)
#    data.append(ad1_value+'\n')
#    ad2_val=str(x[11:15])
    ad2_val=str(x[13:15]+x[11:13])
    ad2_i=int(ad2_val,base=16)
    ad2_f=float(ad2_i)
    ad2="%0.1f" % float(ad2_f)
    ad2_value=str(ad2)
    temp_val = str(x[17:21])
    temp_i = int(temp_val, base=16)
    temp_f = float(temp_i)+0.4
    temp = "%0.1f" % float(temp_f/16)
    temp_value = str(temp)
    data.append(temp_value+'\n')
#    data.append('999\n')
#    data.append('999\n')
    data.append(ad1_value+'\n')
#    data.append(ad2_value+'\n')
    data.append('999\n')
    uc_kind=x[15:16]
#    if(uc_kind=='1'):
#      uc_length=str(x[16:17])
#      len=int(uc_length)
#      uart_dat=str(x[21:18+len])
#      uart_i=int(uart_dat)
#      uart_f=float(uart_i)
#      uart_d="%0.1f" % float(uart_f/10)
#      uart_data=str(uart_d)
#      data.append(uart_data+'\n')
#      etx=x[18+len:19+len]
    data.append('1')
    uc_length = str(x[16:17])
    len = int(uc_length)
    try:
#      if(uc_kind=='0'):
#        j=9
#      else:
#        j=9+len
      f=open(file,'w')
      i=0
      for i in range(0,9):
        f.write(data[i])
        print data[i]
      f.close()
    except IOError:
      print("Cannot open file : "+file)
      exit()
