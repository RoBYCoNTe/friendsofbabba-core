<?php
declare(strict_types=1);

namespace FriendsOfBabba\Core\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use FriendsOfBabba\Core\Model\Table\MediaTable;

/**
 * FriendsOfBabba\Core\Model\Table\MediaTable Test Case
 */
class MediaTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \FriendsOfBabba\Core\Model\Table\MediaTable
     */
    protected $Media;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.FriendsOfBabba/Core.Media',
        'plugin.FriendsOfBabba/Core.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Media') ? [] : ['className' => MediaTable::class];
        $this->Media = $this->getTableLocator()->get('Media', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Media);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \FriendsOfBabba\Core\Model\Table\MediaTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \FriendsOfBabba\Core\Model\Table\MediaTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
