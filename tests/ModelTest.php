<?php

class ModelTest extends TipyTestCase {

    public function testJustForExample() {
        // Just example of assertThrown method
        $this->assertThrown('TipyModelException', "Unable to save deleted model", function () {
            $post = new TipyTestBlogPost;
            $post->userId = 2;
            $post->title = "Hello World!";
            $post->message = "This is a blog post!";

            $this->assertEqual($post->isNewRecord(), true);
            $this->assertEqual($post->createdAt, null);
            $this->assertEqual($post->save(), true);
            $this->assertEqual($post->isNewRecord(), false);
            $this->assertNotEqual($post->createdAt, null);

            $post->delete();
            $post->save();
        });
        
        // This should not raise exceptions
        $post = new TipyTestBlogPost;
        $post->userId = 2;
        $post->title = "Hello World!";
        $post->message = "This is a blog post!";

        $this->assertEqual($post->isNewRecord(), true);
        $this->assertEqual($post->createdAt, null);
        $this->assertEqual($post->save(), true);
        $this->assertEqual($post->isNewRecord(), false);
        $this->assertNotEqual($post->createdAt, null);

        $post->delete();
    }

    public function testCRUD() {
        $post = new TipyTestBlogPost;
        $post->userId = 2;
        $post->title = "Hello World!";
        $post->message = "This is a blog post!";

        $this->assertEqual($post->isNewRecord(), true);
        $this->assertEqual($post->createdAt, null);
        $this->assertEqual($post->save(), true);
        $this->assertEqual($post->isNewRecord(), false);
        $this->assertNotEqual($post->createdAt, null);

        // post got an id after save
        $id = $post->id;

        // Load and update post but to another variable
        $post2 = TipyTestBlogPost::load($id);
        $this->assertEqual($post2->isNewRecord(), false);
        $this->assertNotEqual($post2, null);
        $this->assertEqual($post2->message, "This is a blog post!");

        $post2->message = "This is a new text";
        $post2->createdAt = 1;
        $this->assertEqual($post2->save(), true);

        // Reload first post and check that message was updated
        $post->reload();

        $this->assertEqual($post->message, "This is a new text");
        $this->assertEqual($post->createdAt, 1);

        // Delete post
        $post->delete();

        $this->assertEqual($post->isDeletedRecord, true);
        try {
            $post->save();
        } catch (Exception $e) {
        }
        $this->assertNotEqual($e, null);
        $this->assertEqual(get_class($e), 'TipyModelException');
        $this->assertEqual($e->getMessage(), "Unable to save deleted model");

        try {
            $post->reload();
        } catch (Exception $e) {
        }
        $this->assertNotEqual($e, null);
        $this->assertEqual(get_class($e), 'TipyModelException');
        $this->assertEqual($e->getMessage(), "Unable to reload deleted model");
    }

    public function testDataTypes() {
        $post = TipyTestBlogPost::create([
            'userId' => 2,
            'title' => 'This is a title',
            'message' => 'This is a message!'
        ]);
        $this->assertTrue(is_int($post->id));
        $this->assertTrue(is_int($post->userId));
        $this->assertTrue(is_int($post->createdAt));
        $this->assertNull($post->updatedAt);
        $this->assertTrue(is_string($post->title));
        $this->assertTrue(is_string($post->message));

        $post->reload();

        $this->assertTrue(is_int($post->id));
        $this->assertTrue(is_int($post->userId));
        $this->assertTrue(is_int($post->createdAt));
        $this->assertNull($post->updatedAt);
        $this->assertTrue(is_string($post->title));
        $this->assertTrue(is_string($post->message));
    }

    public function testTypeCastNulls() {
        $post = TipyTestBlogPost::create([
            'userId' => 2,
            'title' => 'This is a title',
            'message' => 'This is a message!'
        ]);
        $comment = TipyTestBlogComment::create([
            'userId'     => 0,
            'blogPostId' => $post->id,
            'title'      => '',
            'message'    => null
        ]);
        $comment->reload();
        $this->assertTrue(is_int($comment->id));
        $this->assertTrue(is_int($comment->userId));
        $this->assertIdentical($comment->userId, 0);
        $this->assertTrue(is_string($comment->title));
        $this->assertIdentical($comment->title, '');
        $this->assertNull($comment->message);
    }

    public function testReloadNewRecord() {
        $post = new TipyTestBlogPost;
        try {
            $post->reload();
        } catch (Exception $e) {
        }
        $this->assertNotEqual($e, null);
        $this->assertEqual(get_class($e), 'TipyModelException');
        $this->assertEqual($e->getMessage(), "Unable to reload unsaved model");
    }

    public function testNewWithAttributes() {
        $post = new TipyTestBlogPost([
            'userId' => 2,
            'title' => 'This is a title',
            'message' => 'This is a message!'
        ]);
        $this->assertEqual($post->isNewRecord(), true);
        $this->assertEqual($post->userId, 2);
        $this->assertEqual($post->title, "This is a title");
        $this->assertEqual($post->message, "This is a message!");
    }

    public function testCreate() {
        $post = TipyTestBlogPost::create([
            'userId' => 2,
            'title' => 'This is a title',
            'message' => 'This is a message!'
        ]);
        $this->assertEqual($post->isNewRecord(), false);
        $this->assertEqual($post->userId, 2);
        $this->assertEqual($post->title, "This is a title");
        $this->assertEqual($post->message, "This is a message!");
        $this->assertEqual(TipyTestBlogPost::count(), 1);
    }

    public function testFind() {
        TipyTestBlogPost::create([
            'userId' => 2,
            'title' => 'This is a title',
            'message' => 'This is a message!'
        ]);
        TipyTestBlogPost::create([
            'userId' => 2,
            'title' => 'This is another title',
            'message' => 'This is another  message!'
        ]);

        // Test find all
        $result = TipyTestBlogPost::find();
        $this->assertEqual(sizeof($result), 2);
        $post = $result[0];
        $this->assertEqual($post->title, 'This is a title');
        $post = $result[1];
        $this->assertEqual($post->title, 'This is another title');

        for ($i=1; $i<=10; $i++) {
            TipyTestBlogPost::create([
                'userId' => $i,
                'title' => "Title $i",
                'message' => "This is a message $i!"
            ]);
        }
        $count = TipyTestBlogPost::count();
        $this->assertEqual($count, 12);

        // Test find by condition
        $result = TipyTestBlogPost::find([
            "conditions" => "user_id >=?",
            "values" => [7]
        ]);
        $this->assertEqual(sizeof($result), 4);

        // Test order
        $result = TipyTestBlogPost::find([
            "conditions" => "user_id >=?",
            "values" => [7],
            "order" => "title desc"
        ]);
        $this->assertEqual(sizeof($result), 4);
        $this->assertEqual($result[0]->title, "Title 9");
        $this->assertEqual($result[1]->title, "Title 8");
        $this->assertEqual($result[2]->title, "Title 7");
        $this->assertEqual($result[3]->title, "Title 10");

        // Test limit
        $result = TipyTestBlogPost::find([
            "conditions" => "user_id >=?",
            "values" => [7],
            "order" => "title desc",
            "limit" => 2
        ]);
        $this->assertEqual(sizeof($result), 2);
        $this->assertEqual($result[0]->title, "Title 9");
        $this->assertEqual($result[1]->title, "Title 8");

        // Test offset
        $result = TipyTestBlogPost::find([
            "conditions" => "user_id >=?",
            "values" => [7],
            "order" => "title desc",
            "limit" => 2,
            "offset" => 1
        ]);
        $this->assertEqual(sizeof($result), 2);
        $this->assertEqual($result[0]->title, "Title 8");
        $this->assertEqual($result[1]->title, "Title 7");
    }

    public function testAttributes() {
        $post = new TipyTestBlogPost();
        try {
             $post->unknown = "Bang!";
        } catch (TipyModelException $e) {
        }
        $this->assertNotEqual($e, null);
        $this->assertEqual($e->getMessage(), "Unknown property 'unknown' for TipyTestBlogPost");
    }

    public function testValidationOnCreate() {
        $this->assertThrown('TipyValidationException', "Post should belongs to user", function () {
            $post = TipyTestBlogPost::create([
                'title' => 'This is a title',
                'message' => 'This is a message!'
            ]);
            $this->assertNotEqual($post, null);
            $this->assertEqual($post->isNewRecord(), true);
            $this->assertEqual($post->id, null);
        });
    }

    public function testValidation() {
        $this->assertThrown('TipyValidationException', "Post should belongs to user", function () {
            $post = new TipyTestBlogPost;
            $post->title = 'This is a title';
            $post->message = 'This is a message!';
            $post->save();
            $this->assertEqual($post->isNewRecord(), true);
            $this->assertEqual($post->id, null);

            $post->userId = 2;
            $this->assertEqual($post->save(), true);
            $this->assertEqual($post->isNewRecord(), false);
            $this->assertNotEqual($post->id, null);
        });
    }

    public function testDependent() {
        $this->createUsersWithAsocs(5);
        $this->assertEqual(TipyTestUser::count(), 5);
        $this->assertEqual(TipyTestProfile::count(), 5);
        $this->assertEqual(TipyTestUserAndGroupRelation::count(), 25);
        $this->assertEqual(TipyTestBlogPost::count(), 25);
        $this->assertEqual(TipyTestBlogComment::count(), 125);

        $user = TipyTestUser::findFirst();
        $user->delete();
        $this->assertEqual(TipyTestUser::count(), 4);
        $this->assertEqual(TipyTestProfile::count(), 5);
        $this->assertEqual(TipyTestUserAndGroupRelation::count(), 20);
        $this->assertEqual(TipyTestBlogPost::count(), 20);
        $this->assertEqual(TipyTestBlogComment::count(), 100);
    }

    public function testForeignKeys() {
        $this->createUsersWithFriends(10);
        $this->assertEqual(TipyTestUser::count(), 10);
        $this->assertEqual(TipyTestFriend::count(), 45);

        // Get last user
        $user = TipyTestUser::findFirst([
            "order" => "id desc"
        ]);
        // Check assocs
        $this->assertEqual($user->login, 'login_10');
        $this->assertEqual(sizeof($user->friends), 9);
        // Test delete
        $user->delete();
        $this->assertEqual(TipyTestUser::count(), 9);
        $this->assertEqual(TipyTestFriend::count(), 36);
    }

    public function testTransactions() {
        $this->createUsersWithFriends(10);
        $this->assertEqual(TipyTestUser::count(), 10);
        TipyModel::transaction(function() {
            $this->createUsersWithFriends(10);
            $user = TipyTestUser::findFirst();
            $user->lockForUpdate();
            $this->assertEqual(TipyTestUser::count(), 20);
            $this->assertEqual(TipyTestFriend::count(), 90);
            TipyModel::rollback();
        });
        $this->assertEqual(TipyTestUser::count(), 10);
        $this->assertEqual(TipyTestFriend::count(), 45);
        TipyModel::transaction(function() {
            $this->createUsersWithFriends(10);
            $this->assertEqual(TipyTestUser::count(), 20);
            $this->assertEqual(TipyTestFriend::count(), 90);
        });
        $this->assertEqual(TipyTestUser::count(), 20);
        $this->assertEqual(TipyTestFriend::count(), 90);
    }

    public function testCreatedUpdatedAt() {
        $user = TipyTestUser::create([
            'login' => 'some login',
            'password' => 'some password',
            'email' => 'some_email@example.com'
        ]);
        $time1 = time();
        $post = TipyTestBlogPost::create([
                'userId' => $user->id,
                'title' => "Post",
                'message' => "This is a message!",
        ]);
        $time2 = time();
        $createdAt = $post->createdAt;
        $this->assertTrue($post->createdAt >= $time1 && $post->createdAt <= $time2);
        $this->assertNull($post->updatedAt);
        $post->title = 'New title';
        $post->save();
        $time3 = time();
        $this->assertTrue($post->updatedAt >= $time2 && $post->updatedAt <= $time3);
        $this->assertEqual($post->createdAt, $createdAt);
    }

    public function testUnknownAttribute() {
        $this->assertThrown('TipyModelException', "Unknown property 'name' for TipyTestUser", function () {
            $user = TipyTestUser::create([
                'name' => 'James Bond',
                'login' => 'some login',
                'password' => 'some password',
                'email' => 'some_email@example.com'
            ]);
        });
    }

    // methods that have names not starting whith 'test' are for seeding DB
    public function createUsersWithAsocs($count) {
        for ($i=1; $i<=$count; $i++) {
            $user = TipyTestUser::create([
                'login' => 'login_'.$i,
                'password' => 'password_'.$i,
                'email' => 'email_'.$i.'@example.com'
            ]);
            $profile = TipyTestProfile::create([
                'userId' => $user->id,
                'sign' => 'signature of user '.$user->id
            ]);
            for ($j=1; $j<=$count; $j++) {
                $relation = TipyTestUserAndGroupRelation::create([
                    'userId' => $user->id,
                    'groupId' => $j
                ]);
                $post = TipyTestBlogPost::create([
                'userId' => $user->id,
                'title' => "Post $i",
                'message' => "This is a message $i!",
                'createdAt' => time() + $i
                ]);
                for ($k=1; $k<=$count; $k++) {
                    $comment = TipyTestBlogComment::create([
                        'userId' => $user->id,
                        'blogPostId' => $post->id,
                        'title' => "Comment $j to post $i",
                        'message' => "This is a comment message $i:$j!",
                        'createdAt' => time()+$j
                    ]);
                }
            }
        }
    }

    // methods that have names not starting whith 'test' are for seeding DB
    public function createUsersWithFriends($count) {
        $ids = [];
        for ($i=1; $i<=$count; $i++) {
            $user = TipyTestUser::create([
                'login' => 'login_'.$i,
                'password' => 'password_'.$i,
                'email' => 'email_'.$i.'@example.com'
            ]);
            $ids[$i] = $user->id;
            for ($j = 1; $j < $i; $j++) {
                $friend = TipyTestFriend::create([
                    'personId' => $user->id,
                    'friendId' => $ids[$j]
                ]);
            }
        }
    }
}