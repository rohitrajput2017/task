<?php

namespace Drupal\hello\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class HelloController extends ControllerBase {

  /**
   * A more complex _controller callback that takes arguments.
   *
   * This callback is mapped to the path
   * '/hello/{first}/{second}'.
   *
   * The arguments in brackets are passed to this callback from the page URL.
   * The placeholder names "first" and "second" can have any value but should
   * match the callback method variable names; i.e. $first and $second.
   *
   * This function also demonstrates a more complex render array in the returned
   * values. Instead of rendering the HTML with theme('item_list'), content is
   * left un-rendered, and the theme function name is set using #theme. This
   * content will now be rendered as late as possible, giving more parts of the
   * system a chance to change it if necessary.
   *
   * Consult @link http://drupal.org/node/930760 Render Arrays documentation
   * @endlink for details.
   *
   * @param string $first
   *   A string to use, should be a number.
   * @param string $second
   *   Another string to use, should be a number.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the parameters are invalid.
   */
  public function arguments($first, $second) {
    // print_r($first);die;
    $site_config = $this->config('system.site');
    // Make sure you don't trust the URL to be safe! Always check for exploits.
    if ($first != $site_config->get('siteapikey')) {
      // We will just show a standard "access denied" page in this case.
      drupal_set_message($this->t('Siteapikey is not correct.'), 'error');
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    }

    if (is_numeric($second)) {
      $values = \Drupal::entityQuery('node')->condition('nid', $second)->condition('type', 'page')->execute();
      if (empty($values)) {
        // We will just show a standard "access denied" page in this case.
        drupal_set_message($this->t('This is not valid node ID.'), 'error');
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }
      else {
        $json_array = array(
          'data' => array()
        );
        $nodes = Node::loadMultiple($values);
        foreach ($nodes as $node) {
          $json_array['data'][] = array(
            'type' => $node->get('type')->target_id,
            'id' => $node->get('nid')->value,
            'attributes' => array(
              'title' => $node->get('title')->value,
              'content' => $node->get('body')->value,
            ),
          );
        }
        return new JsonResponse($json_array);
      }
    }
    else {
      drupal_set_message($this->t('Please enter valid node ID.'), 'error');
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    }
  }

}
