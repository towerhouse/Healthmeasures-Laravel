<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the 
| application Healthmeasures.
| I assumed the post/push actions wanted to be protected by a middleware 
| but comment or change everything as you wish.
| 
| Every route is prefixed by the namespace healthmeasures
*/

$app->group(['namespace' => 'healthmeasures'], function() use ($app)
{
    $app->group(['middleware' => 'auth'], function () use ($app) {
        
        $app->post('measure', [
            'as' => 'saveMeasure', 'uses' => 'Healthmeasures@saveMeasure'
        ]);

        $app->post('measure/bulk', [
            'as' => 'saveBulkMeasures', 'uses' => 'Healthmeasures@saveBulkMeasures'
        ]);
        
        $app->post('value', [
            'as' => 'saveValue', 'uses' => 'Healthmeasures@saveMeasure'
        ]);

        $app->post('value/bulk', [
            'as' => 'saveBulkValues', 'uses' => 'Healthmeasures@saveBulkMeasures'
        ]);
    });
    
    $app->get('measure', [
        'as' => 'getMeasures', 'uses' => 'Healthmeasures@getMeasures'
    ]);
    
    $app->get('measure/{id}', [
        'as' => 'getValueById', 'uses' => 'Healthmeasures@getMeasureById'
    ]);
    
    $app->get('value/{id}', [
        'as' => 'getValueById', 'uses' => 'Healthmeasures@getValueById'
    ]);

    $app->get('stats/graph/{owner_id}/{measure_id}/{start}/{end?}/{graph_title?}/{graph_type?}/{graph_path?}', [
        'as' => 'setValuesAndGraph', 'uses' => 'Healthmeasures@setValuesAndGraph'
    ]);
    
    $app->get('stats/report/{owner_id}/{measure_id}/{start}/{end?}/{graph_title?}/{graph_type?}/{graph_path?}', [
        'as' => 'setValuesAndReport', 'uses' => 'Healthmeasures@setValuesAndReport'
    ]);    
});

