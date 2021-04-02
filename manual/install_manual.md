# Standard installation

**1. PHP setup**
- `sudo apt-get install php php-intl php-curl php-json php-mysql php-xml mysql-server php-mbstring`
- Make sure your PHP environment is set up correctly and you can use PHP on the command line and also Composer. Install composer: https://getcomposer.org
- Install the project dependencies using composer by typing `composer install` in the project root.

**2. Database setup**
- Create a MySQL database and create the DB structure from `misc/db.sql`
- Set up MySQL **non-root** user and [grant it privileges to the db]( https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql)
- Import MySQL timezone data: `mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -uroot -p mysql`

**3. TRD config setup**
- Run the following commands (in order):
```
mkdir ~/.trd
cp -R /path/to/trd/.trd.example/* ~/.trd
```
- Check that the config files copied correctly: `ls ~/.trd` (You should see a few JSON files)
- Copy `.env.example` to `.env` and configure the settings (or find a way to set the environmental variables on your system/container yourself)

**4. Set up scheduled tasks**

Set up cron to perform nightly maintenance tasks:
```
00 00 * * * php /path/to/trd/app/console.php trd:cleanup
00 01 * * * php /path/to/trd/app/console.php trd:refresh_cache
```

**5. Learn tmux**

You will need to run the TRD server and web gui process in the background. We strongly recommend doing this using tmux or screen. So go learn how to use these.

**5. Start TRD**

Type `php server.php` in your TRD directory and preferably run this in a tmux pane.

**6. Spin up the web GUI**

Run the following command in a tmux pane ().

`cd web; php -S 127.0.0.1:13131` (or any other high port)

**7. Visit the web GUI**

You are going to need to visit the web GUI. For security reasons we bound it to 127.0.0.1 so it's not acessible from outside. You will need to forward a port to visit this from whatever machine you are on. See [Security](security.md) for more information on this.
