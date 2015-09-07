CREATE TABLE `tipy_test_records` (
    `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
    `value` VARCHAR( 20 ) NULL,
    PRIMARY KEY ( `id` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_users` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
`login` VARCHAR( 20 ) NULL ,
`password` VARCHAR( 20 ) NULL ,
`email` VARCHAR( 60 ) NULL,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_blog_posts` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
`user_id` BIGINT( 20 ) NOT NULL,
`created_at` BIGINT( 20 ) NULL,
`updated_at` BIGINT( 20 ) NULL,
`title` VARCHAR( 255 ) NULL ,
`message` text,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_blog_comments` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
`blog_post_id` BIGINT( 20 ) NOT NULL,
`user_id` BIGINT( 20 ) NOT NULL,
`created_at` BIGINT( 20 ) NULL,
`title` VARCHAR( 255 ) NULL ,
`message` text,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_groups` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 20 ) NULL ,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_user_and_group_relations` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
`user_id` BIGINT( 20 ) NOT NULL,
`group_id` BIGINT( 20 ) NOT NULL,
PRIMARY KEY ( `id` ),
UNIQUE KEY `user_group` (`user_id`,`group_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_profiles` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
`user_id` BIGINT( 20 ) NULL,
`sign` VARCHAR( 40 ) NULL ,
`created_at` BIGINT( 20 ) NULL,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tipy_test_friends` (
`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
`person_id` BIGINT( 20 ) NOT NULL,
`friend_id` BIGINT( 20 ) NOT NULL,
PRIMARY KEY ( `id` ),
UNIQUE KEY `friend` (`person_id`,`friend_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
