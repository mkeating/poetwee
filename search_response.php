<?php 


//confirms search terms sent
if(!empty($_GET['q'])){

	//sanitize 
	$search_terms = htmlspecialchars($_GET['q']);
	
	//split the search terms by word
	$words = explode(" ", $search_terms);
	
	$results = array();
	
	//Create OAuth connection
	require 'twt_tokens.php';
	require 'tmhOAuth.php';
	$connection = new tmhOAuth(array(
		'consumer_key' => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'user_token' => $user_token,
		'user_secret' => $user_secret
		));
	
	
	//loop through $words, store 10 responses per word in $results
	foreach ($words as $word)
	{
		$tweets = array();
		
		//run the search thru twitter api
		$http_code = $connection->request('GET', $connection->url('1.1/search/tweets'),
			array('q' => ' '.$word.' ',
					'count' => 30,
					'lang' => 'en',
					'type' => 'recent'));
		
		//search was successful
		if($http_code == 200) {
			
			// Extract the tweets from the response
			$response = json_decode($connection->response['response'], true);
			$tweet_data = $response['statuses'];
			
			//accumulate tweets from search results
			foreach($tweet_data as $tweet){
			
				//ignore retweets
				if(isset($tweet['retweeted_status'])){
					continue;
					}
					
				//ignore t.co links
				if(strpos($tweet['text'], 'https://t.co') !== false){
					continue;
					}
				if(strpos($tweet['text'], 'http://t.co') !== false){
					continue;
					}
				
				$text = $tweet['text'];

				//remove screennames
				$text = preg_replace( '/\@(.*?)\ /','', $text);
				
				//remove hashtags
				$text = preg_replace( '/\#(.*?)\ /','', $text);

				//bolds the search word
				$text = str_ireplace($word, '<b>'.$word.'</b>', $text); 
				
				//Add this tweet's text to the results
				array_push($tweets, $text);
				}
			
			//add this word's results to the total results
			array_push($results, $tweets);
			
		//Handle errors from API request
		}else {
			if($http_code == 429) {
				print 'Error: Twitter API rate limit reached';
			}else{
				print 'Error: Twitter was not able to process that search';
				}
			
		}
	}

	//send back results 
	print json_encode($results);
	
	
}else{
	print 'No search terms found';
}
	
	
?>