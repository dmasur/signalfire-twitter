<?php

/**
 * @file
 * Contains \Drupal\signalfire_twitter\Plugin\Block\TweetsBlock.
 */

namespace Drupal\signalfire_twitter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Provides a 'TweetsBlock' block.
 *
 * @Block(
 *  id = "tweets_block",
 *  admin_label = @Translation("Tweets block"),
 * )
 */
class TweetsBlock extends BlockBase {

  private function validate_config($config){

    // Create a variable used to indicate how many
    // of the required keys have been found
    $keys_found = 0;

    // Create an array containing the identifier of the
    // array element required
    $required_keys = [
      'consumer_key',
      'consumer_secret',
      'access_token',
      'access_token_secret',
      'tweet_count',
      'screen_name'
    ];

    // Iterate the required keys and get the configuration value
    // from Drupal. If the configuration value is found then icrement
    // the counter
    foreach($required_keys as $key){
      $value = $config->get($key);
      if (isset($value)){
        $keys_found++;
      }
    }

    // Check the number of keys found === the number of keys required
    // If the same then return true, else false
    return $keys_found === count($required_keys);

  }

  private function convert_atsigns($tweet){
    return preg_replace("/@([\S]*)/", "<a href=\"http://www.twitter.com/$1\">@$1</a>", $tweet);
  }

  private function convert_hashtags($tweet){
    return preg_replace("/#([\S]*)/", "<a target=\"_new\" href=\"http://twitter.com/search?q=$1\">#$1</a>", $tweet);
  }

  private function convert_links($tweet){
    return preg_replace("/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/", "<a target=\"_blank\" href=\"$1\">$1</a>", $tweet);
  }

  private function format_tweet_with_links($tweet){

    // Convert any links in the text to hyperlinks
    $tweet = $this->convert_links($tweet);

    // Convert any #tags in the tweet text to hyperlinks
    $tweet = $this->convert_hashtags($tweet);

    // Convert any @signs in the tweet text to hyperlinks
    $tweet = $this->convert_atsigns($tweet);

    // Return the formatted text
    return $tweet;
  }

  private function cache_id(){

    // Return a unique id for the caching
    return 'signalfire_twitter_tweets';

  }

  private function cache_expire(){
    return time() + 600;
  }

  private function load_tweets($config){

    // Create array for tweets
    $tweets = [];

    // See if the tweets are already cached
    if ($cache = \Drupal::cache()->get($this->cache_id())){

      // Set the tweets array to contain the cached data
      $tweets = $cache->data;

    }else{

      // Attempt to fetch tweets
      try{

        // Tweets not cached so connect to twitter
        $connection = new TwitterOAuth(
          $config->get('consumer_key'),
          $config->get('consumer_secret'),
          $config->get('access_token'),
          $config->get('access_token_secret')
        );

        // Get the tweets for the user
        $tweets = $connection->get(
          'statuses/user_timeline',
          array(
            'count' => $config->get('tweet_count'),
            'screen_name' => $config->get('screen_name'),
            'exclude_replies' => true
          )
        );

        // Add the retrieved tweets to the cache and expire after
        // the timestamp (set as 10 minutes currently)
        \Drupal::cache()->set($this->cache_id(), $tweets, $this->cache_expire());

      }catch (Exception $ex){

        // Do something
        drupal_set_message(t('An error occurred, was unable to fetch tweets'), 'error');

      }

    }

    // Return the tweets
    return $tweets;

  }

  private function tweet_markup(){

    // Get the configuration set in the admin
    $config = \Drupal::config('signalfire_twitter.tweets');

    // If config is not null and has the fields required
    if ($config && $this->validate_config($config)){

      // Load tweets either from cache or live
      $tweets = $this->load_tweets($config);

      // Declare an elements array, setting its theme to item_list
      // and the type of item_list as ul
      $elements = [];
      $elements['#theme'] = 'item_list';
      $elements['#type'] = 'ul';

      // Check if there are any tweets to display
      if (count($tweets) > 0){
        $date_formatter = \Drupal::service('date.formatter');

        // There are tweets so iterate over then
        foreach($tweets as $tweet){

          // Create an item in the array for the tweet, formatting the
          // links etc in the tweet and setting a class of tweet on the li
          $elements['#items'][] = [
            '#markup' => sprintf(
              '<span class="text">%s</span><span class="date">%s</span>',
              $this->format_tweet_with_links($tweet->text),
              $date_formatter->format(strtotime($tweet->created_at))
            ),
            '#wrapper_attributes' => ['class'=>'tweet']
          ];

        }

      }else{

        // There are no tweets in the $tweet array so display message and add
        // no-tweets class
        $elements['#items'][] = [
          '#markup'=>'No tweets found',
          '#wrapper_attributes' => ['class'=>'no-tweets']
        ];

      }
    }else{

      // Something is wrong with the configuration, say this and add a class
      $elements['#items'][] = [
        '#markup'=>'Configuration Incorrect',
        '#wrapper_attributes' => ['class'=>'config-error']
      ];

    }

    // Return the $elements drupal render array
    return drupal_render($elements);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache'] = ['max-age'=>0];
    $build['tweets_block']['#markup'] = $this->tweet_markup();
    return $build;
  }

}
