import time


def Fibonacci(n):
    if n == 0:
        return 0
    elif n == 1:
        return 1
    elif n == 2:
        return 1
    else:
        return Fibonacci(n-1)+Fibonacci(n-2)


try:
    ts = time.time()
    print(Fibonacci(100))
    ts2 = time.time()
    print(ts2-ts)

except Exception as e:
    print(e)
