<?php

Route::get('admin/import', 'ImportController@getImport');
Route::get('admin/polls/old/{poll_id}/{is_old}', 'AdminController@getMarkPollAsOld');

Route::get('admin/contests/competition/{contest_id}', 'PiController@getCompetitionQuestions');
Route::get('admin/contests/inactive', 'AdminController@getInactiveContests');
Route::get('admin/contests/create', 'AdminController@getContestForm');
Route::get('admin/contests/edit/{contest_id}', 'AdminController@getContestForm');
Route::get('admin/contests/activate/{contest_id}', 'AdminController@getActivateContest');
Route::get('admin/contests/deactivate/{contest_id}', 'AdminController@getDeactivateContest');
Route::get('admin/contests', 'AdminController@getContests');
Route::post('admin/contests/random', 'AdminController@postContestRandomPollsForm');
Route::post('admin/contests/trading', 'AdminController@postContestTradingForm');
Route::post('admin/contests', 'AdminController@postContestForm');

Route::get('admin/contest_pollsters/reactivate/{contest_pollster_id}', 'AdminController@reactivateContestPollster');
Route::post('admin/contest_pollsters/result', 'AdminController@postContestPollsterResultForm');
Route::post('admin/contest_pollsters', 'AdminController@postContestPollsterForm');

Route::get('admin/questions/inactive', 'AdminController@getInactiveQuestions');
Route::get('admin/questions/create', 'AdminController@getQuestionForm');
Route::get('admin/questions/edit/{question_id}', 'AdminController@getQuestionForm');
Route::get('admin/questions/activate/{question_id}', 'AdminController@getActivateQuestion');
Route::get('admin/questions/deactivate/{question_id}', 'AdminController@getDeactivateQuestion');
Route::get('admin/questions', 'AdminController@getQuestions');
Route::post('admin/questions/competition', 'PiController@postCompetitionQuestionForm');
Route::post('admin/questions', 'AdminController@postQuestionForm');
