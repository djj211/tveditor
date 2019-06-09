<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class ShowsController extends AppController
{	
	public function index()
    {
    	$role = $this->Auth->user('role');
		if ($role == "admin")
		{
			$this->viewBuilder()->layout('admin');
		}
		else if($role == "read")
		{
			$this->viewBuilder()->layout('read');
		}
		
		$this->set('role', $role);
		
		$shows = array();
		$removed = array();
		
		if (($handle = fopen("/root/.flexget/shows.csv", "r")) !== FALSE) {
    		while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
        		$num = count($data);
        		for ($c=0; $c < $num; $c++) {
        			$shows[$data[$c]] = $data[$c];
        		}
    		}
    		fclose($handle);
		}
		
		if (($handle = fopen("/root/.flexget/removed.csv", "r")) !== FALSE) {
    		while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
        		$num = count($data);
        		for ($c=0; $c < $num; $c++) {
        			$removed[$data[$c]] = $data[$c];
        		}
    		}
    		fclose($handle);
		}
				
		$this->set('shows', $shows);
		$this->set('removed', $removed);

	}
	
	public function mainSubmit()
	{
		$this->RequestHandler->renderAs($this,'json');
		if ($this->request->is('ajax')) {
			$role = $this->Auth->user('role');
			$result = "";
			
			if ($role != "read")
			{		
				$shows = array();
				$removed = array();
				
				$shows = json_decode($this->request->data['Active_Shows']);
				$removed = json_decode($this->request->data['Removed_Shows']);
				
				$result = $this->Shows->updateCsvs($shows, $removed);
				
			}
			else {
				$result = "Unauthorized";
			}
			$this->set('text', $result);
			$this->set('_serialize', ['text']);
			
		}
	}
	
	public function TvDbLookup()
	{
		$this->RequestHandler->renderAs($this,'json');
		if ($this->request->is('ajax')) {
			$show = $this->request->data['new_show'];
			$result = $this->Shows->getTvShow($show);
			$html = "";
			$code = "";
			if ($result != "Failure")
			{
				$code = "success";
				if ($result->Series) {
					foreach ($result->Series as $series) {
						 $html .= '<div class="show-row unselected selectDisable" title="' . $series->Overview . '" id="' . $series->seriesid . '">' .
						 				'<div class="tvBlock">' .
						 					'<div><img class="show-banner selectDisable" src="http://thetvdb.com/banners/' . $series->banner . '" alt="' . $series->SeriesName . '"></div>' .
						 			 	'</div>' .
						 			 	'<div id="theShow" class="tvBlock show-name selectDisable">' .
						 			 		$series->SeriesName .
						 			 	'</div>' .
						 			 	'<div id="theNetwork" class="tvBlock show-name selectDisable">' .
						 			 		$series->Network .
						 			 	'</div>' .
						 			 	'<div class="show-description selectDisable">' .
						 					$series->Overview .
						 			 	'</div>' .
						 			 '</div>';
					}
				}
				else {
					$code = "Not Found";
				}
			}
			else {
				$code = "error";
			}

			$this->set(compact('html','code'));
			$this->set('_serialize', array('html', 'code'));
		}
	}

	public function addShow()
	{
		$this->RequestHandler->renderAs($this,'json');
		if ($this->request->is('ajax')) {
			$role = $this->Auth->user('role');
			$result = "";
			
			if ($role != "read")
			{		
				$show = $shows = $this->request->data['show'];
				$season = $this->request->data['season'];
				$episode = $shows = $this->request->data['episode'];
				$showId = $shows = $this->request->data['showId'];
				$cancelNote = "";
	
				$episodesXml = $this->Shows->getEpisode($showId);
				
				if ($episodesXml == "Failure")
				{
					$result = "Failure Getting Epsiode XML";
				}
				else {
						
					$status = $episodesXml->Series[0]->Status;
					
					if (!$episode && !$season)
					{				
						if ($status != "Ended")
						{
							if($episodesXml->Episode) {
								foreach ($episodesXml->Episode as $episode)
								{
									$episodeDate = $episode->FirstAired;
									$date = strtotime($episodeDate);
									if ($date > strtotime('now') || (!$date && $episode->SeasonNumber != 0))
									{
										$season = sprintf("%02d", $episode->SeasonNumber);
										$episode = sprintf("%02d", $episode->EpisodeNumber);
										break;
									}
									else {
										$season = sprintf("%02d", $episode->SeasonNumber + 1);
										$episode = sprintf("%02d", "01");
									}
								}
							}
						}
						else {
							$season = "01";
							$episode = "01";
							$cancelNote = " Note: This Show is Canceled.";
						}
					}
					else {
						if ($status == "Ended")
						{
							$cancelNote = " Note: This Show is Canceled.";
						}
					}
					
					$result = $this->Shows->addToCsv($show, $season, $episode);
					
					if ($result == "Success")
					{
						$flexgetResults = $this->Shows->flexgetBegin($show, $season, $episode);
					
						if (strpos($flexgetResults,'will be accepted starting with') == false)
						{
							$result = "Failure" . $flexgetResults;
						}					
						else {
							$result == "Success";
							$html = "Sucessfully Added " . $show . " Starting at Season " . $season . " Episode " . $episode . "!" . $cancelNote;
						}
					}
				}
			}
			else {
				$result = "Unauthorized";
			}

			$this->set(compact('html','result'));
			$this->set('_serialize', array('html', 'result'));
		}
	}
	
	public function editShow() {
		$this->RequestHandler->renderAs($this,'json');
		
		if ($this->request->is('ajax')) {
			$role = $this->Auth->user('role');
			$show = $shows = $this->request->data['show'];
			$tvDbResult = $this->Shows->getTvShow($show);
			$html;
			$title;
			$returnStart;
			$returnDownload;
			$status;
			$cancledNote = "";
			$showId;
			$episodeDate = "";
			$nextSeason = "";
			$nextEpisode = "";
			$lastEpisode = "";
			$lastSeason = "";
			$lastAired ="";
			$episodeOverView = "";
			$nextDate = "";
			
    		$edits=$this->Shows->find('all');
    		$edits->contain(['LastDownloaded', 'episode_releases']);
			$edits->select(['season' => $edits->func()->max('season'), 'number' => $edits->func()->max('number'), 'LastDownloaded.name', 'series_id']);
			$edits->where(['episode_releases.downloaded' => 1, 'LastDownloaded.name' => $show]);			
			$lstDownload = $edits->first();

			if($lstDownload)
			{
				if($lstDownload->season && $lstDownload->number)
				{
					$returnDownload = "Season: " . $lstDownload->season . " Episode: " . $lstDownload->number;
				}
				else
				{
					$returnDownload = "";
				}
			}
			else
			{
				$returnDownload = "";
			}
			
			$starts=$this->Shows->find('all');
    		$starts->contain(['Starts']);
			$starts->where(['Starts.name' => $show]);
			$start = $starts->first();
			
			if ($start)
			{
				if ($start->season && $start->number)
				{
				$returnStart = "Season: " . $start->season . " Episode: " . $start->number;
				}
				else 
				{
					$returnStart = "";
				}
			}
			else 
			{
				$returnStart = "";
			}
			
			if ($tvDbResult != "Failure")
			{
				$code = "success";
				
				if ($tvDbResult->Series) {
					$showId = $tvDbResult->Series[0]->seriesid;
					$episodesXml = $this->Shows->getEpisode($showId);
					if ($episodesXml->Series) {
						$status = $episodesXml->Series[0]->Status;
						
						if($episodesXml->Episode) {
							foreach ($episodesXml->Episode as $episode)
							{
								$episodeDate = $episode->FirstAired;
								date_default_timezone_set('America/New_York');
								$current = date('Y-m-d', strtotime("today"));
								$date = strtotime($episodeDate);
								
								if (($episodeDate >= $current && $episode->SeasonNumber != "0") || (!$date && $episode->SeasonNumber != "0"))
								{
									$nextSeason = sprintf("%02d", $episode->SeasonNumber);
									$nextEpisode = sprintf("%02d", $episode->EpisodeNumber);
									
									if(date('m/d/Y', $date) != "12/31/1969") {
										$nextDate = " on " . date('m/d/Y', $date);
									}
									
									break;
								}
								else {
									$lastSeason = sprintf("%02d", $episode->SeasonNumber);
									$lastEpisode = sprintf("%02d", $episode->EpisodeNumber);
									$lastAired = date('m/d/Y', $date);
									$episodeOverView = $episode->Overview;
									if ($status != "Ended")
									{
										$nextSeason = sprintf("%02d", $episode->SeasonNumber + 1);
										$nextEpisode = sprintf("%02d", "01");
									}
								}
							}
							
						}
						
						if ($status == "Ended")
						{
							$status = "<span style='color:red'>" . $status . "</span>";
							$cancledNote = "<span style='color:red'>  NOTE: This Show has been Canceled</span>";
						}
						else 
						{
							$status = "<span style='color:green'>" . $status . "</span>";
						}
						
					}
					$seasonField = "";
					$episodeField = "";
					if ($role != "read")
					{
						$seasonField = '<span id="season_span">Season:</span><input type="number" id="edit_season" name="edit_season" class="glowing-border season-ep" placeholder="##" value = "' . $nextSeason . '" />';
						$episodeField = '<span id="episode_span">Episode:</span><input type="number" id="edit_episode" name="edit_episode" class="glowing-border season-ep" placeholder="##" value = "' . $nextEpisode . '" />';
					}
					else 
					{
						$seasonField = '<span id="season_span">Season:</span>' . $nextSeason . '';
						$episodeField = '<span id="episode_span">Episode:</span>' . $nextEpisode;
					}
					
					$html = '<div><img class="selectDisable" src="http://thetvdb.com/banners/' . $tvDbResult->Series[0]->banner . '" alt="' . $tvDbResult->Series[0]->SeriesName . '"></div>' .
							'<div id="expandOverview" class="selectDisable">' .
								'<span id="expand-icon" class="ui-icon ui-icon-squaresmall-plus blue tveditors-icon"></span>Show Overview' . 
							'</div>' .
							'<div id="edit-overview" class="selectDisable">' .
					 			$tvDbResult->Series[0]->Overview .
					 		'</div>' . 
					 		'<div class="selectDisable edit-header">Previously Set Start Value</div>' .
					 		'<div class="selectDisable show-info">' .
					 			$returnStart .
					 		'</div>' . 
					 		'<div class="selectDisable edit-header">Last Downloaded</div>' .
					 		'<div class="selectDisable show-info">' .
					 			$returnDownload .
					 		'</div>' .
					 		'<div class="selectDisable edit-header">Last Aired</div>' .
					 		'<div class="selectDisable show-info">' .
					 			"Season: " . $lastSeason . " Episode: " . $lastEpisode . " on " . $lastAired .
					 			'<div id="expand-episode">' .
									'<span class="ui-icon ui-icon-squaresmall-plus blue tveditors-icon"></span>Overview' . 
								'</div>' .
								'<div id="ep-overview" class="selectDisable">' .
					 				$episodeOverView .
					 			'</div>' .
							'</div>' .
							'<div class="selectDisable edit-header">Status</div>' .
					 		'<div class="selectDisable show-info">' .
					 			$status . 				 	
					 		'</div>' .
							'<div class="selectDisable edit-header">Next Up:</div>' .
					 		'<div class="show-info">' .
					 			'<form name="edit_show" id="edit_show">' .
					 				$seasonField .
					 				$episodeField .
					 				$cancledNote . $nextDate .
					 			'</form>' .
							'</div>';
							
					if ($role != "read")
					{									 		
						$title = "Edit: " . $tvDbResult->Series[0]->SeriesName . " - " . $tvDbResult->Series[0]->Network;
					}
					else {
						$title = "View: " . $tvDbResult->Series[0]->SeriesName . " - " . $tvDbResult->Series[0]->Network;
					}
				}
			
			}
			else {
				$code = "Failure Getting Episode XML";
			}
			
			$this->set(compact('code', 'html','title', 'returnStart', 'returnDownload'));
			$this->set('_serialize', array('code', 'html', 'title', 'returnStart', 'returnDownload'));
		}

	}

	public function setShowStart()
	{
		$this->RequestHandler->renderAs($this,'json');
		if ($this->request->is('ajax')) {
			$show = $shows = $this->request->data['show'];
			$season = $this->request->data['season'];
			$episode = $shows = $this->request->data['episode'];
			
			$role = $this->Auth->user('role');
			$result = "";
			
			if ($role != "read")
			{		
				$flexgetResults = $this->Shows->flexgetBegin($show, $season, $episode);
			
				if (strpos($flexgetResults,'will be accepted starting with') == false)
				{
					$result = "Failure " . $flexgetResults;
				}					
				else {
					$result = "Success";
					$html = "Successfully Updated " . $show . " Start Episode to Season " . $season . " Episode " . $episode . "!";
				}
			}
			else {
				$result = "Unauthorized";
			}
			$this->set(compact('result', 'html'));
			$this->set('_serialize', array('result', 'html'));
		}
			
	}
	
	public function deleteShow()
	{
		$this->RequestHandler->renderAs($this,'json');
		
		if ($this->request->is('ajax')) {
			$result = "";
			$html = "";
			
			$role = $this->Auth->user('role');
			if ($role != "read")
			{
				$show = $shows = $this->request->data['show'];
				
				$flexgetResults = $this->Shows->flexgetDelete($show);
				
				if ($flexgetResults == "Success")
				{
					$result = $this->Shows->csvRemove($show);
					$html = "Successfully Removed Show " . $show . "!";			
				}
				else {
					$result = $flexgetResults . " Error Removing From Flexget";
				}
			}
			else {
				$result = "Unauthorized";
			}
			$this->set(compact('result', 'html'));
			$this->set('_serialize', array('result', 'html'));
		}
		
	}
	
	public function startDeluge() {
		$this->RequestHandler->renderAs($this,'json');
		
		if ($this->request->is('ajax')) {
			$role = $this->Auth->user('role');
			$result = "";
			
			if ($role != "read")
			{
				$result = $this->Shows->startDeluge();
			}
			else {
				$result = "Unauthorized";
			}
			
			$this->set('text', $result);
			$this->set('_serialize', ['text']);			
		}
	}

}
?>
