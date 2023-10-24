<?php

// @author: Michal Dolata <https://github.com/MichalDolata>
// @author: Marcin Lawniczak <marcin@lawniczak.me>
// @date: 26.09.2017
// @update: 26.09.2019
// This task does require Composer. You can add more libraries if you want to.
// We suggest using Guzzle for requests (http://docs.guzzlephp.org/en/stable/)
// Remember to composer install
// The script will be outputting to a web browser, so use HTML for formatting

// When making different kinds of applications, data is often needed that we don't yet have.
// Many 3rd party providers offer APIs (wikipedia.org/wiki/Application_programming_interface)
// that can be consumed to find data we need.

// Your task is to use the The Star Wars API (https://swapi.co/) and it's docs (https://swapi.co/documentation)
// Display all starships provided through the API, with their properties
// Each ship should have the names of pilots and names of films displayed (if none, indicate)
// Each pilot should have its species also displayed
require 'vendor/autoload.php';

use GuzzleHttp\Client;
set_time_limit(0);
$client = New GuzzleHttp\Client();

$res = $client->request('GET', 'https://swapi.dev/api/starships');
$body = json_decode($res->getBody());
$ships = [];
while (true){
    $ships = array_merge($ships,$body->results);
    if($body->next == null){
        break;
    }
    $res = $client->request('GET', $body->next);
    $body = json_decode($res->getBody());
}
$films = [];
$pilots = [];
//get all the pilots and films with as little api calls as possible
foreach ($ships as $ship) {
    if ($ship->pilots != null) {
        foreach($ship->pilots as $pilot){
            if (isset($pilots[basename($pilot)])) {
                continue;
            }
            $res = $client->request('GET', $pilot);
            $pilots[basename($pilot)] = json_decode($res->getBody());
            if (!empty($pilots[basename($pilot)]->species[0])) {
                $res = $client->request('GET', $pilots[basename($pilot)]->species[0]);
                $pilots[basename($pilot)]->species = json_decode($res->getBody())->name;
                continue;
            }
            $pilots[basename($pilot)]->species = "Brak danych";
        }
    }
    if ($ship->films != null) {
        foreach($ship->films as $film){
            if (isset($films[basename($film)])) {
                continue;
            }
            $res = $client->request('GET', $film);
            $films[basename($film)] = json_decode($res->getBody());
        }
    }
}
?>
<style>
    ul {
        list-style-type: none;
    }
    td{
        border: 1px solid black;
    }

</style>
<?php foreach($ships as $ship):?>
<table>
    <tr>
        <th>
            Własność
        </th>
        <th>
            Wartość
        </th>
    </tr>
    <?php
        foreach ($ship as $property => $propetyValue){
            echo "<tr>";
            echo "<td>$property</td>";
            echo "<td>";
            if (empty($propetyValue)) {
                echo "Brak </td>";
            }
            elseif(!is_array($propetyValue)){
                echo "$propetyValue </td>";
            }
            else{
                echo "<ul>";
                foreach ($propetyValue as $value) {
                    echo "<li>";
                    if ($property == 'pilots') {
                        echo $pilots[basename($value)]->name.":gatunek(".$pilots[basename($value)]->species.")";
                    }
                    echo "</li>";
                    if ($property == 'films') {
                        echo $films[basename($value)]->title;
                    }
                }
                echo "</ul> </td>";
            }
        }
    ?>

</table>
<?php endforeach; ?>
