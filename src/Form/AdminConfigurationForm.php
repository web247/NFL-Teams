<?php

namespace Drupal\nfl_teams\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure nfl_teams.
 */
class AdminConfigurationForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'nfl_teams.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nfl_teams_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Key'),
      '#description' => $this->t('Api Key used to get data from the endpoint.'),
      '#default_value' => $config->get('api_key'),
    ];

    $form['rows_per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Rows per page'),
      '#description' => $this->t('The count of rows per page.'),
      '#default_value' => $config->get('rows_per_page'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->configFactory->getEditable(static::SETTINGS)
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('rows_per_page', $form_state->getValue('rows_per_page'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
