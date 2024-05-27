<?php

namespace Drupal\custom_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a form for editing node data.
 */
class CustomFormEdit extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_form_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    // Store the node object in the form state for use in submit handler.
    $form_state->set('node', $node);

    // Add form elements here based on the fields you want to edit.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $node->get('field_name')->value,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $node->get('field_emailll')->value, // Corrected field name
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#default_value' => $node->get('field_depatment')->value, // Corrected field name
      '#required' => TRUE,
    ];

    $form['picture'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Picture'),
      '#upload_location' => 'public://images/',
      '#required' => TRUE,
      '#default_value' => [$node->get('field_picture')->target_id], // Corrected field value to be an array
    ];

    // Add a submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add validation logic here if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the node from the form state.
    $node = $form_state->get('node');
    if (!$node) {
      \Drupal::messenger()->addError($this->t('Node not found.'));
      return;
    }

    // Save the form data.
    $values = $form_state->getValues();
    $node->setTitle($values['name']);
    $node->set('field_emailll', $values['email']);
    $node->set('field_depatment', $values['department']);

    if (!empty($values['picture'])) {
      $file = \Drupal\file\Entity\File::load($values['picture'][0]);
      if ($file) {
        $node->set('field_picture', [
          'target_id' => $file->id(),
          'alt' => $file->getFilename(),
        ]);
      }
    } else {
      // Clear existing picture if empty.
      $node->set('field_picture', NULL);
    }

    $node->save();

    // Optionally, you can redirect the user after saving.
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
  }

}
