<h2><span style="color: #ff0000;"><strong>&nbsp;Opensource-casino-v10</strong></span></h2>
<div class="container">
<p><strong>Open source slots casino script (formerly Goldsvet) v10</strong></p>
<p><span style="color: #ff0000;"><strong>V10 november 2023 release adds Laravel 10 and PHP 8.2+ support.</strong></span></p>
<p><span style="color: #ff0000;"><strong>ALSO NOW SUPPORTS PLUGINS LIKE&nbsp;<br />SPORTS BETTING, CRYPTO COMPETITIONS AND STOCKS COMPETITIONS<br />PLUGINS AVAILABLE IN DISCORD DOWNLOADS TOO ONLY FOR SUBSCRIBERS&nbsp;</strong></span></p>
<p>This is a Laravel casino app. You need to download game packs for it.</p> 
  <p>Official Discord : <a href="https://discord.gg/3QpZNd89WZ"> https://discord.gg/3QpZNd89WZ</a></p>
<p>DEMO : <a href="HTTPS:///casino.377PLAY.COM">HTTPS://casino.377PLAY.COM</a></p>
<p><span style="color: #339966;">DEMO DISCLAIMER : demo always goes under updates, tests and db flushing, dont use it as a stable website.</span></p>
<p>Join our Discord for game downloads (compatible up to v10):</p><p>1100 games total as of december 2023 includes 100 prag pack</p>
<p><a href="https://discord.gg/HscTu67FSu">https://discord.gg/HscTu67FSu</a></p>
<h2>Partner Service</h2>
<p><a href="https://promex.me/shop/discordoffers/goldsvet-version-9-complete-php-script-laravel-9-php8/">Promex Partner Service</a></p>
<p class="note">This partner service offers a pre-setup environment for hosting the script, which you can edit after initial setup.</p>
<h2>GeoIP2 City Database</h2>
<p>Starting V10 GeoIP2 support is still built in but not in use, you can manually enable it by unmarking the php syntax then<br />Download the GeoIP2 City database from our discord</p>
<h2>Setup Instructions</h2>
<p>This document shows a setup sample on a cPanel server, and can be replicated on other setups.</p>
<ul>
<li>Setup your server with Apache, MySQL, PHP <span style="text-decoration: line-through;">7.1-7.4&nbsp;</span>, PHP 8+, Composer, Laravel 10, Node.js 16 &amp; PM2</li>
<li>Force Domain SSL</li>
<li>Create a new email &amp; password</li>
<li>Create a new database and grant all access</li>
<li>Import the SQL file located in <code>CASINO/DATABASE/MIGRATIONS/betshopme_8.sql</code></li>
<li>
<p dir="auto">Force Domain SSL</p>
</li>
<li>
<p dir="auto">Generate SSL CRT KEY &amp; BUNDLE COPY THE CONTENTS OF YOUR CRT/KEY/BUNDLE TO FILES IN FOLDER CASINO/PTWEBSOCKET/SSL/ Create a new email &amp; password</p>
<p dir="auto">&nbsp;</p>
</li>
<li>
<p dir="auto">Create a new database Grant all access Import the SQL file located in folder CASINO/DATABASE/MIGRATIONS/betshopme_8.sql via PHPMYADMIN to the database --extra DB file not required (experimentalarcadegames.sql) unless you are experimenting with arcade games Zip<br />-----File Uploads-----</p>
</li>
<li>
<p dir="auto">Casino.zip and public_html.zip should be unzipped in the following manner public_html &rarr; this is your public directory casino &rarr; this goes outside your public folder for security so it becomes YOUR ROOT FOLDER /casino /public_html If you decide to move your casino folder INSIDE public_html You have to modify two things 1: open index.php inside public_html and replace all folder paths ( ./../casino portion to ./casino/ ) 2: MUST configure .htaccess to deny .env files or all dot files ( google dot files protection via htaccess)</p>
</li>
<li>
<p dir="auto">//**** extra tip since it contains demo user accounts Generate new password hash for existing users and run this in phpmyadmin (replace hash)&nbsp;<a href="https://bcrypt-generator.com/" rel="nofollow">https://bcrypt-generator.com/</a>&nbsp;If you need to has a new word. Example : (run this in phpmyadmin) UPDATE w_users SET password = '$2a$12$s1RpwEx/oTL3vYQGZjC33eBHECRJb7gkjmAk9Tmyefub7gQ4nh8XS';</p>
<p dir="auto">This has makes all users have password : Test123 ********///</p>
</li>
</ul>
<h2>SSL Specific Instructions</h2>
<p>Delete self signed if any Generate or install the Lets Encrypt one if you have it Save text file via notepad or direct Certificate: (CRT) ==&gt; crt.crt Private Key (KEY) --&gt; key.key Certificate Authority Bundle: (CABUNDLE) ==&gt; intermediate.pem Go in Folder casino/PTWebSocket/ssl and replace those 3 files --------------- FILE EDITS casino/.env EDIT LINES for domain, database and user/password, email and password EDIT casino/config/app.php (URL line 65 )</p>
<h2>File Edits</h2>
<p dir="auto">EDIT casino/public/ ALL SOCKET FILES CHANGE YOUR DOMAIN NAMEIF YOU NEED TO CHANGE PORTS YOU CAN DO SO HERE AS WELLGames downloads Find download packages at discord&nbsp;<a href="https://discord.gg/HscTu67FSu" rel="nofollow">https://discord.gg/HscTu67FSu</a>&nbsp;Currently ~ 1000 games &ndash; 40 GB total. Go to /home/USERNAME/public_html/ <span style="color: #ff0000;">Password is : password</span> Download the core + 3 game packs Unpack <br />(Unix Tip : upload one zip file, install 7z, unpack with:: 7z x -ppassword file.zip ) ---</p>
<h2>Games Downloads</h2>
<p>Find download packages at Discord: <a href="https://discord.gg/HscTu67FSu">https://discord.gg/HscTu67FSu</a></p>
<p>Currently ~1000 games &ndash; 40 GB total.</p>
<h2>PM2 Commands</h2>
<p>PM2 COMMANDS <a href="https://pm2.keymetrics.io/docs/usage/quick-start/" rel="nofollow">https://pm2.keymetrics.io/docs/usage/quick-start/</a>&nbsp;</p>
<p>FROM INSIDE&nbsp;PTWEBSOCKET webfolder COMMANDS::<br /><strong>pm2 start Arcade.js --watch </strong></p>
<p><strong>pm2 start Server.js --watch </strong></p>
<p><strong>pm2 start Slots.js --watch </strong></p>
<p>OR if you tested before and not expecting errors, all in one command :</p>
<p><strong>pm2 start Arcade.js --watch &amp;&amp; pm2 start Server.js --watch &amp;&amp; pm2 start Slots.js &ndash;watch</strong> <br /><br />SAMPLE USEFUL COMMANDS <br />pm2 stop all <br />pm2 delete all <br />pm2 flush <br />pm2 logs <br />all commands on&nbsp;<a href="https://pm2.keymetrics.io/docs/usage/quick-start/" rel="nofollow">https://pm2.keymetrics.io/docs/usage/quick-start/</a>&nbsp;<br /><br />extra tool can be used called wscat (install via ssh)wscat -c "wss://domain:PORT/slots' &lt;--- as an example to make sure you get connected msgOpen ports in Firewall 22154 22188 22197 (or whatever you set your Socket file ports to) Run site :: it should work now if everything was setup correctly. <br /><br />Visual Edits : To change Sliders Text, and footer notes / terms and conditions. Edit : \casino\resources\lang\en\app.php Lines 1255 ++ include text in frontpage Sliders are in root folder /woocasino/ for easy access and change (slider1,2,3,4,5 and for mobile mslider1,2,3,4,5)</p>
<h2>Troubleshooting</h2>
<p dir="auto">Minor troubleshooting if your composer/artisan not ran correctly</p>
<p dir="auto">php artisan cache:clear &amp;&amp; php artisan view:clear &amp;&amp; php artisan config:clear &amp;&amp; php artisan event:clear &amp;&amp; php artisan route:clear</p>
<h2>URL Troubleshooting</h2>
<p dir="auto">URL TROUBLESHOOTING 404 ERROR MAKE SURE YOUR HTACCESS WAS GENERATED, CORRECTLY AND DID HAVE PROPER DATA, OR MANUALLY COPY PASTE PROVIDED HTACCESS&nbsp;</p>
<p dir="auto">&nbsp;</p>
</div>
