<?php

namespace Drupal\custom_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for managing custom form route.
 */
class CustomFormController extends ControllerBase {

  /**
   * Callback for rendering the custom route.
   */
  public function customRoute() {
    // Query all student nodes.
    $query = \Drupal::entityTypeManager()->getStorage('node')
      ->getQuery()
      ->condition('type', 'student')
      ->sort('created', 'DESC');
    $nids = $query->execute();

    // Initialize table rows array.
    $rows = [];

    // Load each node and extract data.
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      $id = $node->get('field_id')->value;
      $name = $node->getTitle();
      $email = $node->get('field_emailll')->value;
      $department = $node->get('field_depatment')->value;

      // Render the image.
      $image = '';
      if ($node->hasField('field_picture') && !$node->get('field_picture')->isEmpty()) {
        $image_render_array = $node->get('field_picture')->view('default');
        $image_render_array[0]['#item_attributes']['style'] = 'width: 100px;';
        $image = \Drupal::service('renderer')->renderPlain($image_render_array);
      } else {
        $image = 'Picture not added';
      }

      // Add data to table rows array.
      $rows[] = [
        'ID' => $id,
        'Name' => $name,
        'Email' => $email,
        'Department' => $department,
        'Picture' => $image,
        'Actions' => [
          'data' => [
            '#type' => 'container',
            'edit' => [
              '#type' => 'link',
              '#title' => $this->t('Edit'),
              '#url' => Url::fromRoute('custom_form.edit_form', ['node' => $nid]),
            ],
            'delete' => [
              '#type' => 'link',
              '#title' => $this->t('Delete'),
              '#url' => Url::fromRoute('entity.node.delete_form', ['node' => $nid]),
            ],
          ],
        ],
      ];
    }

    // Build the table.
    $build = [
      '#theme' => 'table',
      '#header' => [
        $this->t('ID'),
        $this->t('Name'),
        $this->t('Email'),
        $this->t('Department'),
        $this->t('Picture'),
        $this->t('Actions'),
      ],
      '#rows' => $rows,
    ];

    return $build;
  }

  /**
   * Challback for rendering the edit form.
   */
  public function editForm($node) {
    // Load the node.
    $node = Node::load($node);
  
    // Ensure te node exists.
    if (!$node) {
      // Display a message and return a 404 Not Found response.
      drupal_set_message($this->t('The requested node does not exist.'), 'error');
      throw new NotFoundHttpException();
    }
  
    // Ensure the user has access to edit the node.
    if ($node->access('update')) {
      // Build and return the form.
      return \Drupal::formBuilder()->getForm('Drupal\custom_form\Form\CustomFormEdit', $node);
    }
    else {
      // Display an access denied message and return a 403 Forbidden response.
      drupal_set_message($this->t('You are not authorized to edit this node.'), 'error');
      return new Response('', 403);
    }
  }
}