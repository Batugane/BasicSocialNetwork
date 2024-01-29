<?php
session_start();

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

require 'userDb.php';
require 'csrf.php';
$user = $_SESSION["user"];

// ADD POST
if (isset($_POST["btnPost"])) {

    if (csrf_check()) {
        extract($_POST);
        $currentDate = date("Y-m-d H:i:s");

        $postContent = filter_var($postContent, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        require "Upload.php";
        $img = new Upload("imageUpload", "postImages");

        $stmt = $db->prepare("INSERT INTO post (userId, creationDate, fupload, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user["id"], $currentDate, $img->filename, $postContent]);
    } else {
        echo "CSRF Error ";
        exit;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="main.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/4ed1987bc2.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

</head>

<body>
    <div class="fixed-header">
        <div class="header-content">
            <div class="user-info">
                <?php
                if (empty($user["profilePic"])) {
                    echo "<img src='images/default.jpg'>";
                } else {
                    echo "<img
            src='images/{$user["profilePic"]}'
          />";
                } ?>
                <span class="username">
                    <?= $user["name"] . " " . $user["surname"] ?></span>
            </div>
            <div class="searchContainer">
                <div class="searchbar">
                    <input type="text" autocomplete="off" placeholder="Search..." class="form-control" id="livesearch" size="40" />
                </div>
                <div id="searchresult"></div>
            </div>

            <div class="logout">
                <a href="logout.php" class="logout-button">Logout</a>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="postContainer">
            <div class="newPost">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" method="post" enctype="multipart/form-data" id="createPost">
                    <textarea class="postContent" name="postContent" rows="4" cols="50" placeholder="Write your thoughts :)"></textarea>
                    <div class="bottomBar">
                        <input type="hidden" name="csrf_token" value="<?= create_csrf_token() ?>">

                        <input type="file" name="imageUpload" class="fileUpload" accept="image/png, image/gif, image/jpeg" />
                        <input type="submit" name="btnPost" class="btn btn-primary submitBtn" value="Post" />
                    </div>
                </form>
            </div>

            <div class="dispPost"></div>
        </div>
        <div class="social">
            <h2 class="text-center ">Notifications</h2>
            <div class="userList" id="notifications"></div>
            <h2 class="text-center ">Friends List</h2>
            <div class="userList" id="friends"></div>
        </div>
    </div>
</body>


</html>

<script>
    updateNotifications();
    updateFriendsList();
    fetchPosts();


    setInterval(updateNotifications, 2000);
    setInterval(updateFriendsList, 2000);
    setInterval(fetchPosts, 60000);


    $(document).ready(function() {

        //HIDE LIVE SEARCH BAR WHEN SOMEWHERE ELSE'S CLICKED
        $(document).click(function(event) {
            var target = $(event.target);
            if (!target.is("#searchresult") && !target.closest("#searchresult").length) {
                $("#searchresult").hide();
            }
        });


        $(document).on('click', '.commentBtn', function() {
            var postId = $(this).closest('.post').attr('class').split(' ')[1].split('_')[1];
            var input = $(this).siblings('input[type="text"]').val();
            var csrf_token = $('input[name="csrf_token"]').val();

            $.ajax({
                url: 'postComment.php',
                method: 'POST',
                data: {
                    postId: postId,
                    input: input,
                    csrf_token: csrf_token
                },
                success: function(response) {
                    fetchPosts()
                }
            });

            $(this).siblings('input[type="text"]').val('');
        });


        $(document).on('click', '.countData', function(event) {
            event.preventDefault()
            var targetElement = $(this)
            var postId = $(this).closest('.post').attr('class').split(' ')[1].split('_')[1];
            var type = $(this).children('span').attr('class');
            var csrf_token = $('input[name="csrf_token"]').val();

            action = '';
            sibling = $(this).siblings().first()
            if (type == 'likeCount') {
                if ($(this).hasClass('alreadyLiked')) {
                    action = 'removeLike'
                } else {
                    if (sibling.hasClass('alreadyDisliked')) {
                        // UPDATE DISLIKE TO LIKE
                        action = 'updateLike'
                    } else {
                        action = 'like'
                    }
                }

            } else {
                if ($(this).hasClass('alreadyDisliked')) {
                    action = 'removeDislike'
                } else {
                    if (sibling.hasClass('alreadyLiked')) {
                        // UPDATE LIKE TO DISLIKE
                        action = 'updateDislike'
                    } else {
                        action = 'dislike'
                    }
                }
            }


            $.ajax({
                url: 'handleLikeDislike.php',
                method: 'POST',
                data: {
                    postId: postId,
                    action: action,
                    csrf_token: csrf_token
                },
                success: function(response) {
                    var res = JSON.parse(response)
                    // console.log(res)
                    targetElement.closest('.post').find('.likeCount').text(res.likeCount)
                    targetElement.closest('.post').find('.dislikeCount').text(res.dislikeCount)
                    console.log(action)
                    switch (action) {
                        case 'like':
                            //like
                            targetElement.closest('.post').find('.likeCount').siblings().removeClass('far').addClass('fas')
                            targetElement.closest('.post').find('.likeCount').parent('.countData').addClass('alreadyLiked')
                            break;
                        case 'dislike':
                            //dislike
                            targetElement.closest('.post').find('.dislikeCount').siblings().removeClass('far').addClass('fas')
                            targetElement.closest('.post').find('.dislikeCount').parent('.countData').addClass('alreadyDisliked')
                            break;
                        case 'removeLike':
                            //remove like
                            targetElement.closest('.post').find('.likeCount').siblings().removeClass('fas').addClass('far')
                            targetElement.closest('.post').find('.likeCount').parent('.countData').removeClass('alreadyLiked')
                            break;
                        case 'removeDislike':
                            //remove dislike
                            targetElement.closest('.post').find('.dislikeCount').siblings().removeClass('fas').addClass('far')
                            targetElement.closest('.post').find('.dislikeCount').parent('.countData').removeClass('alreadyDisliked')
                            break;
                        case 'updateLike':
                            //remove dislike
                            targetElement.closest('.post').find('.dislikeCount').siblings().removeClass('fas').addClass('far')
                            targetElement.closest('.post').find('.dislikeCount').parent('.countData').removeClass('alreadyDisliked')
                            //like
                            targetElement.closest('.post').find('.likeCount').siblings().removeClass('far').addClass('fas')
                            targetElement.closest('.post').find('.likeCount').parent('.countData').addClass('alreadyLiked')
                            break;
                        case 'updateDislike':
                            //remove like
                            targetElement.closest('.post').find('.likeCount').siblings().removeClass('fas').addClass('far')
                            targetElement.closest('.post').find('.likeCount').parent('.countData').removeClass('alreadyLiked')
                            //dislike
                            targetElement.closest('.post').find('.dislikeCount').siblings().removeClass('far').addClass('fas')
                            targetElement.closest('.post').find('.dislikeCount').parent('.countData').addClass('alreadyDisliked')
                            break;
                    }

                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);

                }
            });
        });


        //FETCH FRIENDS LIST
        function updateFriendsList() {
            $.ajax({
                url: 'getFriends.php',
                method: 'GET',
                success: function(response) {

                    $('#friends').empty();

                    data = JSON.parse(response)
                    for (var i = 0; i < data.length; i++) {
                        var friend = data[i];
                        $('#friends').append(`<div class="userCard">
                        <div class="userDetails ">
                        <img src="images/${friend.profilePic || 'default.jpg'}" alt="">
                                                <span>${friend.name} ${friend.surname}</span>

                        </div>
                        <div class="controls">
                            <span class="removeBtn" data-targetid="${friend.id}">Unfriend</span>
                        </div>
                    </div> `);
                    }

                }
            });
        }



        //LIVESEARCH
        $("#livesearch").keyup(function() {
            var input = $(this).val();
            var csrf_token = $('input[name="csrf_token"]').val();

            if (input != "") {
                $.ajax({
                    url: "livesearch.php",
                    method: "POST",
                    data: {
                        input: input,
                        csrf_token: csrf_token
                    },
                    success: function(data) {

                        $("#searchresult").html(data);
                        $("#searchresult").css("display", "block");

                    }
                });
            } else {
                $("#searchresult").css("display", "none");
            }
        });

        //ACCEPT FRIEND REQUEST
        $(document).on('click', '.acceptBtn', function() {
            var input = $(this).data('targetid');
            var csrf_token = $('input[name="csrf_token"]').val();

            $.ajax({
                url: 'acceptFriendRequest.php',
                method: 'POST',
                data: {
                    input: input,
                    csrf_token: csrf_token
                },
                success: function(response) {
                    updateNotifications();
                    updateFriendsList();
                    fetchPosts();
                }
            });
        });

        //IGNORE FRIEND REQUEST
        $(document).on('click', '.ignoreBtn', function() {
            var input = $(this).data('targetid');
            var csrf_token = $('input[name="csrf_token"]').val();

            $.ajax({
                url: 'ignoreFriendRequest.php',
                method: 'POST',
                data: {
                    input: input,
                    csrf_token: csrf_token
                },
                success: function(response) {
                    updateNotifications();
                    updateFriendsList();
                }
            });
        });

        // REMOVE FRIEND
        $(document).on('click', '.removeBtn', function(event) {
            var input = $(this).data('targetid');
            var csrf_token = $('input[name="csrf_token"]').val();


            $.ajax({
                url: 'removeFriend.php',
                method: 'POST',
                data: {
                    input: input,
                    csrf_token: csrf_token
                },
                success: function(response) {
                    updateFriendsList();
                    fetchPosts();
                }
            });
        });



    });





    //FETCH REQUESTS
    function updateNotifications() {
        $.ajax({
            url: 'fetchNotifications.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var notificationsDiv = $('#notifications');
                notificationsDiv.empty();

                for (var i = 0; i < response.length; i++) {
                    var notification = response[i];
                    console.log(notification)
                    var creationDate = formatDate(notification.creationDate)
                    if (notification.type === 'friendRequest') {
                        // Render friend request notification
                        notificationsDiv.append(
                            `<div class="userCard">
              <div class="userDetails">
                <img src="images/${notification.sender.profilePic || 'default.jpg'}" alt="">
                <span>${notification.sender.name} ${notification.sender.surname} wants to be your friend!</span>
                <div class="controls">
              <span class="acceptBtn" data-targetid="${notification.sender.id}">Accept</span>
              <span class="ignoreBtn" data-targetid="${notification.sender.id}">Ignore</span>
              </div>
    <span class="dateInfo">${creationDate}</span>
    </div>
  </div>`
                        );
                    } else if (notification.type === 'friendRemoval') {
                        notificationsDiv.append(
                            `<div class="userCard">
              <div class="userDetails">
                <img src="images/${notification.remover.profilePic || 'default.jpg'}" alt="">
                <span>${notification.remover.name} ${notification.remover.surname} has removed you from their friends list!</span>
                 <span class="dateInfo">${creationDate}</span>
              </div>
              
            </div>`
                        );
                    }
                }
            }
        });
    }

    //FETCH FRIENDS LIST
    function updateFriendsList() {
        $.ajax({
            url: 'getFriends.php',
            method: 'GET',
            success: function(response) {

                $('#friends').empty();

                data = JSON.parse(response)
                for (var i = 0; i < data.length; i++) {
                    var friend = data[i];
                    $('#friends').append(`<div class="userCard">
                        <div class="userDetails ">
                        <img src="images/${friend.profilePic || 'default.jpg'}" alt="">
                                                <span>${friend.name} ${friend.surname}</span>

                        </div>
                        <div class="controls">
                            <span class="removeBtn" data-targetid="${friend.id}">Unfriend</span>
                        </div>
                    </div> `);
                }

            }
        });
    }

    function fetchPosts() {
        $.ajax({
            url: 'fetchPosts.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Handle the response
                $(".dispPost").empty();
                for (var i = 0; i < data.length; i++) {
                    var post = data[i];
                    var creationDate = formatDate(post.creationDate)
                    $('.dispPost').append(
                        `<div class="post postid_${post.id}">
                        <div class="userDetails postOwner">
                        <img src="images/${post.ownerProfilePic || 'default.jpg'}" alt="">
                        <span>${post.ownerName} ${post.ownerSurname} </span>
                        <span class="dateInfo">${creationDate}</span>
                    </div>
                    <div class="imagePost">
                        <img src="${post.fupload ? 'postImages/' + post.fupload :''}" alt=""/>
                    </div>
                    <div class="textPost">
                        ${post.content}
                    </div>
                    <div class="postInfo">
                        <div class="socialData">
                            <div class="countData ">
                                <span class="likeCount">${post.likeCount}</span>
                                <i class="${post.alreadyLiked == 1 ? 'fas' : 'far'} fa-thumbs-up"></i>
                            </div>
                            <div class="countData">
                                <span class="dislikeCount">${post.dislikeCount}</span>
                                <i class="${post.alreadyDisliked == 1 ? 'fas' : 'far'} fa-thumbs-down"></i>
                            </div>
                        </div>
                        <div class="commentData">
                            <span class="commentCount"></span> Comments
                        </div>
                    </div>
                    <div class="comments">
                        <div class="addComment">

                            <input type="text" />
                            <span class="commentBtn">Comment</span>
                        </div>
                        <div class="dispComment">
                           
                        </div>
                    </div>
                </div>`);

                    if (post.alreadyLiked == 1) {
                        $('.post.postid_' + post.id + ' .likeCount').parent('.countData').addClass('alreadyLiked');
                    }

                    if (post.alreadyDisliked == 1) {
                        $('.post.postid_' + post.id + ' .dislikeCount').parent('.countData').addClass('alreadyDisliked');
                    }
                    fetchComments(post.id);
                    $("img[src='']").each(function() {
                        $(this).addClass("hidden");
                        $(this).parent(".imagePost").addClass("hidden");
                    });
                }
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error(xhr.responseText);
            }
        });
    }

    function fetchComments(postId) {
        $.ajax({
            url: 'fetchComments.php',
            method: 'GET',
            success: function(response) {

                $('.post.postid_' + postId + ' .dispComment').empty()
                data = JSON.parse(response)
                var filteredComments = $.grep(data, function(element) {
                    return element.postId == postId;
                });
                $('.post.postid_' + postId + ' .commentCount').text(filteredComments.length)
                for (var i = 0; i < filteredComments.length; i++) {
                    var comment = filteredComments[i];
                    var creationDate = formatDate(comment.creationDate)
                    $('.post.postid_' + postId + ' .dispComment')
                        .append(`<div class="commentPost">
                            <div class="userDetails">
                            <img src="images/${comment.profilePic || 'default.jpg'}" alt="">
                                <span>${comment.fullname}</span>
                                <span class="dateInfo">${creationDate}</span>
                            </div>
                            <div class="commentContent">
                               ${comment.content}
                            </div>
                        </div> `);
                }

            }
        });
    }

    function formatDate(creationDate) {

        const now = moment(); // Current time
        const diff = now.diff(creationDate, 'minutes'); // Calculate the difference in minutes

        let timeString;
        if (diff < 60) {
            timeString = `${diff} minutes ago`;
        } else if (diff < 1440) {
            timeString = `${Math.floor(diff / 60)} hours ago`;
        } else {
            timeString = `${Math.floor(diff / 1440)} days ago`;
        }

        return timeString;
    }

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>

</html>