<?php
include("userDb.php");
session_start();
require 'csrf.php';

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

// Function to check if two users are friends
function checkFriends($id1, $id2)
{
    global $db;

    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM friendship WHERE 
            (user1 = :id1 AND user2 = :id2) OR (user1 = :id2 AND user2 = :id1)");
        $stmt->bindParam(':id1', $id1);
        $stmt->bindParam(':id2', $id2);
        $stmt->execute();

        $result = $stmt->fetchColumn();

        return $result > 0;
    } catch (PDOException $e) {
        // Handle the error response
        echo "Error: " . $e->getMessage();
    }
}


function checkPendingRequest($senderId, $receiverId)
{

    global $db;

    $query = "SELECT COUNT(*) as count FROM friendRequest
              WHERE ((senderId = :senderId AND receiverId = :receiverId)
              OR (senderId = :receiverId AND receiverId = :senderId))
              AND status = 'pending'";

    $stmt = $db->prepare($query);
    $stmt->execute([
        'senderId' => $senderId,
        'receiverId' => $receiverId
    ]);

    $result = $stmt->fetch();
    $count = $result['count'];

    return $count > 0;
}


if (isset($_POST['input'])) {

    if (csrf_check()) {
        $input = $_POST['input'];
        $input = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $stmt = $db->prepare("SELECT * FROM user WHERE (name LIKE :input OR surname LIKE :input OR email LIKE :input) AND id != :user_id");
        $stmt->execute(array(':input' => '%' . $input . '%', ':user_id' => $user['id']));

        $result = $stmt->fetchAll();
        $rowCount = count($result);

        if ($rowCount > 0) {
?>

            <table class="table table-bordered table-striped mt-4">
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?php echo $row['name'] . " " . $row['surname'] ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                            <?php
                            $isFriend = checkFriends($user['id'], $row['id']);
                            $isPendingRequest = checkPendingRequest($user['id'], $row['id']);

                            if ($isFriend) {
                                echo "Already Friends";
                            } else {

                                if ($isPendingRequest) {
                                    echo "Pending Request";
                                } else {
                                    echo "<span class='addFriend' data-id='" . $row['id'] . "'>Add Friend</span>";
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('.addFriend').on('click', function() {
                        var button = $(this);
                        if (!button.hasClass('requestSent')) {
                            button.addClass('requestSent');

                            var receiverId = button.data('id');

                            $.ajax({
                                url: 'handleFriendRequest.php',
                                method: 'POST',
                                data: {
                                    receiverId: receiverId
                                },
                                success: function(data) {
                                    button.text('Request Sent');
                                },
                                complete: function() {
                                    button.removeClass('addFriend');
                                }
                            });
                        }
                    });
                });
            </script>

<?php
        } else {
            echo "<h6>No Data Found</h6>";
        }
    }
} else {
    echo "CSRF Error ";
    exit;
}

?>