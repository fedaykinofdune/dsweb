dsweb
=====

A web server management and web portal for the DarkStar Private FFXI servers.

**State**
Just started, not much to it atm XD

**Requirements**
- A LAMP stack of sorts, or nginx
- PHP 5.4

**Screenshots**
- Multi-Search: http://i.imgur.com/objeTIM.png
- Character/Account Search: http://i.imgur.com/lB7mZJ6.png
- Character Edit: http://i.imgur.com/VbfUboL.png
- Auction House view: http://i.imgur.com/4oiUtAy.png

**Features**
- Multi search different content from 1 search entry: characters, accounts, skills, items, zones, mobs, etc
- Edit and manage anything in the database
- View anything in the database
- View stuff on auction house

**Todo**
- Full edit all entries
- Backup/Import characters (dumps json extracts and can be uploaded to any other server)
- Manage DarkStar files and configurations (based on version)
- Buy/Sell to auction house
- Send/Recieve items
- Manage equipment


Setup
=====
Very simple to get started:

- Open ```config.php``` in a text editor
- At the top, enter your dspdb details into ```$db_config``` replacing whats currently there
- Upload to same server you have the game running (your public web folder)
- Done

Any issues or help, make a post on this git!
