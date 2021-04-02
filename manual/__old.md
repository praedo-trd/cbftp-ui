## Experimental server (Ignore)

Install Swoole:

- `sudo apt-get install php-dev`
- `pecl install swoole`
- Answer as such:
  - enable sockets supports? yes
  - enable openssl support? yes
  - enable http2 support? no
  - enable mysqlnd support? no
  - enable postgresql coroutine client support? no
- Confirm it's installed: `php --ri swoole`
- Run `php server2.php` instead of the original server.
