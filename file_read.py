def f_read(n):
  try:
    f=open(n)
    fd1=f.readline()
    fd2=f.readline()
    f.close()
    if(fd2=='1\n'):
      f=open(n,'w')
      f.write(fd1[0:1]+'\n')
      f.write('0\n')
      f.close()
      return True
    else:
      return False
  except IOError:
    print("file open error : "+n)
