TC-Unused-Guid-Search-web
=========================

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/0c38a791c0504304bb40c4378dc551a2)](https://www.codacy.com/app/TrinityCore/TC-Unused-Guid-Search-web?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=TrinityCore/TC-Unused-Guid-Search-web&amp;utm_campaign=Badge_Grade)

[TrinityCore](https://github.com/TrinityCore/TrinityCore) DataBase Developer Tool to find free GUIDs of following tables:

- creature
- gameobject
- waypoint_scripts
- pool_template
- game_event
- creature_equip_template
- trinity_string

Configuration: Copy config.php.conf to config.php edit config.php  
Fill the desired databases with the needed values, it will show:  

$dbs['335'] = ["host", "username", "password", "database1",1,1];  
$dbs['434'] = ["host", "username", "password", "database2",250000,200000];  
$dbs['715'] = ["host", "username", "password", "database3",450000,300000];  

If your spawn targets 335 spawn, creature range starts at 1, gameobject at 1. Last applied update: 2017_03_06_01_world_355.sql  
If your spawn targets 434 spawn, creature range starts at 250000, gameobject at 200000. Last applied update: 2017_03_13_09_world.sql  
If your spawn targets 715 spawn, creature range starts at 450000, gameobject at 300000. Last applied update: 2017_03_13_09_world.sql  

# ![logo](https://raw.githubusercontent.com/ShinDarth/TC-Unused-Guid-Search-web/master/img/preview.png)

### License

This tool is open-sourced software licensed under the [GNU AGPL license](https://github.com/ShinDarth/TC-Unused-Guid-Search-web/blob/master/LICENSE).

