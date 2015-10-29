<?php
add_action('wp_enqueue_scripts', 'fts_pinterest_head');
function  fts_pinterest_head() {
    wp_enqueue_style( 'fts_pinterest_css', plugins_url( 'pinterest/css/styles.css',  dirname(__FILE__) ) );
}

include_once('fts_simple_html_dom.php');

class FTS_Pinterest{
	private $pinterestURL = "";
	private $localFileURL = "";
	private $pageHTML = "";
	private $user = "";
	private $pinboardImages = array();
	private $_links = array();
	private $_covers = array();
	private $_thumbs = array(); #An array of arrays.
	public $_fullboards = array();

	/**
	* Get the users pinboard data from Pinterest & store in class variables.
	*/
	public function scrapeUser($user){
		$this->user = $user;
		$this->pinterestURL = "http://www.pinterest.com/" . $this->user . "/";
		$this->localFileURL = WP_CONTENT_DIR.'/plugins/feed-them-premium/feeds/pinterest/cache/pinterest-cache-'.$this->user.'.html';
		$html = $this->loadHTML();
	}

	/**
	* Pulls the user's data from Pinterest if there is no locally stored copy available or if the local file is over 1hr old.
	*/
	private function loadHTML(){		
		date_default_timezone_set('UTC');

		if(!file_exists($this->localFileURL)){
			$this->updatePinterestFile();
		}
		else if (strtotime("-15 minutes") >= filemtime($this->localFileURL)) {
			//If the file is over an hour old, pull a new copy from Pinterest
	        		$this->updatePinterestFile();
		}else{
			//Else, load content from locally cached file.
			$this->pageHTML = file_get_contents($this->localFileURL);
		}

		$this->parseHTML();
	}

	private function updatePinterestFile(){
		
  		$this->pageHTML = wp_remote_fopen($this->pinterestURL);
  		file_put_contents($this->localFileURL, $this->pageHTML);
	}

	private function parseHTML(){
		$html = new fts_simple_html_dom();
		$html->load($this->pageHTML);

		# retrieve all of the pinboards
		$pinBoards = $html->find(".Board");
		$count = 0;
		
		foreach($pinBoards as $board) {
			#Loads the Board Title
			foreach ($board->find(".boardName") as $board_title ) {
				    
					$final_board_title = $board_title->plaintext;
					$final_board_title = trim($final_board_title);
					
		    		$this->_fullboards[$count]['board_title'] = $final_board_title;
		    }
			#Loads the Links
			foreach ($board->find("a") as $link ) {
		    		$this->_links[] = "http://www.pinterest.com" . $link->href;
					$this->_fullboards[$count]['link'] = "http://www.pinterest.com" . $link->href;
		    	}

		    #Loads the cover shots
			foreach ($board->find(".boardCoverWrapper img") as $cover ) {
					$this->_covers[] = $cover->src;
		    		$this->_fullboards[$count]['cover'] = $cover->src;
		    }
			
			#Loads the Pin Count
			foreach ($board->find(".boardCoverWrapper .boardPinCount") as $pincount ) {
					$final_pin_count = $pincount->innertext;
					$final_pin_count = trim($final_pin_count);
		    		$this->_fullboards[$count]['pin_count'] = $final_pin_count;
		    }

			#Loads the thumbnails
			$tempThumbs = array();
			$tempThumbsCount = 0;
			foreach ($board->find(".boardThumbs img") as $thumbs) {
		    		$tempThumbs[] = $thumbs->src;
					$this->_fullboards[$count]['thumbs'][$tempThumbsCount] = $thumbs->src;
		    	$tempThumbsCount++;
			}
		    $this->_thumbs[] = $tempThumbs;
				
		$count++;
		}
		
	}

	public function getCovers(){
		return $this->_covers;
	}

	public function getThumbs(){
		return $this->_thumbs;
	}

	public function getLinks(){
		return $this->_links;
	}
	public function getFullBoards(){
		return $this->_fullboards;
	}
}

add_shortcode( 'fts pinterest', 'fts_pinterest_board_feed' );

function fts_pinterest_board_feed($atts)	{
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	extract( shortcode_atts( array(
		'pinterest_name' => '',
		'boards_count' => '1',
	), $atts ) );
	
	ob_start();
	
	if (!empty($pinterest_name)){
		$pinterest = new FTS_Pinterest; 
		$pinterest->scrapeUser($pinterest_name);
		$pinterestFullBoards = $pinterest->getFullBoards();
	$i= 1;

		 echo '<div class="clear"></div>';
		 echo '<div class="fts-pinterest-boards-wrap">';
		foreach ($pinterestFullBoards as $board)	{
				if($i <= $boards_count) {
					   echo '<a class="fts-pin-board-wrap" href="'.$board['link'].'" target="_blank">';
					   
							echo '<h3 class="fts-pin-board-board_title">'.$board['board_title'].'</h3>';
					  
						  echo '<div class="fts-pin-board-img-wrap"><span class="hoverMask"></span>';
							  echo '<img class="fts-pin-board-cover" src="'.$board['cover'].'"/>';
							  echo '<div class="fts-pin-board-pin-count">'.$board['pin_count'].'</div>';
						  echo '</div>';
						   
					
						echo '<div class="fts-pin-board-thumbs-wrap">';
						//check if there are thumbs.
						if($board['thumbs']){
						  foreach ($board['thumbs'] as $key => $value)	{
							echo '<div class="pinterest-single-thumb-wrap"><span class="hoverMask"></span><img class="fts-pin-board-thumb" src="'.$value.'"/></div>';
						  }
						}
						echo '</div>';
							  
					  echo '</a>';
				 }
		 $i++;		 
		}
		 echo '<div class="clear"></div>';
		 echo '</div><!--/fts-pinterest-boards-wrap-->';
		 	}
	
	else	{
		echo 'Please give a valid Pinterest UserName';
	}
	return ob_get_clean();
}
?>