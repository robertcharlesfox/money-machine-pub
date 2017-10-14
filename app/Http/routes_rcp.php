<?php

Route::get('new-rcp/{pi_contest_id}', 'NewRcpController@scrapeRcp');

Route::post('rcp/ajax/pollster/save', 'AjaxController@ajaxSavePollster');

Route::get('rcp/projections/tvc/{pi_contest_id}', 'ProjectionController@getTvCAnalysis');
Route::get('rcp/projections/favorables/{pi_contest_id}', 'ProjectionController@getFavorables');

Route::get('rcp/projections/drops', 'ProjectionController@getDropAnalysis');
Route::post('rcp/projections/drops', 'ProjectionController@postDropAnalysis');

Route::get('rcp/updates/approval/{pi_contest_id}', 'RcpController@getRcpApprovalUpdates');
Route::get('rcp/updates/nomination/dem', 'RcpController@getRcpDemUpdates');
Route::get('rcp/updates/nomination/gop', 'RcpController@getRcpGopUpdates');
Route::get('rcp/projections/approval/{pi_contest_id}', 'RcpController@getRcpApprovalProjections');
Route::get('rcp/projections/nomination/dem/{include_debate?}', 'RcpController@getRcpDemProjections');
Route::get('rcp/projections/nomination/gop/{include_debate?}', 'RcpController@getRcpGopProjections');
Route::get('rcp/pollsters/{contest_id?}', 'RcpController@getRcpPollsters');

Route::get('rcp', 'RcpController@getRcpPolls');
Route::get('rcp/scrapes', 'RcpController@getRcpScrapes');

Route::get('rcp/purge', 'RcpController@getRcpPurge');
Route::get('michanikos/{poll_id}', 'MichanikosController@getMichanikos');
