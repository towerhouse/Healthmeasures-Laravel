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

$app = app();

$app->group(['prefix' => 'healthmeasures'], function() use ($app)
{
    $app->group(['middleware' => 'auth'], function () use ($app) {
        
        $app->post('measure', [
            'as' => 'saveMeasure', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@saveMeasure'
        ]);

        $app->post('measure/bulk', [
            'as' => 'saveBulkMeasures', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@saveBulkMeasures'
        ]);
        
        $app->post('value', [
            'as' => 'saveValue', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@saveMeasure'
        ]);

        $app->post('value/bulk', [
            'as' => 'saveBulkValues', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@saveBulkMeasures'
        ]);
    });
    
    $app->get('measure', [
        'as' => 'getMeasures', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@getMeasures'
    ]);
    
    $app->get('measure/{id}', [
        'as' => 'getMeasureById', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@getMeasureById'
    ]);
    
    $app->get('value', [
        'as' => 'getValues', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@getValues'
    ]);
    
    $app->get('value/{id}', [
        'as' => 'getValueById', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@getValueById'
    ]);

    $app->get('stats/graph/{owner_id}/{measure_id}/{start}/{end}/{graph_title}/{graph_type}/{graph_path}', [
        'as' => 'setValuesAndGraph', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@setValuesAndGraph'
    ]);
    
    $app->get('stats/report/{owner_id}/{measure_id}/{start}/{end}/{graph_title}/{graph_type}/{graph_path}', [
        'as' => 'setValuesAndReport', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@setValuesAndReport'
    ]);    
    
    $app->get('stats/report/html/{owner_id}/{measure_id}/{start}/{end}/{graph_title}/{graph_type}/{graph_path}', [
        'as' => 'showReport', 'uses' => 'Healthmeasures\Laravel\HealthmeasuresController@showReport'
    ]);  
});

