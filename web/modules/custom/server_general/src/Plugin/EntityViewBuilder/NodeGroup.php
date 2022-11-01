<?php

namespace Drupal\server_general\Plugin\EntityViewBuilder;

use Drupal\intl_date\IntlDate;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\server_general\EntityDateTrait;
use Drupal\server_general\EntityViewBuilder\NodeViewBuilderAbstract;
use Drupal\server_general\LineSeparatorTrait;
use Drupal\server_general\SocialShareTrait;
use Drupal\server_general\TitleAndLabelsTrait;
use Drupal\Core\Link;
use \Drupal\Core\Url;
use Drupal\og\OgMembershipInterface;


/**
 * The "Node Group" plugin.
 *
 * @EntityViewBuilder(
 *   id = "node.group",
 *   label = @Translation("Node - Group"),
 *   description = "Node view builder for Group bundle."
 * )
 */
class NodeGroup extends NodeViewBuilderAbstract {

  use EntityDateTrait;
  use LineSeparatorTrait;
  use SocialShareTrait;
  use TitleAndLabelsTrait;

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, NodeInterface $entity) {
    $elements = [];

    // Header.
    $element = $this->buildHeader($entity);
    $elements[] = $this->wrapContainerWide($element);

    // Main content and sidebar.
    $element = $this->buildMainAndSidebar($entity);
    $elements[] = $this->wrapContainerWide($element);

    $elements = $this->wrapContainerVerticalSpacingBig($elements);
    $build[] = $this->wrapContainerBottomPadding($elements);

    return $build;
  }

  /**
   * Build the header.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array
   *
   * @throws \IntlException
   */
  protected function buildHeader(NodeInterface $entity): array {
    $elements = [];

    $elements[] = $this->buildConditionalPageTitle($entity);

    // Show the node type as a label.
    $node_type = NodeType::load($entity->bundle());
    $elements[] = $this->buildLabelsFromText([$node_type->label()]);

    $current_user = \Drupal::currentUser();
    //$logged_in = \Drupal::currentUser()->isAuthenticated();

    if ($current_user->isAuthenticated()) {
      $account = \Drupal\user\Entity\User::load($current_user->id());; // pass your uid
      $name = $account->get('name')->value;

      $parameters = [
          'entity_type_id' => $entity->getEntityTypeId(),
          'group' => $entity->id(),
          'og_membership_type' => OgMembershipInterface::TYPE_DEFAULT,
        ];

      $url = Url::fromRoute('og.subscribe', $parameters)->toString();
      $link = \Drupal\Core\Link::fromTextAndUrl('here', \Drupal\Core\Url::fromUri('internal:'.$url))->toString();

      $elements[] = [
        '#type' => 'markup',
        '#markup' => t('Hi @user, click @link if you would like to subscribe to this group called @groupname', array('@user'=> $name, '@groupname' => $entity->label(), '@link' => $link)),
      ];

     /* $link['title'] = $this->t("Hi @user, click here if you would like to subscribe to this group called @groupname", ['@user'=> $name, '@groupname' => $entity->label()]);
      $link['class'] = ['subscribe', 'request'];
      $link['url'] = $url;

      $elements[] = [
        '#type' => 'link',
        '#title' => $link['title'],
        '#url' => $link['url'],
      ];*/

    }


    // Date.
    $timestamp = $this->getFieldOrCreatedTimestamp($entity, 'field_publish_date');
    $element = IntlDate::formatPattern($timestamp, 'long');
    // Make text bigger.
    $elements[] = $this->wrapTextDecorations($element, FALSE, FALSE, 'lg');

    $elements = $this->wrapContainerVerticalSpacing($elements);
    return $this->wrapContainerNarrow($elements);
  }

  /**
   * Build the Main content and the sidebar.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array
   *
   * @throws \IntlException
   */
  protected function buildMainAndSidebar(NodeInterface $entity): array {
    $main_elements = [];
    $sidebar_elements = [];
    $social_share_elements = [];

    $medias = $entity->get('field_group_image')->referencedEntities();
    $main_elements[] = $this->buildEntities($medias);

    
    // Get the body text, wrap it with `prose` so it's styled.
    $main_elements[] = $this->buildProcessedText($entity, 'body');

    $main_elements[] = $entity->get('og_group')->view();

    // Get the tags, and social share.
    $sidebar_elements[] = $this->buildTags($entity);

    // Add a line separator above the social share buttons.
    $social_share_elements[] = $this->buildLineSeparator();
    $social_share_elements[] = $this->buildSocialShare($entity);

    $sidebar_elements[] = $this->wrapContainerVerticalSpacing($social_share_elements);

    return [
      '#theme' => 'server_theme_main_and_sidebar',
      '#main' => $this->wrapContainerVerticalSpacingBig($main_elements),
      '#sidebar' => $this->wrapContainerVerticalSpacingBig($sidebar_elements),
    ];

  }

  /**
   * Build Teaser view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildTeaser(array $build, NodeInterface $entity) {
    $media = $this->getReferencedEntityFromField($entity, 'field_group_image');
    $timestamp = $this->getFieldOrCreatedTimestamp($entity, 'field_publish_date');

    $element = [
      '#theme' => 'server_theme_card',
      '#title' => $entity->label(),
      '#image' => $media instanceof MediaInterface ? $this->buildImageStyle($media, 'card', 'field_media_image') : NULL,
      '#date' => IntlDate::formatPattern($timestamp, 'long'),
      '#url' => $entity->toUrl(),
    ];
    $build[] = $element;

    return $build;
  }

}
