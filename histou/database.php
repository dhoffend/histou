<?php
class Influxdb
{
    private $url;
    
    public function __construct($url) 
    {
        $this->url = $url."&q=";
    }

    public function makeRequest($query)
    {
        $content = file_get_contents($this->url.urlencode($query));
        if ($content === false) {
            returnData('Influxdb not reachable', 1, 'Influxdb not reachable');
        } else {
            return json_decode($content, true)['results'];
        }
    }
    
    public function filterPerfdata($request, $host, $service, $fieldSeperator)
    {
        $regex = sprintf("/%s%s%s%s(.*?)%s(.*?)%s(.*)/", preg_quote($host, '/'), $fieldSeperator, preg_quote($service, '/'), $fieldSeperator, $fieldSeperator, $fieldSeperator);
        $data = array('host' => $host, 'service' => $service);
        foreach ($request as $queryResult) {
            if (!empty($queryResult['series'])) {
                foreach ($queryResult['series'] as $table) {
                    if (preg_match($regex, $table['name'], $result)) {                        
                        if (!array_key_exists('perfLabel', $data)) {                    
                            $data['perfLabel'] = array();
                        }
                        if (!array_key_exists($result[2], $data['perfLabel'])) {
                            $data['perfLabel'][$result[2]] = array();
                        }
                        $data['command'] = $result[1];
                        $data['perfLabel'][$result[2]][$result[3]] = array();
                        if(array_key_exists('columns', $table)){
                            for($tagId = 1; $tagId < sizeof($table['columns']); $tagId++){
                                $data['perfLabel'][$result[2]][$result[3]][$table['columns'][$tagId]] = $table['values'][0][$tagId];
                            }
                        }
                    }
                }
            }else{
                return array($queryResult['error']);
            }
        }
        if (isset($data['perfLabel'])){
            ksort($data['perfLabel'], SORT_NATURAL);
            foreach($data['perfLabel'] as &$perfLabel){
                uksort($perfLabel, "Influxdb::comparePerfLabel");
            }
        }
        //echo "<pre>";print_r($data);echo "</pre>";
        return $data;
    }
    
    private static function getPerfLabelIndex($label) {
        switch($label) {
            case 'value':
                return 1;
            case 'warn':
                return 2;
            case 'crit':
                return 3;
            case 'min':
                return 4;
            case 'max':
                return 5;
        }
        return 0;
    }
    
    private static function comparePerfLabel($firstLabel, $secondLabel) {
        $first = Influxdb::getPerfLabelIndex($firstLabel);
        $second = Influxdb::getPerfLabelIndex($secondLabel);
        if ($first == $second) {
            return 0;
        }
        return ($first < $second) ? -1 : 1;
    }
}
?>
