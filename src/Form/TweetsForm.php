<?php

/**
 * @file
 * Contains Drupal\signalfire_twitter\Form\TweetsForm.
 */

namespace Drupal\signalfire_twitter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TweetsForm.
 *
 * @package Drupal\signalfire_twitter\Form
 */
class TweetsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'signalfire_twitter.tweets',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tweets_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load the configuration so can be used in default value fields
    $config = $this->config('signalfire_twitter.tweets');
    // Create the consumer key (required by twitter apps)
    $form['consumer_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Key'),
      '#description' => $this->t('Consumer Key from apps.twitter.com'),
      '#default_value' => $config->get('consumer_key')
    );
    // Create the consumer secret (required by twitter apps)
    $form['consumer_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Secret'),
      '#description' => $this->t('Consumer Secret from apps.twitter.com'),
      '#default_value' => $config->get('consumer_secret')
    ); 
    // Create the access token (required by twitter apps)    
    $form['access_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#description' => $this->t('Access token from apps.twitter.com'),
      '#default_value' => $config->get('access_token')
    ); 
    // Create the access token secret (required by twitter apps)    
    $form['access_token_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Access Token Secret'),
      '#description' => $this->t('Access token secret from apps.twitter.com'),
      '#default_value' => $config->get('access_token_secret')      
    ); 
    // Indicate how many tweets from the time line of the user to return
    $form['tweet_count'] = array(
      '#type' => 'select',
      '#title' => $this->t('Number of tweets'),
      '#description' => $this->t('The number of tweets to display from a timeline'),
      '#options' => [
        1 => '1 Tweet',
        2 => '2 Tweets',
        3 => '3 Tweets',
        4 => '4 Tweets',
        5 => '5 Tweets',
        6 => '6 Tweets',
        7 => '7 Tweets',
        8 => '8 Tweets',
        9 => '9 Tweets',
        10 => '10 Tweets'
      ],
      '#default_value' => $config->get('tweet_count')
    );
    // Indicate the screen name of the user to get tweets from
    $form['screen_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Screen Name'),
      '#description' => $this->t('Screen name from twitter excluding @'),
      '#default_value' => $config->get('screen_name')
    );
    // Build the form
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('signalfire_twitter.tweets')
      ->set('consumer_key', $form_state->getValue('consumer_key'))
      ->set('consumer_secret', $form_state->getValue('consumer_secret'))
      ->set('access_token', $form_state->getValue('access_token'))
      ->set('access_token_secret', $form_state->getValue('access_token_secret'))
      ->set('tweet_count', $form_state->getValue('tweet_count'))
      ->set('screen_name', $form_state->getValue('screen_name'))
      ->save();
  }

}
