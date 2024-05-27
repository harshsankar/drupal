<?php

namespace Drupal\custom_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Provides a custom form to create a student node.
 */
class CustomForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ID'),
      '#required' => TRUE,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
    ];

    $form['picture'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Picture'),
      '#upload_location' => 'public://images/',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Create a new node of type 'student'
    $node = Node::create(['type' => 'student']);

    // Set the node's fields
    $node->setTitle($values['name']);
    $node->set('field_id', $values['id']);
    $node->set('field_emailll', $values['email']);
    $node->set('field_depatment', $values['department']);

    // Check if picture is provided
    if (!empty($values['picture'])) {
      $file = File::load($values['picture'][0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $node->set('field_picture', [
          'target_id' => $file->id(),
          'alt' => $file->getFilename(),
        ]);
      }
    }

    // Save the new node
    $node->save();

    // Provide a message to the user
    \Drupal::messenger()->addMessage($this->t('New student node created successfully.'));
  
  }
}
