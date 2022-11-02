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
   
    $this->drupalGet($this->group->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    
    $this->assertSession()->pageTextContains(t('if you would like to subscribe to this group called'));
    $this->clickLink('here');
    $this->assertSession()->addressEquals('group/'. $this->group->toUrl() .'/subscribe');
    
  }
}
