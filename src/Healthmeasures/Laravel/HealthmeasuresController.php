<?php

namespace Healthmeasures\Laravel;

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
     * @param string $measure_name
     * @param string $measure_unit
     * @param string $measure_lang
     * @return json
     */
    public function saveMeasure($measure_name, $measure_unit, $measure_lang)
    {
        $m = new Measure($measure_name, $measure_unit, $measure_lang);
        $m->save();
        return response()->json(['data' => $this->arraySingle($m)]);
    }

    /**
     * Takes a bulk of measure data from a csv file, sets a default language for
     * every unit if necessary and replaces them all if necessary in a db.
     * @param string path to $csv_file
     * @param string $lang
     * @return json
     */
    public function saveBulkMeasures($csv_file, $lang = "en")
    {
        if (Input::file($csv_file)->isValid()) {
            if ($lang != "en") {
                Measure::setDefaultLanguage($lang);
            }
            $pathname = Input::getPathname();
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
                case 'id':
                    $collection = $m->getById($keyword);
                    break;
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
            $response = response()->json(['id' => time(), 'status' => 400, 'title' => $e->getMessage()]);
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
        $m = new Measure();
        $m->getById($id);
        return response()->json(['data' => $this->arraySingle($m)]);
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
    public function saveValue($owner_id, $measure_id, $created_at, $value)
    {
        $v = new Value($owner_id, $measure_id, $created_at, $value);
        $v->save();
        return response()->json(['data' => $this->arraySingle($v)]);
    }

    /**
     * Takes a bulk of values data from a csv file
     * and replaces them all if necessary in a db.
     * @param string path to $csv_file
     * @return json
     */
    public function saveBulkValues($csv_file)
    {
        if (Input::file($csv_file)->isValid()) {
            $pathname = Input::getPathname();
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
        $v = new Value();
        $collection = $v->getAll();
        $response = response()->json(['data' => $this->arrayCollection($collection)]);
        return $response;
    }
    
    /**
     * Returns a value given its id.
     * @param string $id
     * @return json
     */
    public function getValueById($id)
    {
        $v = new Value();
        $v->getById($id);
        return response()->json(['data' => $this->arraySingle($v)]);
    }

    /**
     * Returns a collection of values given their owner_id, measure_id, created_at start and ended_at date.
     * @param string $owner_id
     * @param string $measure_id
     * @param mysql date $start
     * @param mysql date $end (optional)
     * @return json
     */
    public function getValuesByDate($owner_id, $measure_id, $start, $end = "now")
    {
        $v = new Value();
        $collection = $v->getValuesByDate($owner_id, $measure_id, $start, $end);
        return response()->json(['data' => $this->arrayCollection($collection)]);
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
        $stats = $this->doSetValuesAndGraph($owner_id, $measure_id, $start, $end, $graph_title, $graph_type, $graph_path);
        return response()->json(['data' => $this->arraySingle($stats)]);
    }

    
    public function showReport($owner_id, $measure_id, $start, $end = "now", $graph_title = "default", $graph_type = "linear", $graph_path = 'default')
    {
        $stats = $this->doSetValuesAndGraph($owner_id, $measure_id, $start, $end, $graph_title, $graph_type, $graph_path);
        $all = $stats->getCompleteStatsInformation();
        $date_values = $all['Data Table'];
        unset($all['Data Table']);
        
        $view_data = [
            'report_title' => $stats->getTitle(),
            'graph_image' => $stats->getPreferredImagePath(),
            'rows_stat' => $all,
            'rows_data' => $date_values,
        ];
        
        return response(view('healthmeasures::report', $view_data))->header('Content-Type', 'html');
    }

    /** Auxiliar method **/
    protected function doSetValuesAndGraph($owner_id, $measure_id, $start, $end = "now", $graph_title = "default", $graph_type = "linear", $graph_path = 'default')
    {
        $v = new Value();
        $values = $v->getValuesByDate($owner_id, $measure_id, $start, $end);
        $stats = new Stats($values);
        if ($graph_title != "default") {
            $stats->title = $graph_title;
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
