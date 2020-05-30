<?php

namespace Drupal\nfl_teams\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Pager\PagerManager;
use Drupal\Core\Pager\PagerParameters;
use Drupal\Core\Utility\TableSort;
use Drupal\nfl_teams\NflTeamsClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines NflTeamsController class.
 */
class NflTeamsController extends ControllerBase {

  /**
   * The client.
   *
   * @var Drupal\nfl_teams\NflTeamsClient
   */
  protected $nflTeamsClient;


  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;


  /**
   * Pager Manager.
   *
   * @var Drupal\Core\Pager\PagerManager
   */
  protected $pagerManager;

  /**
   * Pager Params service.
   *
   * @var Drupal\Core\Pager\PagerParameters
   */
  protected $pagerParam;

  /**
   * Module configuration.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructor for NflTeams class.
   *
   * @param \Drupal\nfl_teams\NflTeamsClient $nfl_teams_client
   *   The nfl service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request.
   * @param \Drupal\Core\Pager\PagerManager $page_manager
   *   Pager Manager.
   * @param Drupal\Core\Pager\PagerParameters $pager_params
   *   Pager parameters.
   */
  public function __construct(
    NflTeamsClient $nfl_teams_client,
    RequestStack $request_stack,
    PagerManager $page_manager,
    PagerParameters $pager_params
  ) {
    $this->nflTeamsClient = $nfl_teams_client;
    $this->request = $request_stack->getCurrentRequest();
    $this->pagerManager = $page_manager;
    $this->pagerParam = $pager_params;
    $this->config = $this->nflTeamsClient->getConfig();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nfl_teams_client'),
      $container->get('request_stack'),
      $container->get('pager.manager'),
      $container->get('pager.parameters')
    );
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {

    $teams = $this->nflTeamsClient->teamsList();

    // Build the table header.
    $header = [
      ['data' => $this->t('Name'), 'field' => 'name'],
      ['data' => $this->t('Nickname'), 'field' => 'nickname'],
      ['data' => $this->t('Display Name'), 'field' => 'display_name'],
      ['data' => $this->t('ID'), 'field' => 'id'],
      ['data' => $this->t('Conference'), 'field' => 'conference'],
      ['data' => $this->t('Division'), 'field' => 'division', 'sort' => 'asc'],
    ];

    $rows = [];

    $totalRows = count($teams);

    $rowsPerPage = $this->config->get('rows_per_page') ?: 10;

    // Create pager.
    $this->pagerManager->createPager($totalRows, $rowsPerPage);

    // Sort items.
    $rows = $this->sortItems($teams, $header);

    // Paginate items.
    $rows = $this->itemsPager($rows, $rowsPerPage);

    // The table description.
    $build = [
      '#markup' => $this->t('List of the NFL Teams'),
    ];

    // Generate the table.
    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Split array for pager.
   *
   * @param array $items
   *   Items which need split.
   * @param int $num_page
   *   How many items view in page.
   *
   * @return array
   *   Paged items.
   */
  public function itemsPager(array $items, int $num_page) {
    // Get the number of the current page.
    $currentPage = $this->pagerParam->findPage();
    // Split an array into chunks.
    $chunks = array_chunk($items, $num_page);
    // Return current group item.
    $currentPageItems = $chunks[$currentPage];

    return $currentPageItems;
  }

  /**
   * Custom table sort.
   *
   * @param array $rows
   *   Table rows which need sortable.
   * @param array $header
   *   Table header.
   * @param int $flag
   *   Sort flag.
   *
   * @return array
   *   The sorterd array.
   * @see http://php.net/manual/ru/function.sort.php
   */
  public function sortItems(array $rows, array $header, int $flag = SORT_STRING | SORT_FLAG_CASE) {

    $order = TableSort::getOrder($header, $this->request);
    $sort = TableSort::getSort($header, $this->request);

    $column = $order['sql'];
    foreach ($rows as $row) {
      $temp_array[] = $row[$column];
    }
    if ($sort == 'asc') {
      asort($temp_array, $flag);
    }
    else {
      arsort($temp_array, $flag);
    }

    foreach ($temp_array as $index => $data) {
      $new_rows[] = $rows[$index];
    }

    return $new_rows;
  }

}
