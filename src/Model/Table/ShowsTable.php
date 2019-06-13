<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Network\Http\Client;
use Cake\Netowrk\Http\Response;

class ShowsTable extends Table
{
	
	public function initialize(array $config)
	{
		$this->table('series_episodes');
		$this->primaryKey('id');
		
		$this->belongsTo('LastDownloaded', [
			'className' => 'series',
			'bindingKey' => 'id',
			'foreignKey' => 'series_id',
			'propertyName' => 'series'
		]);
		
		$this->belongsTo('Starts', [
			'className' => 'series',
			'bindingKey' => 'begin_episode_id',
			'foreignKey' => 'id',
			'propertyName' => 'starts'
		]);
		
		$this->belongsTo('episode_releases', [
			'bindingKey' => 'episode_id',
			'foreignKey' => 'id',
			'propertyName' => 'episodes',
		]);

	}
	
	public function getTvShow($show)
	{
		$http = new Client();
		$response = $http->get('http://thetvdb.com/api/GetSeries.php', ['seriesname' => $show]);
		if ($response->statusCode() == 200)
		{
			$xml = $response->xml;
			return $xml;
		}
		else {
			return "Failure";			
		}
		
	}
	
	public function getEpisode($showId)
	{
		$apiKey = "";
		$http = new Client();
		$response = $http->get('http://thetvdb.com/api/' . $apiKey . '/series/' . $showId . '/all/en.xml');
				if ($response->statusCode() == 200)
		{
			$xml = $response->xml;
			return $xml;
		}
		else {
			return "Failure";			
		}
	}
	
	public function addToCsv($show, $season, $episode)
	{
		try
		{
			$shows = array();
			$exist = false;
			
			$handle = fopen("/root/.flexget/shows.csv", "r");
    		while ($data = fgetcsv($handle, 1000, "|")) {
        		$num = count($data);
        		for ($c=0; $c < $num; $c++) {
        			if ($show == $data[$c])
					{
						return "exists";
						break;
					}
        			$shows[] = $data[$c];
        		}
    		}
			
    		fclose($handle);
			
			$handle = fopen("/root/.flexget/removed.csv", "r");
    		while ($data = fgetcsv($handle, 1000, "|")) {
        		$num = count($data);
        		for ($c=0; $c < $num; $c++) {
        			if ($show == $data[$c])
					{
						return "exists";
						break;
					}
        		}
    		}
			
    		fclose($handle);
			$shows[] = $show;
			natcasesort($shows);
			
			$fp = fopen('/root/.flexget/shows.csv', 'w');
			
		    foreach ($shows as $item)
    		{
    			fwrite($fp, $item . "\n");
			}
			
			fclose($fp);
			
			return "Success";

		}
		catch (Exception $e) {
			return $e->getMessage();
		}
	
	}

	public function updateCsvs($shows, $removed) 
	{
			try {		
							
				$count = count($shows);
				
				$fp = fopen('/root/.flexget/shows.csv', 'w');
				for ($c=0; $c < $count; $c++) {
					fwrite($fp, $shows[$c] . "\n");
				}
							
				fclose($fp);
				
				$count = count($removed);
				
				$fp = fopen('/root/.flexget/removed.csv', 'w');

				for ($c=0; $c < $count; $c++) {
					fwrite($fp, $removed[$c] . "\n");
				}
								
				fclose($fp);
								
				return 'Success';
			}
			catch (Exception $e) {
				return "Error: " . $e->getMessage();
			}
	}
	
	public function flexgetBegin($show, $season, $episode)
	{
		$cmd = '/usr/local/bin/flexget -c /root/.flexget/config.yml series begin "' . $show . '" S' . $season . 'E' . $episode;
		$flexgetResults = exec($cmd);
		return $flexgetResults;
	}
	
	public function flexgetDelete($show)
	{
		$cmd = '/usr/local/bin/flexget -c /root/.flexget/config.yml series forget "' . $show . '"';
        $flexgetResults = exec($cmd);
		if ($flexgetResults) {
			return "Success";
		}
		else {
			return "Failure";
		}
	}
	public function csvRemove($show)
	{
		try
		{
			$shows = array();
			$removed = array();
			
			$handle = fopen("/root/.flexget/shows.csv", "r");
    		while ($data = fgetcsv($handle, 1000, "|")) {
        		$num = count($data);
        		for ($c=0; $c < $num; $c++) {
        			$shows[] = $data[$c];
        		}
    		}
			
    		fclose($handle);
			
			$handle = fopen("/root/.flexget/removed.csv", "r");
    		while ($data = fgetcsv($handle, 1000, "|")) {
        		$num = count($data);
        		for ($c=0; $c < $num; $c++) {
					$removed[] = $data[$c];
        		}
    		}
			
    		fclose($handle);

			natcasesort($shows);
			natcasesort($removed);
			
			$fp = fopen('/root/.flexget/shows.csv', 'w');
			
		    foreach ($shows as $item)
    		{
    			if ($item != $show)
    			fwrite($fp, $item . "\n");
			}
			
			fclose($fp);
			
			$fp = fopen('/root/.flexget/removed.csv', 'w');
			
		    foreach ($removed as $item)
    		{
    			if ($item != $show)
    			fwrite($fp, $item . "\n");
			}
			
			fclose($fp);
			
			return "Success";

		}
		catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function startDeluge() {
		
		exec('sudo -u dan /usr/bin/pkill deluge-web > /dev/null 2>&1');
		
		$delugeWeb = exec('ps ax | grep -v grep | grep deluge-web');
		
		while($delugeWeb) {
			$delugeWeb = exec('ps ax | grep -v grep | grep deluge-web');
		}
		
		$running = exec('ps ax | grep -v grep | grep deluged');
					
		if($running) {
			shell_exec('sudo -u dan /usr/bin/deluge-web > /dev/null 2>/dev/null &');
		}
		else {
			shell_exec('sudo -u dan /usr/bin/deluged > /dev/null 2>&1');
			shell_exec('sudo -u dan /usr/bin/deluge-web > /dev/null 2>/dev/null &');
		}

		
		$delugeWeb = exec('ps ax | grep -v grep | grep deluge-web');
		$count = 0;
		while(strpos($delugeWeb, "Sl") === FALSE  || $count == 20) {
			$delugeWeb = exec('ps ax | grep -v grep | grep deluge-web');
			
			$count = $count++;
		}

		if ($count == 20) {
			return "fail";	
		}
		
		$running = exec('ps ax | grep -v grep | grep deluged');
		
		if($running) {
			return "started";
		}
		else {
			return "fail";
		}
				
	}
}
?>
