<?php

Route::get('elections/scrape/cnn', 'ElectionsController@scrapeCNN');

Route::get('elections/potus', 'ElectionsController@getPotus');
Route::get('elections/senate', 'ElectionsController@getSenate');
Route::get('elections/governor', 'ElectionsController@getGovernor');
Route::get('elections/house', 'ElectionsController@getHouse');

// Data scrapes
Route::get('elections/scrape/nevada', 'ElectionScrapeController@getNevada');
Route::get('elections/scrape/north_carolina', 'ElectionScrapeController@getNorthCarolina');
Route::get('elections/scrape/florida', 'ElectionScrapeController@getFlorida');
Route::get('elections/scrape/iowa', 'ElectionScrapeController@getIowa');
Route::get('elections/scrape/louisiana', 'ElectionScrapeController@getLouisiana');

// AJAX requests
Route::post('elections/races/update', 'ElectionsController@ajaxUpdateRace');
Route::get('elections/ajax/graph/{stat}/{race_id}', 'ElectionsController@getAjaxGraph');
Route::get('elections/ajax/quote/{race_id}', 'ElectionsController@getAjaxPriceQuote');
Route::get('elections/ajax/visit/{race_id}', 'ElectionsController@getAjaxVisitMarket');
