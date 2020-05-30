<?php

namespace Drupal\nfl_teams;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * NFL Teams Client service.
 */
class NflTeamsClient {

  use StringTranslationTrait;

  // The endpoint base uri.
  const BASE_URI = 'http://delivery.chalk247.com/';

  const LOGGER_CHANNEL = 'nfl_teams';

  /**
   * The http client factory.
   *
   * @var Drupal\Core\Http\ClientFactory
   */
  protected $client;

  /**
   * Config factory.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language Manager.
   *
   * @var Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The cache default service.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * The logger channel factory.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * The api key, from configuration.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * The cache id.
   *
   * @var string
   */
  protected $cid;

  /**
   * NflTeamsClient constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The client factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The config factory.
   * @param Drupal\Core\Cache\CacheBackendInterface $cache_default
   *   The cache.
   * @param Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger channel factory.
   */
  public function __construct(
    ClientFactory $http_client_factory,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    CacheBackendInterface $cache_default,
    LoggerChannelFactoryInterface $logger_channel_factory) {

    $this->client = $http_client_factory->fromOptions([
      'base_uri' => self::BASE_URI,
    ]);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->cacheDefault = $cache_default;
    $this->loggerChannelFactory = $logger_channel_factory;

    $this->loggerChannel = $this->loggerChannelFactory->get(self::LOGGER_CHANNEL);

    $this->apiKey = $this->configFactory->get('nfl_teams.settings')
      ->get('api_key');

    $this->cid = 'nfl_teams:data:' . $this->languageManager
      ->getCurrentLanguage()
      ->getId();

  }

  /**
   * Get the teams list.
   *
   * @return array
   *   Teams list.
   */
  public function teamsList() {

    $data = NULL;

    if ($cache = $this->cacheDefault->get($this->cid)) {
      $this->loggerChannel->log(RfcLogLevel::INFO, $this->t('Data loaded from the cache.'));
      $data = $cache->data;
    }
    else {
      $this->loggerChannel->log(RfcLogLevel::INFO, $this->t('Date loaded from the API feed.'));
      $response = $this->client->get('team_list/NFL.JSON', [
        'query' => [
          'api_key' => $this->apiKey,
        ],
      ]);
      $data = Json::decode($response->getBody());
      $this->cacheDefault->set($this->cid, $data);
    }

    return $data['results']['data']['team'];
  }

  /**
   * Get the api key.
   *
   * @return string
   *   The api key.
   */
  public function getApiKey() {
    return $this->apiKey;
  }

  /**
   * Get Rows per page config.
   *
   * @return Drupal\Core\Config\ImmutableConfig
   *   Module configuration.
   */
  public function getConfig() {
    return $this->configFactory->get('nfl_teams.settings');
  }

  /**
   * Get the cache id.
   *
   * @return string
   *   The cache id.
   */
  public function getCid() {
    return $this->cid;
  }

}
