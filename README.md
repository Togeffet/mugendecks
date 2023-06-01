# mugendecks

Super in-progress -- If you're interested in contributing please check back in a bit when there's more stuff! For now it's only really set up to work directly on the server, I'll get it working and documented more clearly to be able to work locally...Soon(TM).

To run this, make sure you have the following installed first:
* [PHP](https://www.php.net/manual/en/install.php) (I have version 7.2.24 but any version would probably be fine)
* [Limelight](https://github.com/nihongodera/limelight)

To download load files:
They can be found at https://drive.google.com/drive/u/0/folders/1_CVAI_-oJ2JgBiK9z9Xm17fB68ik0D4n

To create load files:

JMDict
* Download the jmdict-eng json from [jmdict-simplified](https://github.com/scriptin/jmdict-simplified/releases)
* Extract it to load_files/jsons, renaming it to jmdict_eng.json while you're at it
* cd into the jmdict directory
* Run `php create_jmdict_load_files.php`

If you want to choose the sql files it creates, change the constants to true/false near the top of create_jmdict_load_files.php.


JMNEDict
* Download the jmnedict-eng json from [jmdict-simplified](https://github.com/scriptin/jmdict-simplified/releases)
* Extract it to load_files/jsons, renaming it to jmnedict_eng.json while you're at it
* cd into the jmnedict directory
* Run `php create_jmnedict_load_files.php`

The server at the moment has these two dictionaries combined...but I'm not so sure how good of an idea that is so please experiment or think about this if you can!


Run a local MySQL server and insert the load files. This can be done using the command line or MySQL Workbench, etc.

Create a privileged MySQL user named "admin" and with the password "pass" or change the values near the top of scripts.php

Finally, run Mugendecks by changing you directory to the root of the folder and running "php index.php" (I think, it's kinda untested so you may need to change more stuff, sorry!)
