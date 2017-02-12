<?php

namespace Healthmeasures\Laravel;
use \Illuminate\Http\Request;
use \Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use Healthmeasures\Measurement\Measure;
use Healthmeasures\Measurement\Value;
use Healthmeasures\Measurement\Stats;

/**
 * This controller is associated with the REST API from Healthmeasures
 * Responses are formatted following the standard from http://jsonapi.org/
 */
class HealthmeasuresController extends Controller
{

    public function __construct()
    {
        //
    }

    /** Measure methods * */

    /**
     * Replaces (stores or update) a measure if the attributes are repeated
     * The following parameters are received by json payload
     * @param string $name
     * @param string $unit
     * @param string $lang
     * @return json
     */
    public function saveMeasure(Request $request)
    {
        try {
            $measure_name = urldecode($request->json()->get('name'));
            $measure_unit = urldecode($request->json()->get('unit'));
            $measure_lang = urldecode($request->json()->get('lang'));

            $m = new Measure($measure_name, $measure_unit, $measure_lang);
            $m->save();
            return response()->json(['data' => $this->arraySingle($m)]);
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }

    /**
     * Takes a bulk of measure data from a csv file sent as multipart/form-data, sets a default language for
     * every unit if necessary and replaces them all if necessary in a db.
     * @param file handler called "Measure.csv"
     * @param string $lang
     * @return json
     */
    public function saveBulkMeasures(Request $request, $lang = "en")
    {
        $csv_file = 'Measure_csv';
        if ($lang != "en") {
            Measure::setDefaultLanguage($lang);
        }

        if ($request->hasFile($csv_file) && $request->file($csv_file)->isValid()) {
            $pathname = $request->file($csv_file)->getPathname();
            $m = new Measure();
            $collection = $m->bulkConstructor($pathname);
            $response = response()->json(['data' => $this->arrayCollection($collection)]);
        } else {
            $response = response()->json(['id' => time(), 'status' => 400, 'title' => "csv file `$csv_file` is invalid"]);
        }
        return $response;
    }

    /**
     * Returns a collection of measures given a keyword and a requested search criteria.
     * @param string $keyword value for (id, name, unit, lang or all)
     * @param string $criteria  (id, name, unit, lang or all)
     * @return json
     */
    public function getMeasures($keyword, $criteria = 'all')
    {
        $collection = array();
        $m = new Measure();
        $collection = [];

        try {
            switch ($criteria) {
                case 'name':
                    $collection = $m->getMeasuresByName($keyword);
                    break;
                case 'unit':
                    $collection = $m->getMeasuresByUnit($keyword);
                    break;
                case 'lang':
                    $collection = $m->getMeasuresByLang($keyword);
                    break;
                default:
                    $collection = $m->getAll();
            }
            $response = response()->json(['data' => $this->arrayCollection($collection)]);
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }

        return $response;
    }

    /**
     * Returns a value given its id.
     * @param string $id
     * @return json
     */
    public function getMeasureById($id)
    {
        try {
            $m = new Measure();
            $m2 = $m->getById($id);
            $response = response()->json(['data' => $this->arraySingle($m)]);
            if (!$response) {
                $response = new Response(['id' => time(), 'status' => 404, 'title' => "The measure was not found"], 404);
            }
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }

        return $response;
    }

    /** Value methods * */

    /**
     * Replaces (stores or update) a value if the attributes are repeated
     * @param string $owner_id (owner of value, it belongs to an external system)
     * @param string $measure_id
     * @param string $created_at (when the value was taken, mysql format)
     * @param string $value (of the measure)
     * @return json
     */
    public function saveValue(Request $request)
    {
        try {
            $owner_id   = urldecode($request->json()->get('owner_id'));
            $measure_id = urldecode($request->json()->get('measure_id'));
            $created_at = urldecode($request->json()->get('created_at'));
            $value      = urldecode($request->json()->get('value'));

            $v = new Value($owner_id, $measure_id, $created_at, $value);
            $v->save();
            return response()->json(['data' => $this->arraySingle($v)]);
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }

    /**
     * Takes a bulk of values data from a csv file called Value.csv
     * and replaces them all if necessary in a db.
     * @param string path to $csv_file
     * @return json
     */
    public function saveBulkValues(Request $request)
    {
        $csv_file = 'Value_csv';

        if ($request->hasFile($csv_file) && $request->file($csv_file)->isValid()) {
            $pathname = $request->file($csv_file)->getPathname();
            $v = new Value();
            $collection = $v->bulkConstructor($pathname);
            $response = response()->json(['data' => $this->arrayCollection($collection)]);
        } else {
            $response = response()->json(['id' => time(), 'status' => 400, 'title' => "csv file `$csv_file` is invalid"]);
        }
        return $response;
    }

    public function getValues()
    {
        try {
            $v = new Value();
            $collection = $v->getAll();
            $response = response()->json(['data' => $this->arrayCollection($collection)]);
            return $response;
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }
    
    /**
     * Returns a value given its id.
     * @param string $id
     * @return json
     */
    public function getValueById($id)
    {
        try {
            $v = new Value();
            $v->getById($id);
            return response()->json(['data' => $this->arraySingle($v)]);
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }

    /**
     * Returns a collection of values given their owner_id, measure_id, created_at start and ended_at date.
     * @param string $owner_id
     * @param string $measure_id
     * @param mysql date $start
     * @param mysql date $end
     * @return json
     */
    public function getValuesByDate($owner_id, $measure_id, $start, $end)
    {
        try {
            $v = new Value();
            $measure_id = urldecode($measure_id);
            $owner_id = urldecode($owner_id);
            $start = urldecode($start);
            $end = urldecode($end);

            $collection = $v->getValuesByDate($owner_id, $measure_id, $start, $end);
            return response()->json(['data' => $this->arrayCollection($collection)]);
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }

    /** Stat methods * */

    /**
     * Returns a stats response with all the properties for a graph including the
     * path where the graph was stored
     * @param string $owner_id
     * @param string $measure_id
     * @param string $start
     * @param string $end
     * @param string $graph_title
     * @param string $graph_type
     * @param string $graph_path
     * @return json
     */
    public function setValuesAndGraph($owner_id, $measure_id, $start, $end = "now", $graph_title = "default", $graph_type = "linear", $graph_path = 'default')
    {
        try {
            $stats = $this->doSetValuesAndGraph(urldecode($owner_id), 
                    urldecode($measure_id), 
                    urldecode($start), 
                    urldecode($end), 
                    urldecode($graph_title), 
                    urldecode($graph_type), 
                    urldecode($graph_path));
            return response()->json(['data' => $this->arraySingle($stats)]);
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }

    /**
     * Shows a view for a report
     * @param string $owner_id
     * @param string $measure_id
     * @param string $start
     * @param string $end
     * @param string $graph_title
     * @param string $graph_type
     * @param string $graph_path
     * @return html
     */
    public function showReport($owner_id, $measure_id, $start, $end = "now", $graph_title = "default", $graph_type = "linear", $graph_path = 'default')
    {
        try {
            $stats = $this->doSetValuesAndGraph(urldecode($owner_id), 
                    urldecode($measure_id), 
                    urldecode($start), 
                    urldecode($end), 
                    urldecode($graph_title), 
                    urldecode($graph_type), 
                    urldecode($graph_path));
            $all = $stats->getCompleteStatsInformation();
            $date_values = $all['Data Table'];
            unset($all['Data Table']);

            $view_data = [
                'report_title' => $stats->getTitle(),
                'graph_image' => $stats->getPreferredImagePath(),
                'rows_stat' => $all,
                'rows_data' => $date_values,
            ];

            $view_name = view()->exists('healthmeasures.report') ? 'healthmeasures.report' : 'healthmeasures::report';
            return response(view($view_name, $view_data))->header('Content-Type', 'html');
        } catch (\Exception $e) {
            $response = new Response(['id' => time(), 'status' => 400, 'title' => $e->getMessage()], 400);
            $response->header('Content-Type', 'json');
        }
    }

    /** Auxiliar method **/
    protected function doSetValuesAndGraph($owner_id, $measure_id, $start, $end = "now", $graph_title = "default", $graph_type = "linear", $graph_path = 'default')
    {
        $v = new Value();
        $values = $v->getValuesByDate($owner_id, $measure_id, $start, $end);
        $stats = new Stats($values);
        if ($graph_title != "default") {
            $stats->setTitle($graph_title);
        }

        if ($graph_path == 'default') {
            $stats->image_path = public_path("healthmeasures/" . $stats->getId() . '.jpg');
        }

        if (!file_exists($stats->image_path)) {
            $stats->generateDateMeasureGraph($graph_type);
        }

        //Set public url image path
        $stats->url_image_path = url("healthmeasures/" . basename($stats->image_path));

        return $stats;
    }
    
    /** Helpers to encode data in json**/
    
    protected function arraySingle($o)
    {
        return $o->toArray();
    }
    
    protected function arrayCollection($coll)
    {
        $newColl = [];
        foreach ($coll as $o) {
            $newColl[] = $o->toArray();
        }
        return $newColl;
    }

}
