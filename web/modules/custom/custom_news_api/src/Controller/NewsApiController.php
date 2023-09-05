<?php
namespace Drupal\custom_news_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;

class NewsApiController extends ControllerBase {
  public function listBlogs(Request $request) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'news')
      ->accessCheck(FALSE);
    $nids= $query->execute();  
      
    $nodes = Node::loadMultiple($nids);
    $data = [];
    foreach ($nodes as $node) {

      $image_url = [];
      $image_field = $node->get('field_images');
      foreach ($image_field->getValue() as $item) {
        $file_id = $item['target_id'];
        $file = \Drupal\file\Entity\File::load($file_id);
        if ($file && $file->getFileUri()) {
          $file_url = \Drupal::service('file_url_generator')->generate($file->getFileUri());
          $image_urls = $file_url->toString();
          $image_url[] = $image_urls;
        }
      }
      $data[] = [
        'title' => $node->getTitle(),
        'body' => $node->get('body')->value,
        'published_date' => $node->getCreatedTime(),
        'author' => $node->getOwner()->getDisplayName(),
        'images' => $image_url,
        'tag' => $node->field_news_tags->entity->getName(),
      ];
    }
    return new JsonResponse($data);
  }
}  


