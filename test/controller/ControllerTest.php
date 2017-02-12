<?php
//Following documentation by https://ole.michelsen.dk/blog/testing-your-api-with-phpunit.html

class ControllerTest extends PHPUnit_Framework_TestCase
{
    protected $client;
    protected $mock_value_id;
    protected $mock_measure_id;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://mbhealth-api.local'
        ]);
    }
    
    protected function getMockMeasureObjectId()
    {
        $response = $this->client->request('GET', '/healthmeasures/measure/hips/name');
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        return $data['data'][0]['id'];
    }
    
    protected function getMockValueObjectId()
    {
        $response = $this->client->request('GET', '/healthmeasures/value');
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        return $data['data'][0]['id'];
    }

    public function testPost_NewMeasure_MeasureObject()
    {
        $response = $this->client->post('/healthmeasures/measure', [
            'json' => [
                'name'    => "Hips",
                'unit'    => 'cms',
                'lang'    => 'en'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertEquals("Hips", $data['data']['attributes']['name']);
        $this->mock_measure_id = $data['data']['id'];
    }
    
    public function testGet_Measures()
    {
        //Get measures by name
        $response = $this->client->request('GET', '/healthmeasures/measure/hips/name');
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertEquals(count($data['data']), 1);
        $this->assertEquals("Hips", $data['data'][0]['attributes']['name']);
    }
    
    public function testGet_MeasureById()
    {    
        //Get measure by id
        $id = $this->getMockMeasureObjectId();
        $response2 = $this->client->request('GET', '/healthmeasures/measure/' . $id);
        $data2 = json_decode($response2->getBody(), true);
        $this->assertEquals($id, $data2['data']['id']);
    }
    
    public function testPost_NewValue_ValueObject()
    {
        $response = $this->client->post('/healthmeasures/value', [
            'json' => [
                'owner_id' => 'testingowner', 
                'measure_id' => $this->getMockMeasureObjectId(), 
                'created_at' => "2023-02-11 10:42:43", 
                'value' => '90'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertEquals("90", $data['data']['attributes']['value']);
        $this->assertNotEmpty($data['data']['id']);
        $this->mock_value_id = $data['data']['id'];
    }
    
    public function testGet_ValueById()
    {       
        //Get measure by id
        $id = $this->getMockValueObjectId();
        $response2 = $this->client->request('GET', '/healthmeasures/value/' . $id);
        $data2 = json_decode($response2->getBody(), true);
        $this->assertEquals($id, $data2['data']['id']);
    }
    
    public function testGet_Values()
    {       
        $response = $this->client->request('GET', '/healthmeasures/value');
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertGreaterThanOrEqual(1, count($data['data']));
    }
    
    public function testGet_ValuesByDate()
    {    
        $owner_id = 'testingowner';
        $measure_id = $this->getMockMeasureObjectId();
        $start = "2023-02-11 10:42:43";
        $end = "2023-02-11 10:42:43";
        $response = $this->client->request('GET', "/healthmeasures/value/date/$owner_id/$measure_id/$start/$end");
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertEquals(count($data['data']), 1);
        $this->assertEquals("90", $data['data'][0]['attributes']['value']);
        
        //There are no values on the future, the set of data is empty
        $start2 = "2100-01-01";
        $response2 = $this->client->request('GET', "/healthmeasures/value/date/$owner_id/$measure_id/$start2/$start2");
        $this->assertEquals(200, $response2->getStatusCode());
        $data2 = json_decode($response2->getBody(), true);
        $this->assertEquals(count($data2['data']), 0);
    }
    
    public function testGet_ShowReport()
    {
        $owner_id = 'testingowner';
        $measure_id = $this->getMockMeasureObjectId();
        $start = "2023-02-11 10:42:43";
        $end = "2023-02-11 10:42:43";
        $response = $this->client->request('GET', "/healthmeasures/stats/report/html/$owner_id/$measure_id/$start/$end/default/linear/default");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('html', $response->getHeader('Content-Type')[0]);
    }
    
    public function testGet_SetValuesAndGraph()
    {
        $owner_id = 'testingowner';
        $measure_id = $this->getMockMeasureObjectId();
        $start = "2023-02-11 10:42:43";
        $end = "2023-02-11 10:42:43";
        $response = $this->client->request('GET', "/healthmeasures/stats/graph/$owner_id/$measure_id/$start/$end/default/linear/default");
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertNotEmpty($data['data']['attributes']['image_path']);
    }
    
}
