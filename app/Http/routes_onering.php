<?php

// cron route to kick off scheduled programs.
Route::get('onering/destiny', 'OneRingController@getDestiny');

// Coordinate the Nine.
Route::get('onering/nazgul/status/{nazgul_id}/{status_code}', 'OneRingController@setNazgulStatus');
Route::get('onering/nazgul', 'OneRingController@getNazgul');
Route::post('onering/nazgul', 'OneRingController@postNazgul');

// Set timers for RCP scrapers.
Route::get('onering/rcp/scrapers', 'OneRingController@getRcpScrapers');
Route::post('onering/rcp/scrapers', 'OneRingController@postRcpScrapers');

// The master auto-trade killswitch.
Route::get('onering/autotrade/on', 'OneRingController@activateAutotrade');
Route::get('onering/autotrade/off', 'OneRingController@deActivateAutotrade');

Route::get('onering/ajax/analyze/{question_id}', 'AjaxController@getAjaxBubbles');
Route::get('onering/bubbles', 'OneRingController@makeBubbles');
