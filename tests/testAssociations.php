<?php

require_once 'autoload.php';

class TestAssociations extends TipyTestSuite {

    public function testHasMany() {
        $this->createPostsWithComments(5, 10);
        $this->assertEqual(TipyTestBlogPost::count(), 5);
        $this->assertEqual(TipyTestBlogComment::count(), 50);

        // Get last post
        $post = TipyTestBlogPost::findFirst([
            "order" => "created_at desc"
        ]);
        $this->assertEqual($post->title, 'Post 5');

        // Test has many collection
        $queryCount =  TipyDAO::$queryCount;
        $comments = $post->comments;
        // We got a new query
        $this->assertEqual(TipyDAO::$queryCount, $queryCount+1);
        $this->assertEqual(sizeof($comments), 10);
        $this->assertEqual($comments[0]->title, 'Comment 1 to post 5');

        // Test cached association
        $this->assertNotEqual($post->associationsCache["comments"], null);
        $this->assertEqual($comments, $post->associationsCache["comments"]);

        // Test no query
        $queryCount =  TipyDAO::$queryCount;
        $commentsAgain = $post->comments;
        // no more queries
        $this->assertEqual(TipyDAO::$queryCount, $queryCount);
        $this->assertEqual($comments, $commentsAgain);

        // Test has many collection with conditions
        $comments = $post->comments([
            "order" => " created_at desc"
        ]);
        $this->assertEqual(sizeof($comments), 10);
        $this->assertEqual($comments[0]->title, 'Comment 10 to post 5');
        // Test queries association is not cached
        $this->assertNotEqual($comments, $post->associationsCache["comments"]);
    }

    public function testBelongTo() {
        $this->createPostsWithComments(5, 10);
        $this->assertEqual(TipyTestBlogPost::count(), 5);
        $this->assertEqual(TipyTestBlogComment::count(), 50);
        $comment = TipyTestBlogComment::findFirst([
            'conditions' => 'title = ?',
            'values' => ['Comment 4 to Post 3']
        ]);
        $this->assertEqual($comment->message, 'This is a comment message 3:4!');

        // Test belongsTo collection
        $queryCount =  TipyDAO::$queryCount;
        $post = $comment->post;
        // We got a new query
        $this->assertEqual(TipyDAO::$queryCount, $queryCount+1);

        $this->assertNotEqual($post, null);
        $this->assertEqual($post->title, 'Post 3');

        // Test cached association
        $this->assertNotEqual($comment->associationsCache["post"], null);
        $this->assertEqual($post, $comment->associationsCache["post"]);

        // Test no query
        $queryCount =  TipyDAO::$queryCount;
        $postAgain = $comment->post;
        // no more queries
        $this->assertEqual(TipyDAO::$queryCount, $queryCount);
        $this->assertEqual($post, $postAgain);
    }

    public function testHasManyThrough() {
        $this->createUsersWithGroups(10, 5);
        $this->assertEqual(TipyTestUser::count(), 10);
        $this->assertEqual(TipyTestGroup::count(), 5);
        $this->assertEqual(TipyTestUserAndGroupRelation::count(), 14);
        $user = TipyTestUser::load(1);
        $this->assertEqual($user->login, 'login_1');

        // Test has_many_through collection
        $queryCount =  TipyDAO::$queryCount;
        $groups = $user->groups;
        // We got a 2 new querys
        $this->assertEqual(TipyDAO::$queryCount, $queryCount + 2);

        $this->assertNotEqual($groups, null);
        $this->assertEqual(sizeof($groups), 5);
        $this->assertEqual($groups[0]->name, 'name_1');
        // Test cached association
        $this->assertNotEqual($user->associationsCache["groups"], null);
        $this->assertEqual($groups, $user->associationsCache["groups"]);

        // Test no query
        $queryCount =  TipyDAO::$queryCount;
        $groupsAgain = $user->groups;
        // no more queries
        $this->assertEqual(TipyDAO::$queryCount, $queryCount);
        $this->assertEqual($groups, $groupsAgain);

        // Test has many collection with conditions
        $groups = $user->groups([
            "order" => " name desc"
        ]);
        $this->assertEqual(sizeof($groups), 5);
        $this->assertEqual($groups[0]->name, 'name_5');
        // Test queries association is not cached
        $this->assertNotEqual($groups, $user->associationsCache["groups"]);

    }

    public function testHasOne() {
        $this->createUsersWithProfiles(10);
        $this->assertEqual(TipyTestUser::count(), 10);
        $this->assertEqual(TipyTestProfile::count(), 10);

        // Get last user
        $user = TipyTestUser::findFirst([
            "order" => "id desc"
        ]);
        $this->assertEqual($user->login, 'login_10');

        // Test has many collection
        $queryCount =  TipyDAO::$queryCount;
        $profile = $user->profile;
        // We got a new query
        $this->assertEqual(TipyDAO::$queryCount, $queryCount+1);
        $this->assertEqual($profile->sign, 'signature of user 20'); // Auto_INCREMENT doesn't rollback
        $this->assertEqual($profile->userId, $user->id);
        // Test cached association
        $this->assertNotEqual($user->associationsCache["profile"], null);
        $this->assertEqual($profile, $user->associationsCache["profile"]);

        // Test no query
        $queryCount =  TipyDAO::$queryCount;
        $profileAgain = $user->profile;
        // no more queries
        $this->assertEqual(TipyDAO::$queryCount, $queryCount);
        $this->assertEqual($profile, $profileAgain);

    }

    // methods that have names not starting whith 'test' are for seeding DB
    public function createPostsWithComments($postsCount, $commentsCount) {
        $userId = 2;
        for ($i=1; $i<=$postsCount; $i++) {
            $post = TipyTestBlogPost::create([
                'userId' => $userId,
                'title' => "Post $i",
                'message' => "This is a message $i!",
                'createdAt' => time() + $i
            ]);
            for ($j=1; $j<=$commentsCount; $j++) {
                $comment = TipyTestBlogComment::create([
                    'userId' => $userId,
                    'blogPostId' => $post->id,
                    'title' => "Comment $j to post $i",
                    'message' => "This is a comment message $i:$j!",
                    'createdAt' => time()+$j
                ]);
            }
        }
    }

    // methods that have names not starting whith 'test' are for seeding DB
    public function createUsersWithGroups($usersCount, $groupsCount) {
        // user with id =1 in all groups
        // group with id = 1 contains all users

        for ($i=1; $i<=$usersCount; $i++) {
            $user = TipyTestUser::create([
                'login' => 'login_'.$i,
                'password' => 'password_'.$i,
                'email' => 'email_'.$i.'@example.com'
            ]);

            $relation = TipyTestUserAndGroupRelation::create([
                'userId' => $user->id,
                'groupId' => 1
            ]);

        }
        for ($i=1; $i<=$groupsCount; $i++) {
            $group = TipyTestGroup::create([
                'name' => 'name_'.$i
            ]);
            if ($i > 1) { // group with id = 1 alredy contains user with id = 1
                $relation = TipyTestUserAndGroupRelation::create([
                    'userId' => 1,
                    'groupId' => $group->id
                ]);
            }

        }
    }

    // methods that have names not starting whith 'test' are for seeding DB
    public function createUsersWithProfiles($count) {
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

        }
    }
}
