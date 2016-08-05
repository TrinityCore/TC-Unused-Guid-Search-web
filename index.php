<?php
require_once("config.php");

$dbLinks = [];
$dbErrors = [];

$table_creature = 1;
$table_gameobject = 2;
$table_waypoint_scripts = 3;
$table_pool_template = 4;
$table_game_event = 5;
$table_creature_equip_template = 6;
$table_trinity_string = 7;

$table_creature_sel =
$table_gameobject_sel =
$table_waypoint_scripts_sel =
$table_pool_template_sel =
$table_game_event_sel =
$table_creature_equip_template_sel =
$table_trinity_string_sel = "";

global $dbs;

foreach ($dbs as $n => $db) {
    $dbLinks[$n] = @new mysqli($db[0], $db[1], $db[2], $db[3]);

    if (mysqli_connect_error()) {
        $dbErrors[] = 'Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
        unset($dbLinks[$n]);
    }
}

if (isset($_GET['table']) && $_GET['table'] != "") {
    switch ($_GET['table']) {
        case $table_creature:
            $table = "creature";
            $param = "guid";
            $table_creature_sel = "selected";
            break;

        case $table_gameobject:
            $table = "gameobject";
            $param = "guid";
            $table_gameobject_sel = "selected";
            break;

        case $table_waypoint_scripts:
            $table = "waypoint_scripts";
            $param = "guid";
            $table_waypoint_scripts_sel = "selected";
            break;

        case $table_pool_template:
            $table = "pool_template";
            $param = "entry";
            $table_pool_template_sel = "selected";
            break;

        case $table_game_event:
            $table = "game_event";
            $param = "eventEntry";
            $table_game_event_sel = "selected";
            break;

        case $table_creature_equip_template:
            $table = "creature_equip_template";
            $param = "CreatureID";
            $table_creature_equip_template_sel = "selected";
            break;

        case $table_trinity_string:
            $table = "trinity_string";
            $param = "entry";
            $table_trinity_string_sel = "selected";
            break;
    }
}

// make guid search guid continous enabled by default
$continuous = (isset($_GET['continuous']) && $_GET['continuous'] == "on");
$continuous_checked = $continuous ? "checked" : "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Unused Guid Search">
    <meta name="author" content="ShinDarth">
    <title>TC Unused Guid Search</title>

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

</head>
<body>
<div class="container">
    <h2 class="text-center">
        <img src="img/trinitycore.png" alt="TrinityCore">TrinityCore Unused GUID Search Tool
    </h2>

    <hr>

    <form style="margin: auto;" class="form-inline" role="form" method="GET">
        <div class="form-group">
            <strong>DB:</strong>
            <select name="db" title="DB" class="text-center">
                <?php
                foreach ($dbLinks as $n => $l) {
                    echo '<option value="' . $n . '" ' . (isset($_GET['db']) && $_GET['db'] == $n ? 'selected' : '') . '>' . (is_numeric($n) ? $dbs[$n][3] : $n) . "</option>\n";
                }
                ?>
            </select><br>
            <strong>Table:</strong>
            <select name="table" title="Table" class="text-center">
                <option value="<?= $table_creature ?>"<?= $table_creature_sel ?>>`creature`</option>
                <option value="<?= $table_gameobject ?>"<?= $table_gameobject_sel ?>>`gameobject`</option>
                <option value="<?= $table_waypoint_scripts ?>"<?= $table_waypoint_scripts_sel ?>>`waypoint_scripts`</option>
                <option value="<?= $table_pool_template ?>"<?= $table_pool_template_sel ?>>`pool_template`</option>
                <option value="<?= $table_game_event ?>"<?= $table_game_event_sel ?>>`game_event`</option>
                <option value="<?= $table_creature_equip_template ?>"<?= $table_creature_equip_template_sel ?>>`creature_equip_template`</option>
                <option value="<?= $table_trinity_string ?>"<?= $table_trinity_string_sel ?>>`trinity_string`</option>
            </select>
        </div>
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Starting from:</div>
                <input name="starting-from" style="max-width: 120px;" class="form-control" type="text" value="<?php if (isset($_GET['starting-from'])) { echo $_GET['starting-from']; } ?>" placeholder="1">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">GUID amount:</div>
                <input name="amount" style="max-width: 80px;" class="form-control" type="text" value="<?php if (isset($_GET['amount'])) { echo $_GET['amount']; } ?>" placeholder="10">
            </div>
        </div>
        <div class="checkbox">
            <label>
                <input name="continuous" type="checkbox" <?= $continuous_checked ?>> Continuous
            </label>
        </div>
        <button type="submit" class="btn btn-success">Search</button>
    </form>

    <br>

    <?php

    if ($dbErrors) {
        echo '<pre>' . implode("\n", $dbErrors) . '</pre>';
    }

    if (!$dbLinks) {
        die('No DB connections could be established.');
    }

    if (isset($_GET['db']) && empty($dbLinks[$_GET['db']])) {
        die('Invalid DB selected');
    }

    if (isset($_GET['table'])) {
        $db = &$dbLinks[$_GET['db']];

        if (isset($_GET['starting-from']) && $_GET['starting-from'] != null) {
            $starting_from = $_GET['starting-from'];
        } else {
            $starting_from = 1;
        }

        if (isset($_GET['amount']) && $_GET['amount'] != null) {
            $amount = $_GET['amount'];
        } else {
            $amount = 10;
        }

        $query_max_min = sprintf("SELECT MAX(%s), MIN(%s) FROM %s", $param, $param, $table);
        $result_max_min = $db->query($query_max_min);

        if (!$result_max_min) {
            die("Error querying: " . $query_max_min);
        }

        $row_max_min = mysqli_fetch_row($result_max_min);

        $MAX_GUID = $row_max_min[0];
        $MIN_GUID = $row_max_min[1];

        printf("<p class=\"text-center\">Table <strong>`%s`</strong> has MAX(%s) = <strong>%d</strong> and MIN(%s)= <strong>%d</strong></p>", $table, $param, $MAX_GUID, $param, $MIN_GUID);

        $query = sprintf("SELECT %s FROM `%s` WHERE %s >= %d ORDER BY %s ASC", $param, $table, $param, $starting_from, $param);
        $result = $db->query($query);

        if (!$result) {
            die("Error querying: " . $query);
        }

        $row = mysqli_fetch_row($result);
        $last = $row[0];

        $count = 0;

        printf("<div><pre>%s</pre></div>", $query);

        echo "<div><pre>";

        if ($continuous) {
            while (($row = mysqli_fetch_row($result)) != null) {
                if ($count >= $amount) {
                    break;
                }

                $current = $row[0];

                if ($current - $last - 1 >= $amount) {
                    for ($i = $last + 1; $i < $current; $i++) {
                        if ($count >= $amount) {
                            break;
                        }

                        printf("%d<br>", $i);
                        $count++;
                    }

                    break;
                }

                $last = $current;
            }
        } else {
            while (($row = mysqli_fetch_row($result)) != null) {
                if ($count >= $amount) {
                    break;
                }

                $current = $row[0];

                if ($current != $last + 1) {
                    for ($i = $last + 1; $i < $current; $i++) {
                        if ($count >= $amount) {
                            break;
                        }

                        printf("%d<br>", $i);
                        $count++;
                    }
                }

                $last = $current;
            }
        }

        echo "</pre></div>";
    }

    echo "<hr>";
    echo "<p class=\"text-center\">";

    foreach($dbLinks as $name => $db) {
        $creatureRange = $dbs[$name][4];
        $gameObjectRange = $dbs[$name][5];

        $query = "SELECT `name` FROM `updates` ORDER BY `timestamp` DESC LIMIT 1";
        $result = $db->query($query);

        if (($object = mysqli_fetch_object($result)) != null) {
            $last = $object->name;
        } else {
            $last = "none";
        }

        echo "If your spawn targets $name spawn, creature range starts at $creatureRange, gameobject at $gameObjectRange. Last applied update: $last<br>";
    }

    echo "<br>";
    echo "Example: if you are going to spawn one NPC which has been present from wow 1 to wow 7 on same coordinates, then you must choose 1 as start range, if it was moved of place on wow 4 then you must choose the range of 250000.<br>";
    echo "         if you are going to spawn one NPC which has been present only on 4.3.4 or higher you must choose 250000<br>";
    echo "</p>";

    ?>
    <hr>
    <h4 class="text-center">
        Coded by <a href="http://www.github.com/ShinDarth">ShinDarth</a>&nbsp;<iframe style="vertical-align: middle;" src="http://ghbtns.com/github-btn.html?user=ShinDarth&repo=TC-Unused-Guid-Search-web&type=watch&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="110" height="20"></iframe>
    </h4>
    <a href="https://github.com/TrinityCore/TC-Unused-Guid-Search-web">
        <img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/38ef81f8aca64bb9a64448d0d70f1308ef5341ab/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Get me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png">
    </a>
</div>

</body>
</html>
