<?php

namespace Drupal\Tests\server_general\ExistingSite;

use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;
use Drupal\og\Entity\OgRole;
use Drupal\og\Og;
use Drupal\og\OgMembershipInterface;
use Drupal\og\OgRoleInterface;


/**
 * A model test case using traits from Drupal Test Traits.
 */
class ServerGeneralNodeGroupTest extends ExistingSiteBase {

   /**
   * Test entity group.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $group;

  /**
   * The owner of the group.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;


  /**
   * An example test method; note that Drupal API's and Mink are available.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testGroup() {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $this->user = $this->createUser();

    
    // Create a "New Tech" group. Will be automatically cleaned up at end of
    // test.
    $this->group = Node::create([
      'type' => 'group',
      'title' => 'New Tech',
      'uid' => $this->user->id(),
    ]);
    $this->group->save();

    $this->drupalSetCurrentUser($this->user);
    $elements = $this->getElements();
    $this->assertEquals('You are the group manager', $elements[0]['#value']);

    // We can browse pages.
    $this->drupalGet($this->group->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    
    $this->clickLink('here');
    $this->assertSession()->addressEquals('group/'. $this->group->toUrl() .'/subscribe');
    
    $user1 = $this->createUser();
    $this->drupalSetCurrentUser($user1);

    $elements = $this->getElements();
    $this->assertEquals('Request group membership', $elements[0]['#title']);
  }

  /**
   * Helper method; Return the renderable elements from the formatter.
   *
   * @return array
   *   The renderable array.
   */
  protected function getElements() {
    return $this->group->get('og_group')->view();
  }
}
