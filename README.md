# mugendecks

Super in-progress -- If you're interested in contributing please check back in a bit when there's more stuff! For now it's only really set up to work directly on the server, I'll get it working and documented more clearly to be able to work locally...Soon(TM).

To run this, make sure you have the following installed first:
* [PHP](https://www.php.net/manual/en/install.php) (I have version 7.2.24 but any version would probably be fine)
* [Limelight](https://github.com/nihongodera/limelight)


To create load files (only jmdict at the moment):
* Download the jmdict-eng json from [jmdict-simplified](https://github.com/scriptin/jmdict-simplified/releases/tag/3.5.0+20230522121815)
* Extract it to load_files/jsons, renaming it to jmdict_eng.json while you're at it
* cd into the jmdict directory
* Run `php create_jmdict_load_files.php`

If you want to choose the sql files it creates, change the constants to true/false near the top of create_jmdict_load_files.php.