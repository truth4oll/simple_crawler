<?php

header('Content-Type: application/json');

include_once __DIR__.'/classes/Crawler.php';


$crawler = new Crawler();

$term = $_GET['term'];

$aItems = $crawler->search($term);

echo json_encode($aItems);
