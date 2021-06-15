<?php

declare(strict_types=1);

/*
 * This file is part of the Ferienpass package.
 *
 * (c) Richard Henkenjohann <richard@ferienpass.online>
 *
 * For more information visit the project website <https://ferienpass.online>
 * or the documentation under <https://docs.ferienpass.online>.
 */

namespace Ferienpass\CoreBundle\Tests\Controller\Fragment;

use Contao\TestCase\FunctionalTestCase;
use Zenstruck\Foundry\Test\Factories;

class ApplicationFormTest extends FunctionalTestCase
{
    use Factories;

    public function testSendApplication(): void
    {
        // 1. "Arrange"
        $post = PostFactory::new() // New Post factory
        ->published()          // Make the post in a "published" state
        ->create([             // Instantiate Post object and persist
            'slug' => 'post-a', // This test only requires the slug field - all other fields are random data
        ])
        ;

        // 1a. "Pre-Assertions"
        $this->assertCount(0, $post->getComments());

        // 2. "Act"
        static::ensureKernelShutdown(); // creating factories boots the kernel; shutdown before creating the client
        $client = static::createClient();
        $client->request('GET', '/posts/post-a'); // Note the slug from the arrange step
        $client->submitForm('Add', [
            'comment[name]' => 'John',
            'comment[body]' => 'My comment',
        ]);

        // 3. "Assert"
        self::assertResponseRedirects('/posts/post-a');

        $this->assertCount(1, $post->refresh()->getComments()); // Refresh $post from the database and call ->getComments()

        CommentFactory::assert()->exists([ // Doctrine repository assertions
            'name' => 'John',
            'body' => 'My comment',
        ]);
    }
}
