SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET GLOBAL time_zone = "+03:00";

-- pass: 123 for default users

DROP TABLE IF EXISTS `comment`;
DROP TABLE IF EXISTS `like_dislike`;
DROP TABLE IF EXISTS `post`;
DROP TABLE IF EXISTS `friendRequest`;
DROP TABLE IF EXISTS `friendRemovalNotification`;
DROP TABLE IF EXISTS `friendship`;
DROP TABLE IF EXISTS `user`;


CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci UNIQUE NOT NULL,
  `password` varchar(100) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `surname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `profilePic` varchar(100) DEFAULT NULL,
  `birthdate` date,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `post` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `userId` int(11) NOT NULL,
   `creationDate` datetime,
   `fupload` varchar(100) DEFAULT NULL,
   `content` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`userId`) REFERENCES user(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `comment` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `postId` int(11) NOT NULL,
   `userId` int(11) NOT NULL,
   `content` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
   `creationDate` datetime,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`postId`) REFERENCES `post`(`id`),
   FOREIGN KEY (`userId`) REFERENCES `user`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `like_dislike` (
   `id` INT AUTO_INCREMENT ,
   `userId` INT NOT NULL,
   `postId` INT NOT NULL,
   `type` ENUM('like', 'dislike') NOT NULL,
   `created_at` datetime,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`userId`) REFERENCES `user`(`id`),
   FOREIGN KEY (`postId`) REFERENCES `post`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `friendRequest` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `senderId` int(11) NOT NULL,
   `receiverId` int(11) NOT NULL,
   `creationDate` datetime,
   `status` enum('pending', 'accepted', 'ignored')   NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`senderId`) REFERENCES user(`id`),
   FOREIGN KEY (`receiverId`) REFERENCES user(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `friendship` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `user1` int(11) NOT NULL,
   `user2` int(11) NOT NULL,
   `created_at` datetime,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`user1`) REFERENCES user(`id`),
   FOREIGN KEY (`user2`) REFERENCES user(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `friendRemovalNotification` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `removerUserId` int(11) NOT NULL,
   `removedUserId` int(11) NOT NULL,
   `creationDate` datetime,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`removerUserId`) REFERENCES `user`(`id`),
   FOREIGN KEY (`removedUserId`) REFERENCES `user`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- pass: 123 for default users
INSERT INTO `user` (`email`, `password`, `name`, `surname`, `profilePic`, `birthdate`)
VALUES
   ('cemal@gmail.com', '$2y$10$97tbRWbAjOmmpVDkeL9aGu8HuNsdQvR8pijc0Yb/xXDIOqk6Yk7we', 'Cemal Fırat', 'Dağ', NULL, '1990-01-01'),
   ('bora@gmail.com', '$2y$10$97tbRWbAjOmmpVDkeL9aGu8HuNsdQvR8pijc0Yb/xXDIOqk6Yk7we', 'Bora', 'Çelikcioğlu', NULL, '1991-02-02'),
   ('batuhan@gmail.com', '$2y$10$97tbRWbAjOmmpVDkeL9aGu8HuNsdQvR8pijc0Yb/xXDIOqk6Yk7we', 'Batuhan', 'Duras', NULL, '1992-03-03');
COMMIT;









